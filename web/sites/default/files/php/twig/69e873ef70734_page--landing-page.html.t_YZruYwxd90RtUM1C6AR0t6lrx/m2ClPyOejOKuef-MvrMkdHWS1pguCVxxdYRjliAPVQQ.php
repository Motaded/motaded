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

/* themes/custom/motaded_theme/templates/layout/page--landing-page.html.twig */
class __TwigTemplate_5a9fc61aa543c772def4991ae3a6eee2 extends Template
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
        // line 5
        yield "
";
        // line 50
        yield "  ";
        // line 53
        yield "
<div id=\"site-header\">
  ";
        // line 55
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(Twig\Extension\CoreExtension::include($this->env, $context, "@motaded_theme/includes/header.html.twig"));
        yield "
</div>

";
        // line 58
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumb", [], "any", false, false, true, 58)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 59
            yield "  <div class=\"bread\">
    ";
            // line 60
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumb", [], "any", false, false, true, 60), "html", null, true);
            yield "
  </div>
";
        }
        // line 63
        yield "
<div class=\"main-wrapper bg-white\">
  <main
    role=\"main\"
    class=\"\"
  >
    <a id=\"main-content\" tabindex=\"-1\"></a>";
        // line 70
        yield "
    <div class=\"layout-content\">
      ";
        // line 72
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "content", [], "any", false, false, true, 72), "html", null, true);
        yield "
    </div>";
        // line 74
        yield "
    ";
        // line 75
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 75)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 76
            yield "      <aside class=\"layout-sidebar-first\" role=\"complementary\">
        ";
            // line 77
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 77), "html", null, true);
            yield "
      </aside>
    ";
        }
        // line 80
        yield "
    ";
        // line 81
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 81)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 82
            yield "      <aside class=\"layout-sidebar-second\" role=\"complementary\">
        ";
            // line 83
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 83), "html", null, true);
            yield "
      </aside>
    ";
        }
        // line 86
        yield "
  </main>
</div>

";
        // line 90
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "pre_footer", [], "any", false, false, true, 90)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 91
            yield "<section class=\"pre-footer-band relative overflow-hidden bg-gradient-to-br from-[#e4f4ef] via-white to-[#f2f8ff]\">
  <div class=\"absolute inset-0 pointer-events-none\" aria-hidden=\"true\">
    <div class=\"absolute left-1/2 top-1/3 h-72 w-72 -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary-100 blur-3xl opacity-60\"></div>
    <div class=\"absolute right-0 bottom-0 h-40 w-40 translate-x-1/2 rounded-full bg-primary-200 blur-3xl opacity-40\"></div>
  </div>
  <div class=\"relative max-w-[1320px] mx-auto px-4 py-16 lg:py-24\">
    <div class=\"pre-footer-cta-inner max-w-xl mx-auto\">
        <div class=\"rounded-3xl border border-white/70 bg-white/75 px-8 py-10 shadow-xl backdrop-blur-sm text-center lg:px-12 lg:py-14\">
          ";
            // line 99
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "pre_footer", [], "any", false, false, true, 99), "html", null, true);
            yield "
        </div>
    </div>
  </div>
</section>
";
        }
        // line 105
        yield "
<div id=\"site-footer\">
  ";
        // line 107
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "footer", [], "any", false, false, true, 107)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 108
            yield "    ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(Twig\Extension\CoreExtension::include($this->env, $context, "@motaded_theme/includes/footer.html.twig"));
            yield "
  ";
        }
        // line 110
        yield "</div>

";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["page"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/motaded_theme/templates/layout/page--landing-page.html.twig";
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
        return array (  153 => 110,  147 => 108,  145 => 107,  141 => 105,  132 => 99,  122 => 91,  120 => 90,  114 => 86,  108 => 83,  105 => 82,  103 => 81,  100 => 80,  94 => 77,  91 => 76,  89 => 75,  86 => 74,  82 => 72,  78 => 70,  70 => 63,  64 => 60,  61 => 59,  59 => 58,  53 => 55,  49 => 53,  47 => 50,  44 => 5,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/motaded_theme/templates/layout/page--landing-page.html.twig", "/var/www/html/web/themes/custom/motaded_theme/templates/layout/page--landing-page.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["if" => 58];
        static $filters = ["escape" => 60];
        static $functions = ["include" => 55];

        try {
            $this->sandbox->checkSecurity(
                ['if'],
                ['escape'],
                ['include'],
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
