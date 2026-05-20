/*
 * Setup cost estimator — Alpine component; pricing from drupalSettings (admin profile).
 * Standalone (no Drupal global): loads before drupal.js when preprocess is disabled.
 */
(function () {
  'use strict';

  function getProfileSettings() {
    const settings = typeof window.drupalSettings !== 'undefined' ? window.drupalSettings : {};
    return settings.motadedSetupCost || {};
  }

  function formatNumber(n) {
    return new Intl.NumberFormat().format(Math.round(n));
  }

  function modifierMatches(answers, modifier) {
    const dimension = modifier.dimension || '';
    if (!dimension) {
      return false;
    }
    const answer = answers[dimension];
    const value = modifier.value || '';
    const valueMin = modifier.value_min || '';
    const valueMax = modifier.value_max || '';

    if (Array.isArray(answer)) {
      if (value === '*' || value === '') {
        return answer.length > 0;
      }
      return answer.includes(value);
    }

    if (valueMin !== '' || valueMax !== '') {
      const num = Number(answer) || 0;
      if (valueMin !== '' && num < Number(valueMin)) {
        return false;
      }
      if (valueMax !== '' && num > Number(valueMax)) {
        return false;
      }
      return true;
    }

    if (value === '*' || value === '') {
      return answer !== undefined && answer !== null && answer !== '';
    }
    return String(answer) === value;
  }

  function applyModifier(amount, modifier, side) {
    let out = amount;
    const mul = Number(modifier['multiply_' + side]) || 0;
    const add = Number(modifier['add_' + side]) || 0;
    if (mul > 0) {
      out *= mul;
    }
    return out + add;
  }

  function calculate(profile, answers) {
    const breakdown = [];
    let setupMin = 0;
    let setupMax = 0;

    (profile.line_items || []).forEach((item) => {
      let min = Number(item.base_min) || 0;
      let max = Number(item.base_max) || 0;
      (profile.modifiers || []).forEach((mod) => {
        if (mod.line_item_id !== item.id) {
          return;
        }
        if (!modifierMatches(answers, mod)) {
          return;
        }
        min = applyModifier(min, mod, 'min');
        max = applyModifier(max, mod, 'max');
      });
      breakdown.push({
        id: item.id,
        label: item.label || item.id,
        min: Math.max(0, Math.round(min)),
        max: Math.max(0, Math.round(max)),
      });
      setupMin += min;
      setupMax += max;
    });

    setupMin = Math.round(setupMin * (Number(profile.global_setup_buffer_min) || 1));
    setupMax = Math.round(setupMax * (Number(profile.global_setup_buffer_max) || 1));

    const monthly = profile.monthly || {};
    let monthlyMin = Number(monthly.base_min) || 0;
    let monthlyMax = Number(monthly.base_max) || 0;

    let employees = 0;
    (profile.steps || []).forEach((step) => {
      if (step.type === 'slider') {
        employees = Number(answers[step.id]) || 0;
      }
    });

    monthlyMin += employees * (Number(monthly.per_employee_min) || 0);
    monthlyMax += employees * (Number(monthly.per_employee_max) || 0);

    const officeAnswer = answers.office !== undefined ? answers.office : answers[findStepId(profile, 'office')] || '';
    (monthly.office || []).forEach((row) => {
      if (String(row.option_id) === String(officeAnswer)) {
        monthlyMin += Number(row.add_min) || 0;
        monthlyMax += Number(row.add_max) || 0;
      }
    });

    const servicesAnswer = answers.services;
    const serviceIds = Array.isArray(servicesAnswer) ? servicesAnswer : [];
    (monthly.service || []).forEach((row) => {
      if (serviceIds.includes(row.option_id)) {
        monthlyMin += Number(row.add_min) || 0;
        monthlyMax += Number(row.add_max) || 0;
      }
    });

    let timelineMin = Number(profile.timeline_default?.min_weeks) || 2;
    let timelineMax = Number(profile.timeline_default?.max_weeks) || 6;
    (profile.timelines || []).forEach((tl) => {
      const conditions = tl.conditions || [];
      if (conditions.length && conditions.every((c) => modifierMatches(answers, c))) {
        timelineMin = Number(tl.min_weeks) || timelineMin;
        timelineMax = Number(tl.max_weeks) || timelineMax;
      }
    });

    const pkg = scorePackage(profile, answers);

    return {
      setup_min: setupMin,
      setup_max: setupMax,
      monthly_min: Math.round(monthlyMin),
      monthly_max: Math.round(monthlyMax),
      timeline_min_weeks: timelineMin,
      timeline_max_weeks: timelineMax,
      breakdown,
      package: pkg,
    };
  }

  function findStepId(profile, id) {
    const step = (profile.steps || []).find((s) => s.id === id);
    return step ? step.id : id;
  }

  function scorePackage(profile, answers) {
    const packages = profile.packages || [];
    if (!packages.length) {
      return null;
    }
    let points = 0;
    packages.forEach((pkg) => {
      (pkg.rules || []).forEach((rule) => {
        const answer = answers[rule.dimension];
        if (Array.isArray(answer) && answer.includes(rule.value)) {
          points += Number(rule.points) || 0;
        } else if (String(answer) === String(rule.value)) {
          points += Number(rule.points) || 0;
        }
      });
    });
    let best = null;
    packages.forEach((pkg) => {
      if (points >= (Number(pkg.min_points) || 0)) {
        best = pkg;
      }
    });
    return best;
  }

  let alpineRegistered = false;

  function setupCostEstimatorFactory() {
    return {
        profile: {},
        steps: [],
        ui: {},
        currency: 'SAR',
        currentStep: 0,
        answers: {},
        result: null,
        hasEstimate: false,
        showLead: false,

        init() {
          this.profile = getProfileSettings();
          this.steps = this.profile.steps || [];
          this.ui = this.profile.ui || {};
          this.currency = this.profile.currency || 'SAR';
          this.steps.forEach((step) => {
            if (step.type === 'slider') {
              this.answers[step.id] = Number(step.slider_min) || 1;
            } else if (step.type === 'checkboxes') {
              this.answers[step.id] = [];
            }
          });
          this.recalculate();
          this.$nextTick(() => this.syncWebformFields());
        },

        get progressPct() {
          if (!this.steps.length) {
            return 0;
          }
          return Math.round(((this.currentStep + 1) / this.steps.length) * 100);
        },

        get progressLabel() {
          const tpl = this.ui.progress_label || 'Step @current of @total';
          return tpl
            .replace('@current', String(this.currentStep + 1))
            .replace('@total', String(this.steps.length));
        },

        selectOption(stepId, optId) {
          this.answers[stepId] = optId;
          this.recalculate();
        },

        toggleService(stepId, optId, checked) {
          if (!Array.isArray(this.answers[stepId])) {
            this.answers[stepId] = [];
          }
          if (checked) {
            if (!this.answers[stepId].includes(optId)) {
              this.answers[stepId].push(optId);
            }
          } else {
            this.answers[stepId] = this.answers[stepId].filter((id) => id !== optId);
          }
          this.recalculate();
        },

        isServiceChecked(stepId, optId) {
          return Array.isArray(this.answers[stepId]) && this.answers[stepId].includes(optId);
        },

        canAdvance(step) {
          if (step.type === 'cards') {
            return Boolean(this.answers[step.id]);
          }
          if (step.type === 'slider') {
            return Number(this.answers[step.id]) >= (Number(step.slider_min) || 1);
          }
          if (step.type === 'checkboxes') {
            return true;
          }
          return false;
        },

        nextStep() {
          if (this.currentStep < this.steps.length - 1) {
            this.currentStep += 1;
          }
        },

        prevStep() {
          if (this.currentStep > 0) {
            this.currentStep -= 1;
          }
        },

        employeesLabel(step) {
          const n = Number(this.answers[step.id]) || 0;
          return n >= (Number(step.slider_max) || 50) ? n + '+' : String(n);
        },

        recalculate() {
          const firstRequired = this.steps.find((s) => s.type === 'cards');
          if (firstRequired && !this.answers[firstRequired.id]) {
            this.hasEstimate = false;
            this.result = null;
            this.syncWebformFields();
            return;
          }
          this.result = calculate(this.profile, this.answers);
          this.hasEstimate = true;
          this.syncWebformFields();
        },

        formatRange(min, max) {
          if (min == null || max == null) {
            return '';
          }
          return formatNumber(min) + ' – ' + formatNumber(max);
        },

        formatTimeline() {
          if (!this.result) {
            return '';
          }
          const weeksSuffix = this.ui.timeline_weeks_suffix || ' weeks';
          return this.result.timeline_min_weeks + '–' + this.result.timeline_max_weeks + weeksSuffix;
        },

        scrollToLead() {
          if (!this.canAdvance(this.steps[this.steps.length - 1])) {
            return;
          }
          this.showLead = true;
          this.$nextTick(() => {
            this.syncWebformFields();
            const el = document.getElementById('setup-cost-lead');
            if (!el) {
              return;
            }
            el.classList.add('is-open');
            el.removeAttribute('aria-hidden');
            const header = document.querySelector('.site-header, header[role="banner"]');
            const offset = header ? header.getBoundingClientRect().height + 16 : 80;
            const top = el.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
          });
        },

        syncWebformFields() {
          const form = document.querySelector(
            '.setup-cost-lead-form, form.webform-submission-setup-cost-estimate-lead-form, #setup-cost-lead form.webform-submission-form'
          );
          if (!form) {
            return;
          }
          if (!this.result) {
            return;
          }
          const set = (name, value) => {
            const input = form.querySelector('[name="' + name + '"]');
            if (input) {
              input.value = value;
            }
          };
          set('estimate_setup_min', String(this.result.setup_min));
          set('estimate_setup_max', String(this.result.setup_max));
          set('estimate_monthly_min', String(this.result.monthly_min));
          set('estimate_monthly_max', String(this.result.monthly_max));
          set('estimate_timeline', this.formatTimeline());
          set('estimate_package', (this.result.package && this.result.package.label) || '');
          set('estimate_answers_json', JSON.stringify(this.answers));
          set('estimate_result_json', JSON.stringify(this.result));
          set('estimate_profile_id', this.profile.profile_id || '');
        },
    };
  }

  function registerSetupCostEstimator() {
    if (typeof Alpine === 'undefined' || alpineRegistered) {
      return false;
    }
    Alpine.data('setupCostEstimator', setupCostEstimatorFactory);
    alpineRegistered = true;
    return true;
  }

  document.addEventListener('alpine:init', registerSetupCostEstimator);
  if (typeof Alpine !== 'undefined') {
    registerSetupCostEstimator();
  }

  function bindLeadForms() {
    document.querySelectorAll('#setup-cost-lead form.webform-submission-form').forEach((form) => {
      if (form.dataset.setupCostBound === '1') {
        return;
      }
      form.dataset.setupCostBound = '1';
      form.addEventListener('submit', () => {
        const root = document.querySelector('[data-setup-cost-estimator]');
        if (root && typeof Alpine !== 'undefined') {
          const data = Alpine.$data(root);
          if (data && typeof data.syncWebformFields === 'function') {
            data.syncWebformFields();
          }
        }
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindLeadForms);
  }
  else {
    bindLeadForms();
  }
})();
