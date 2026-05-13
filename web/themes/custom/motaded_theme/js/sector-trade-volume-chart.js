/**
 * @file
 * Sector logistics: trade volume chart (World Bank merged series), tabs + SVG.
 */
(function (Drupal, once) {
  'use strict';

  /**
   * @param {string} raw
   * @returns {{value: Array<{year: number, value: number}>, growth: Array<{year: number, growth: number}>}|null}
   */
  function parsePayload(raw) {
    if (!raw) {
      return null;
    }
    try {
      const data = JSON.parse(raw);
      if (!data || !Array.isArray(data.value) || data.value.length === 0) {
        return null;
      }
      if (!Array.isArray(data.growth)) {
        data.growth = [];
      }
      return data;
    }
    catch (e) {
      return null;
    }
  }

  /**
   * @param {number} span
   * @param {number} tickCount
   * @returns {number}
   */
  function niceStep(span, tickCount) {
    if (span <= 0) {
      return 1;
    }
    const rough = span / Math.max(1, tickCount - 1);
    const pow10 = Math.pow(10, Math.floor(Math.log10(rough)));
    const err = rough / pow10;
    let nice = 10;
    if (err <= 1) {
      nice = 1;
    }
    else if (err <= 2) {
      nice = 2;
    }
    else if (err <= 5) {
      nice = 5;
    }
    return nice * pow10;
  }

  /**
   * @param {number} min
   * @param {number} max
   * @param {number} count
   * @returns {number[]}
   */
  function buildTicks(min, max, count) {
    const span = max - min;
    if (span <= 0) {
      return [min, min + 1];
    }
    const step = niceStep(span, count);
    const ticks = [];
    let v = Math.ceil(min / step) * step;
    const limit = max + step * 0.001;
    while (v <= limit && ticks.length < 14) {
      ticks.push(v);
      v += step;
    }
    return ticks;
  }

  /**
   * @param {SVGSVGElement} svg
   * @param {'value'|'growth'} mode
   * @param {{value: object[], growth: object[]}} payload
   * @param {string} gradientId
   */
  function renderChart(svg, mode, payload, gradientId) {
    const isValue = mode === 'value';
    const series = isValue ? payload.value : payload.growth;
    if (!series.length) {
      return;
    }

    const years = series.map((p) => p.year);
    const vals = series.map((p) => (isValue ? p.value : p.growth));
    let vmin;
    let vmax;
    if (isValue) {
      vmin = 0;
      vmax = Math.max(...vals, 1) * 1.08;
    }
    else {
      vmax = Math.max(...vals, 0);
      vmin = Math.min(...vals, 0);
      if (vmin === vmax) {
        vmax += 1;
        vmin -= 1;
      }
      const pad = (vmax - vmin) * 0.12;
      vmax += pad;
      vmin -= pad;
    }
    if (vmax <= vmin) {
      vmax = vmin + 1;
    }

    const W = 920;
    const H = 320;
    const padL = 54;
    const padR = 28;
    const padT = 14;
    const padB = 44;
    const pw = W - padL - padR;
    const ph = H - padT - padB;
    const n = series.length;
    const xAt = (i) => padL + (n <= 1 ? pw / 2 : (i / (n - 1)) * pw);
    const yAt = (v) => padT + ph - ((v - vmin) / (vmax - vmin)) * ph;

    while (svg.firstChild) {
      svg.removeChild(svg.firstChild);
    }

    const ns = 'http://www.w3.org/2000/svg';
    const defs = document.createElementNS(ns, 'defs');
    const grad = document.createElementNS(ns, 'linearGradient');
    grad.setAttribute('id', gradientId);
    grad.setAttribute('x1', '0');
    grad.setAttribute('y1', '0');
    grad.setAttribute('x2', '0');
    grad.setAttribute('y2', '1');
    const g0 = document.createElementNS(ns, 'stop');
    g0.setAttribute('offset', '0%');
    g0.setAttribute('stop-color', '#bbf7d0');
    grad.appendChild(g0);
    const g1 = document.createElementNS(ns, 'stop');
    g1.setAttribute('offset', '100%');
    g1.setAttribute('stop-color', 'rgba(255,255,255,0)');
    grad.appendChild(g1);
    defs.appendChild(grad);
    svg.appendChild(defs);

    const yTicks = isValue ? buildTicks(0, vmax, 6) : buildTicks(vmin, vmax, 6);
    yTicks.forEach((tv) => {
      if (tv < vmin - 0.0001 || tv > vmax + 0.0001) {
        return;
      }
      const yy = yAt(tv);
      const line = document.createElementNS(ns, 'line');
      line.setAttribute('x1', String(padL));
      line.setAttribute('x2', String(W - padR));
      line.setAttribute('y1', String(yy));
      line.setAttribute('y2', String(yy));
      line.setAttribute('class', 'sector-trade-chart__grid');
      svg.appendChild(line);
      const text = document.createElementNS(ns, 'text');
      text.setAttribute('x', String(padL - 8));
      text.setAttribute('y', String(yy + 4));
      text.setAttribute('text-anchor', 'end');
      text.setAttribute('class', 'sector-trade-chart__axis');
      text.textContent = isValue ? String(Math.round(tv)) : tv.toFixed(1);
      svg.appendChild(text);
    });

    let pathLine = `M ${xAt(0)} ${yAt(vals[0])}`;
    for (let i = 1; i < n; i += 1) {
      pathLine += ` L ${xAt(i)} ${yAt(vals[i])}`;
    }
    const pathArea = `${pathLine} L ${xAt(n - 1)} ${padT + ph} L ${xAt(0)} ${padT + ph} Z`;

    const area = document.createElementNS(ns, 'path');
    area.setAttribute('d', pathArea);
    area.setAttribute('fill', `url(#${gradientId})`);
    area.setAttribute('class', 'sector-trade-chart__area');
    svg.appendChild(area);

    const linep = document.createElementNS(ns, 'path');
    linep.setAttribute('d', pathLine);
    linep.setAttribute('fill', 'none');
    linep.setAttribute('class', 'sector-trade-chart__line');
    linep.setAttribute('stroke-width', '2.25');
    svg.appendChild(linep);

    vals.forEach((v, i) => {
      const c = document.createElementNS(ns, 'circle');
      c.setAttribute('cx', String(xAt(i)));
      c.setAttribute('cy', String(yAt(v)));
      c.setAttribute('r', '6');
      c.setAttribute('class', 'sector-trade-chart__dot');
      const title = document.createElementNS(ns, 'title');
      title.textContent = isValue
        ? `${years[i]}: ${v}`
        : `${years[i]}: ${v}%`;
      c.appendChild(title);
      svg.appendChild(c);
    });

    years.forEach((yr, i) => {
      const t = document.createElementNS(ns, 'text');
      t.setAttribute('x', String(xAt(i)));
      t.setAttribute('y', String(H - 12));
      t.setAttribute('text-anchor', 'middle');
      t.setAttribute('class', 'sector-trade-chart__axis sector-trade-chart__axis--x');
      t.textContent = String(yr);
      svg.appendChild(t);
    });

    svg.setAttribute('viewBox', `0 0 ${W} ${H}`);
  }

  Drupal.behaviors.sectorTradeVolumeChart = {
    attach(context) {
      once('sectorTradeVolumeChart', '[data-sector-trade-volume-chart]', context).forEach((root) => {
        const payload = parsePayload(root.getAttribute('data-chart'));
        if (!payload) {
          return;
        }
        const gradientId = root.getAttribute('data-gradient-id') || 'stc-fill-default';
        const tabValue = root.querySelector('[data-tab="value"]');
        const tabGrowth = root.querySelector('[data-tab="growth"]');
        const panels = root.querySelectorAll('[data-panel]');
        if (!tabValue || !tabGrowth || !panels.length) {
          return;
        }

        let mode = 'value';

        const redraw = () => {
          panels.forEach((panel) => {
            const panelMode = panel.getAttribute('data-panel');
            const show = panelMode === mode;
            panel.hidden = !show;
            const el = panel.querySelector('.sector-trade-chart__svg');
            if (el && show) {
              const gid = `${gradientId}-${mode}`;
              renderChart(el, mode, payload, gid);
            }
          });
          tabValue.setAttribute('aria-selected', mode === 'value' ? 'true' : 'false');
          tabGrowth.setAttribute('aria-selected', mode === 'growth' ? 'true' : 'false');
          tabValue.classList.toggle('is-active', mode === 'value');
          tabGrowth.classList.toggle('is-active', mode === 'growth');
        };

        tabValue.addEventListener('click', () => {
          mode = 'value';
          redraw();
        });
        tabGrowth.addEventListener('click', () => {
          if (tabGrowth.disabled || !payload.growth.length) {
            return;
          }
          mode = 'growth';
          redraw();
        });

        redraw();
      });
    },
  };
})(Drupal, once);
