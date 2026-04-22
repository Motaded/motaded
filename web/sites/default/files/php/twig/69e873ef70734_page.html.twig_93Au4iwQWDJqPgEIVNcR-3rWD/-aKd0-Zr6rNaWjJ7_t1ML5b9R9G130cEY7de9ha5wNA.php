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

/* themes/custom/motaded_theme/templates/layout/page.html.twig */
class __TwigTemplate_54868c4b836f6aeb564f3ce19f6f027b extends Template
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
        $context["service_page_full_bleed"] = ((array_key_exists("service_page_full_bleed", $context)) ? (Twig\Extension\CoreExtension::default(($context["service_page_full_bleed"] ?? null), false)) : (false));
        // line 52
        $context["show_service_side"] = false;
        // line 53
        if ((($context["node"] ?? null) && (CoreExtension::getAttribute($this->env, $this->source, ($context["node"] ?? null), "bundle", [], "any", false, false, true, 53) == "page"))) {
            // line 54
            yield "  ";
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["node"] ?? null), "hasField", ["field_display_on_services"], "method", false, false, true, 54) && (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["node"] ?? null), "field_display_on_services", [], "any", false, false, true, 54), "value", [], "any", false, false, true, 54) == "1"))) {
                // line 55
                yield "    ";
                $context["show_service_side"] = (((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 55)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (true) : (false));
                // line 56
                yield "  ";
            }
        } elseif ((($tmp = CoreExtension::getAttribute($this->env, $this->source,         // line 57
($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 57)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 58
            yield "  ";
            $context["show_service_side"] = true;
        }
        // line 60
        $context["main_wrapper_classes"] = ["main-wrapper"];
        // line 64
        yield "
";
        // line 65
        if ((($tmp = ($context["service_page_full_bleed"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 66
            yield "  ";
            $context["main_classes"] = ["w-full max-w-none pb-10 px-0 flex flex-col gap-0"];
            // line 69
            yield "  ";
            $context["content_wrapper_classes"] = ["layout-content", "w-full", "max-w-none"];
        } else {
            // line 75
            yield "  ";
            $context["main_classes"] = ["pb-[40px] max-w-[1320px] mx-auto px-4", (((CoreExtension::getAttribute($this->env, $this->source,             // line 77
($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 77) && ($context["show_service_side"] ?? null))) ? ("flex relative gap-8 items-start self-stretch max-md:flex-col max-md:gap-10 max-md:px-10 max-md:py-8 max-sm:gap-8 max-sm:px-4 max-sm:py-6") : ("mt-[40px]"))];
            // line 79
            yield "  ";
            $context["content_wrapper_classes"] = ["layout-content"];
            // line 82
            yield "  ";
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 82) && ($context["show_service_side"] ?? null))) {
                // line 83
                yield "    ";
                $context["content_wrapper_classes"] = Twig\Extension\CoreExtension::merge(($context["content_wrapper_classes"] ?? null), ["flex relative flex-col gap-16 items-start w-[832px] max-md:w-full"]);
                // line 84
                yield "  ";
            }
        }
        // line 86
        yield "
";
        // line 87
        if ((($context["node"] ?? null) && ((($_v0 = (($_v1 = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["node"] ?? null), "type", [], "any", false, false, true, 87), "value", [], "any", false, false, true, 87)) && is_array($_v1) || $_v1 instanceof ArrayAccess && in_array($_v1::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v1[0] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["node"] ?? null), "type", [], "any", false, false, true, 87), "value", [], "any", false, false, true, 87), 0, [], "array", false, false, true, 87))) && is_array($_v0) || $_v0 instanceof ArrayAccess && in_array($_v0::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v0["target_id"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, (($_v2 = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["node"] ?? null), "type", [], "any", false, false, true, 87), "value", [], "any", false, false, true, 87)) && is_array($_v2) || $_v2 instanceof ArrayAccess && in_array($_v2::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v2[0] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["node"] ?? null), "type", [], "any", false, false, true, 87), "value", [], "any", false, false, true, 87), 0, [], "array", false, false, true, 87)), "target_id", [], "array", false, false, true, 87)) == "page"))) {
            // line 88
            yield "  ";
            $context["main_wrapper_classes"] = Twig\Extension\CoreExtension::merge(($context["main_wrapper_classes"] ?? null), ["bg-gray-50"]);
        } else {
            // line 90
            yield "  ";
            $context["main_wrapper_classes"] = Twig\Extension\CoreExtension::merge(($context["main_wrapper_classes"] ?? null), ["bg-white"]);
        }
        // line 92
        yield "

";
        // line 97
        yield "
<div id=\"site-header\">
  ";
        // line 99
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(Twig\Extension\CoreExtension::include($this->env, $context, "@motaded_theme/includes/header.html.twig"));
        yield "
</div>

";
        // line 102
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumb", [], "any", false, false, true, 102)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 103
            yield "  <div class=\"bread";
            if ((($tmp = ($context["service_page_full_bleed"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield " bread--service-hero-band";
            }
            yield "\">
    ";
            // line 104
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumb", [], "any", false, false, true, 104), "html", null, true);
            yield "
  </div>
";
        }
        // line 107
        yield "
<div class=\"";
        // line 108
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::join(($context["main_wrapper_classes"] ?? null), " "), "html", null, true);
        yield "\">
  <main role=\"main\" class=\"";
        // line 109
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::join(($context["main_classes"] ?? null), " "), "html", null, true);
        yield "\">
    <a id=\"main-content\" class=\"visually-hidden\" tabindex=\"-1\"></a>

    <div class=\"";
        // line 112
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::join(($context["content_wrapper_classes"] ?? null), " "), "html", null, true);
        yield "\">
      ";
        // line 113
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "content", [], "any", false, false, true, 113), "html", null, true);
        yield "
    </div>

    ";
        // line 116
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 116)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 117
            yield "      <aside class=\"layout-sidebar-first\" role=\"complementary\">
        ";
            // line 118
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 118), "html", null, true);
            yield "
      </aside>
    ";
        }
        // line 121
        yield "
    ";
        // line 122
        if ((CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 122) && ($context["show_service_side"] ?? null))) {
            // line 123
            yield "      <aside class=\"layout-sidebar-second flex relative flex-col gap-6 items-start p-10 bg-white rounded-2xl border border-gray-300 border-solid w-[416px] max-md:w-full max-sm:p-6\" role=\"complementary\">
        ";
            // line 124
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 124), "html", null, true);
            yield "
      </aside>
    ";
        }
        // line 127
        yield "
  </main>
</div>
";
        // line 130
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "below_content", [], "any", false, false, true, 130)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 131
            yield "  <div class=\"below-content-wrapper mt-6\">
    <div class=\"max-w-[1320px] mx-auto px-4\">
      ";
            // line 133
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "below_content", [], "any", false, false, true, 133), "html", null, true);
            yield "
    </div>
  </div>
";
        }
        // line 137
        yield "
";
        // line 138
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "pre_footer", [], "any", false, false, true, 138)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 139
            yield "<section class=\"pre-footer-band relative overflow-hidden bg-gradient-to-br from-[#e4f4ef] via-white to-[#f2f8ff]\">
  <div class=\"absolute inset-0 pointer-events-none\" aria-hidden=\"true\">
    <div class=\"absolute left-1/2 top-1/3 h-72 w-72 -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary-100 blur-3xl opacity-60\"></div>
    <div class=\"absolute right-0 bottom-0 h-40 w-40 translate-x-1/2 rounded-full bg-primary-200 blur-3xl opacity-40\"></div>
  </div>
  <div class=\"relative max-w-[1320px] mx-auto px-4 py-16 lg:py-24\">
    <div class=\"pre-footer-cta-inner max-w-xl mx-auto\">
      <div class=\"rounded-3xl border border-white/70 bg-white/75 px-8 py-10 shadow-xl backdrop-blur-sm text-center lg:px-12 lg:py-14\">
        ";
            // line 147
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "pre_footer", [], "any", false, false, true, 147), "html", null, true);
            yield "
      </div>
    </div>
  </div>
</section>
";
        }
        // line 153
        yield "
<div id=\"site-footer\">
  ";
        // line 155
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "footer", [], "any", false, false, true, 155)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 156
            yield "    ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(Twig\Extension\CoreExtension::include($this->env, $context, "@motaded_theme/includes/footer.html.twig"));
            yield "
  ";
        }
        // line 158
        yield "</div>

";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["node", "page"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/motaded_theme/templates/layout/page.html.twig";
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
        return array (  243 => 158,  237 => 156,  235 => 155,  231 => 153,  222 => 147,  212 => 139,  210 => 138,  207 => 137,  200 => 133,  196 => 131,  194 => 130,  189 => 127,  183 => 124,  180 => 123,  178 => 122,  175 => 121,  169 => 118,  166 => 117,  164 => 116,  158 => 113,  154 => 112,  148 => 109,  144 => 108,  141 => 107,  135 => 104,  128 => 103,  126 => 102,  120 => 99,  116 => 97,  112 => 92,  108 => 90,  104 => 88,  102 => 87,  99 => 86,  95 => 84,  92 => 83,  89 => 82,  86 => 79,  84 => 77,  82 => 75,  78 => 69,  75 => 66,  73 => 65,  70 => 64,  68 => 60,  64 => 58,  62 => 57,  59 => 56,  56 => 55,  53 => 54,  51 => 53,  49 => 52,  47 => 50,  44 => 5,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/motaded_theme/templates/layout/page.html.twig", "/var/www/html/web/themes/custom/motaded_theme/templates/layout/page.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 50, "if" => 53];
        static $filters = ["default" => 50, "merge" => 83, "escape" => 104, "join" => 108];
        static $functions = ["include" => 99];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if'],
                ['default', 'merge', 'escape', 'join'],
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
