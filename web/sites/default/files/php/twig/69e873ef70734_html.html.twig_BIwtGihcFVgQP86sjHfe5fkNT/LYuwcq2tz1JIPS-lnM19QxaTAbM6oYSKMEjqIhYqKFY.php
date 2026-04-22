<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* themes/custom/motaded_theme/templates/layout/html.html.twig */
class __TwigTemplate_27108e4d7a0018efe5a23398276a8773 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield "<!--
 * Theme: Muin (Custom Licensed Version)
 * Buit by Banafsijy.com – Single Project License – Do Not Redistribute
 -->

";
        // line 32
        $context["body_classes"] = [(((($tmp =         // line 33
($context["logged_in"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("user-logged-in") : ("")), (((($tmp =  !        // line 34
($context["root_path"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("path-frontpage") : (("path-" . \Drupal\Component\Utility\Html::getClass(($context["root_path"] ?? null))))), (((($tmp =         // line 35
($context["node_type"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (("page-node-type-" . \Drupal\Component\Utility\Html::getClass(($context["node_type"] ?? null)))) : ("")), (((($tmp =         // line 36
($context["db_offline"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("db-offline") : ("")), "min-h-screen bg-gray-50 text-gray-900"];
        // line 40
        yield "<!DOCTYPE html>
<html";
        // line 41
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["html_attributes"] ?? null), "html", null, true);
        yield ">
\t<head>
\t\t<head-placeholder token=\"";
        // line 43
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["placeholder_token"] ?? null), "html", null, true);
        yield "\">
\t\t\t<title>";
        // line 44
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->safeJoin($this->env, ($context["head_title"] ?? null), " | "));
        yield "</title>
\t\t\t<css-placeholder token=\"";
        // line 45
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["placeholder_token"] ?? null), "html", null, true);
        yield "\">
\t\t\t\t<js-placeholder token=\"";
        // line 46
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["placeholder_token"] ?? null), "html", null, true);
        yield "\">

\t\t\t\t";
        // line 49
        yield "

\t\t\t";
        // line 51
        if ((($tmp =  !($context["motaded_suppress_seo_tracking"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 52
            yield "\t\t\t";
            // line 53
            yield "\t\t\t<script>
\t\t\twindow.dataLayer = window.dataLayer || [];
\t\t\twindow.gtag = window.gtag || function () { window.dataLayer.push(arguments); };

\t\t\twindow.addEventListener('load', function () {
\t\t\t\t(function (w, d, s, l, i) {
\t\t\t\t\tw[l] = w[l] || [];
\t\t\t\t\tw[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
\t\t\t\t\tvar f = d.getElementsByTagName(s)[0],
\t\t\t\t\t\tj = d.createElement(s),
\t\t\t\t\t\tdl = l != 'dataLayer' ? '&l=' + l : '';
\t\t\t\t\tj.async = true;
\t\t\t\t\tj.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
\t\t\t\t\tf.parentNode.insertBefore(j, f);
\t\t\t\t})(window, document, 'script', 'dataLayer', 'GTM-NBFPBC74');

\t\t\t\tvar g = document.createElement('script');
\t\t\t\tg.async = true;
\t\t\t\tg.src = 'https://www.googletagmanager.com/gtag/js?id=G-G1C83L3T2V';
\t\t\t\tg.onload = function () {
\t\t\t\t\twindow.gtag('js', new Date());
\t\t\t\t\twindow.gtag('config', 'G-G1C83L3T2V');
\t\t\t\t};
\t\t\t\tdocument.head.appendChild(g);
\t\t\t});
\t\t\t</script>

<!-- Ahrefs Analytics -->
<script src=\"https://analytics.ahrefs.com/analytics.js\" data-key=\"S7egiqyeXzc3B99awD8KjA\" async></script>

<!-- Meta Pixel + LinkedIn: load after first interaction or 3s timeout -->
<script>
(function (w, d) {
  var loaded = false;
  function loadMetaPixel() {
    if (w.fbq) return;
    !(function (f, b, e, v, n, t, s) {
      if (f.fbq) return;
      n = f.fbq = function () {
        n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
      };
      if (!f._fbq) f._fbq = n;
      n.push = n;
      n.loaded = !0;
      n.version = '2.0';
      n.queue = [];
      t = b.createElement(e);
      t.async = !0;
      t.src = v;
      s = b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t, s);
    })(w, d, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
    w.fbq('init', '638172781527457');
    w.fbq('track', 'PageView');
  }
  function loadLinkedIn() {
    w._linkedin_partner_id = '6424985';
    w._linkedin_data_partner_ids = w._linkedin_data_partner_ids || [];
    w._linkedin_data_partner_ids.push(w._linkedin_partner_id);
    (function (l) {
      if (!l) {
        w.lintrk = function (a, b) {
          w.lintrk.q.push([a, b]);
        };
        w.lintrk.q = [];
      }
      var s = d.getElementsByTagName('script')[0];
      var b = d.createElement('script');
      b.type = 'text/javascript';
      b.async = true;
      b.src = 'https://snap.licdn.com/li.lms-analytics/insight.min.js';
      s.parentNode.insertBefore(b, s);
    })(w.lintrk);
  }
  function loadDeferredMarketingTags() {
    if (loaded) return;
    loaded = true;
    loadMetaPixel();
    loadLinkedIn();
  }
  ['scroll', 'mousemove', 'touchstart', 'keydown', 'click'].forEach(function (ev) {
    w.addEventListener(ev, loadDeferredMarketingTags, { once: true, passive: true });
  });
  setTimeout(loadDeferredMarketingTags, 3000);
})(window, document);
</script>
<noscript><img height=\"1\" width=\"1\" style=\"display:none\"
  src=\"https://www.facebook.com/tr?id=638172781527457&amp;ev=PageView&amp;noscript=1\"
  alt=\"\" /></noscript>
<noscript>
<img height=\"1\" width=\"1\" style=\"display:none;\" alt=\"\"
  src=\"https://px.ads.linkedin.com/collect/?pid=6424985&amp;fmt=gif\" />
</noscript>
<!-- End Meta Pixel + LinkedIn -->
\t\t\t";
        }
        // line 148
        yield "
\t\t\t\t</head>
\t\t\t\t<body";
        // line 150
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["body_classes"] ?? null)], "method", false, false, true, 150), "html", null, true);
        yield ">
\t\t\t\t\t";
        // line 151
        if ((($tmp =  !($context["motaded_suppress_seo_tracking"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 152
            yield "\t\t\t\t\t<noscript>
\t\t\t\t\t\t<iframe src=\"https://www.googletagmanager.com/ns.html?id=GTM-NBFPBC74\" height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\" title=\"Google Tag Manager\"></iframe>
\t\t\t\t\t</noscript>
\t\t\t\t\t";
        }
        // line 156
        yield "\t\t\t\t\t";
        // line 160
        yield "\t\t\t\t\t<a href=\"#main-content\" class=\"visually-hidden focusable skip-link\">
\t\t\t\t\t\t";
        // line 161
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Skip to main content"));
        yield "
\t\t\t\t\t</a>
\t\t\t\t\t";
        // line 163
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["page_top"] ?? null), "html", null, true);
        yield "
\t\t\t\t\t";
        // line 164
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["page"] ?? null), "html", null, true);
        yield "
\t\t\t\t\t";
        // line 165
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["page_bottom"] ?? null), "html", null, true);
        yield "
\t\t\t\t\t<js-bottom-placeholder token=\"";
        // line 166
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["placeholder_token"] ?? null), "html", null, true);
        yield "\">
\t\t\t\t\t";
        // line 167
        if ((($tmp =  !($context["motaded_suppress_seo_tracking"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 168
            yield "\t\t\t\t\t <script defer type=\"application/javascript\" src=\"https://pulse.clickguard.com/s/accqf1jtljMP6/astOxT2EN0qJL\"></script>
\t\t\t\t\t <script defer type=\"application/javascript\" src=\"https://pulse.clickguard.com/sc\"></script>
\t\t\t\t\t";
        }
        // line 171
        yield "\t\t\t\t\t</body>
\t\t\t\t</html>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["logged_in", "root_path", "node_type", "db_offline", "html_attributes", "placeholder_token", "head_title", "motaded_suppress_seo_tracking", "attributes", "page_top", "page", "page_bottom"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/motaded_theme/templates/layout/html.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  236 => 171,  231 => 168,  229 => 167,  225 => 166,  221 => 165,  217 => 164,  213 => 163,  208 => 161,  205 => 160,  203 => 156,  197 => 152,  195 => 151,  191 => 150,  187 => 148,  90 => 53,  88 => 52,  86 => 51,  82 => 49,  77 => 46,  73 => 45,  69 => 44,  65 => 43,  60 => 41,  57 => 40,  55 => 36,  54 => 35,  53 => 34,  52 => 33,  51 => 32,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/motaded_theme/templates/layout/html.html.twig", "/var/www/html/web/themes/custom/motaded_theme/templates/layout/html.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 32, "if" => 51];
        static $filters = ["clean_class" => 34, "escape" => 41, "safe_join" => 44, "t" => 161];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if'],
                ['clean_class', 'escape', 'safe_join', 't'],
                [],
                $this->source
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
