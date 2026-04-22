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

/* themes/custom/motaded_theme/templates/navigation/menu--menu-mobile.html.twig */
class __TwigTemplate_9fc51873b9491964c80737429d771a0b extends Template
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
        // line 26
        $macros["menus"] = $this->macros["menus"] = $this;
        // line 27
        yield "
";
        // line 32
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($macros["menus"]->getTemplateForMacro("macro_menu_links", $context, 32, $this->getSourceContext())->macro_menu_links(...[($context["items"] ?? null), ($context["attributes"] ?? null), 0]));
        yield "

";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["_self", "items", "attributes", "menu_level"]);        yield from [];
    }

    // line 34
    public function macro_menu_links($items = null, $attributes = null, $menu_level = null, ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "items" => $items,
            "attributes" => $attributes,
            "menu_level" => $menu_level,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = implode('', iterator_to_array((function () use (&$context, $macros, $blocks) {
            // line 35
            yield "\t";
            $macros["menus"] = $this;
            // line 36
            yield "\t";
            if ((($tmp = ($context["items"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 37
                yield "\t\t";
                if ((($context["menu_level"] ?? null) == 1)) {
                    // line 38
                    yield "\t\t\t<div x-show=\"mobileSection==='";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "menu_parent", [], "any", false, false, true, 38), "html", null, true);
                    yield "'\" x-transition x-cloak class=\"pb-3 px-3\">
\t\t\t\t<ul class=\"grid grid-cols-1 gap-2\">
\t\t\t\t";
                }
                // line 41
                yield "\t\t\t\t";
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(($context["items"] ?? null));
                foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                    // line 42
                    yield "\t\t\t\t\t";
                    // line 43
                    $context["classes"] = [(((                    // line 44
($context["menu_level"] ?? null) == 0)) ? ("border-b border-gray-200") : ("")), (((($tmp = CoreExtension::getAttribute($this->env, $this->source,                     // line 45
$context["item"], "in_active_trail", [], "any", false, false, true, 45)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("menu-item--active-trail") : (""))];
                    // line 48
                    yield "\t\t\t\t\t";
                    $context["title"] = Twig\Extension\CoreExtension::replace(Twig\Extension\CoreExtension::lower($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source, $context["item"], "title", [], "any", false, false, true, 48)), [" " => "_"]);
                    // line 49
                    yield "\t\t\t\t\t";
                    if ((($context["menu_level"] ?? null) == 0)) {
                        // line 50
                        yield "\t\t\t\t\t\t<div";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "attributes", [], "any", false, false, true, 50), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 50), "html", null, true);
                        yield ">
\t\t\t\t\t\t";
                    }
                    // line 52
                    yield "
\t\t\t\t\t\t";
                    // line 53
                    if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["item"], "below", [], "any", false, false, true, 53)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 54
                        yield "\t\t\t\t\t\t\t<button type=\"button\" class=\"w-full flex items-center justify-between px-4 py-3 text-md font-medium\" @click=\"mobileSection = mobileSection==='";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
                        yield "' ? null : '";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
                        yield "'\" :aria-expanded=\"mobileSection==='";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
                        yield "'\">
\t\t\t\t\t\t\t\t<span>";
                        // line 55
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "title", [], "any", false, false, true, 55), "html", null, true);
                        yield "</span>
\t\t\t\t\t\t\t\t<img alt=\"chevron\" width=\"16\" height=\"16\" class=\"transition-transform\" :class=\"openMenu==='";
                        // line 56
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
                        yield "' ? 'rotate-180' : ''\" src=\"/themes/custom/motaded_theme/img/icon-arrow-down.svg\"/>
\t\t\t\t\t\t\t</button>
\t\t\t\t\t\t\t";
                        // line 58
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($macros["menus"]->getTemplateForMacro("macro_menu_links", $context, 58, $this->getSourceContext())->macro_menu_links(...[CoreExtension::getAttribute($this->env, $this->source, $context["item"], "below", [], "any", false, false, true, 58), CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "setAttribute", ["menu_parent", ($context["title"] ?? null)], "method", false, false, true, 58), (($context["menu_level"] ?? null) + 1)]));
                        yield "
\t\t\t\t\t\t";
                    } else {
                        // line 60
                        yield "\t\t\t\t\t\t\t";
                        $context["internal_path"] = ((((($context["menu_level"] ?? null) == 1) && CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "url", [], "any", false, false, true, 60), "isRouted", [], "any", false, false, true, 60))) ? (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "url", [], "any", false, false, true, 60), "getInternalPath", [], "method", false, false, true, 60)) : (""));
                        // line 61
                        yield "\t\t\t\t\t\t\t";
                        $context["is_services_hub"] = ((($context["menu_level"] ?? null) == 1) && (($context["internal_path"] ?? null) == "services"));
                        // line 62
                        yield "\t\t\t\t\t\t\t";
                        if ((($context["menu_level"] ?? null) == 1)) {
                            // line 63
                            yield "\t\t\t\t\t\t\t\t<li>
\t\t\t\t\t\t\t\t";
                        }
                        // line 65
                        yield "\t\t\t\t\t\t\t\t";
                        $context["link_classes"] = ["flex", "items-center", "gap-0", "px-4", "py-4", "w-fit", "m-0.5", "rounded-md", "text-md", "leading-[1.2]", "font-medium", "transition", "h-full", "relative", "focus-visible:outline", "focus-visible:outline-2", "focus-visible:outline-gray-800", "text-gray-900"];
                        // line 86
                        yield "\t\t\t\t\t\t\t\t";
                        $context["mobile_hub_classes"] = ["inline-flex", "items-center", "justify-center", "rounded-full", "bg-primary-600", "text-white", "px-8", "py-3.5", "font-normal", "no-underline", "shadow-sm", "transition", "hover:bg-primary-700", "hover:no-underline", "focus-visible:outline", "focus-visible:outline-2", "focus-visible:outline-offset-2", "focus-visible:outline-gray-800", "block", "rounded-md", "px-3", "py-2", "hover:bg-gray-50", "text-sm"];
                        // line 112
                        yield "\t\t\t\t\t\t\t\t";
                        $context["mobile_default_classes"] = ["block", "rounded-md", "px-3", "py-2", "hover:bg-gray-50", "text-sm"];
                        // line 113
                        yield "\t\t\t\t\t\t\t\t";
                        $context["mobile_link_attributes"] = (((($context["menu_level"] ?? null) == 0)) ? ($this->extensions['Drupal\Core\Template\TwigExtension']->createAttribute(["class" =>                         // line 114
($context["link_classes"] ?? null)])) : ((((($tmp =                         // line 115
($context["is_services_hub"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->extensions['Drupal\Core\Template\TwigExtension']->createAttribute(["class" =>                         // line 116
($context["mobile_hub_classes"] ?? null)])) : ($this->extensions['Drupal\Core\Template\TwigExtension']->createAttribute(["class" =>                         // line 117
($context["mobile_default_classes"] ?? null)])))));
                        // line 119
                        yield "\t\t\t\t\t\t\t\t";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->getLink(CoreExtension::getAttribute($this->env, $this->source, $context["item"], "title", [], "any", false, false, true, 119), CoreExtension::getAttribute($this->env, $this->source, $context["item"], "url", [], "any", false, false, true, 119), ($context["mobile_link_attributes"] ?? null)), "html", null, true);
                        yield "
\t\t\t\t\t\t\t\t";
                        // line 120
                        if ((($context["menu_level"] ?? null) == 1)) {
                            // line 121
                            yield "\t\t\t\t\t\t\t\t</li>
\t\t\t\t\t\t\t";
                        }
                        // line 123
                        yield "\t\t\t\t\t\t";
                    }
                    // line 124
                    yield "
\t\t\t\t\t\t";
                    // line 125
                    if ((($context["menu_level"] ?? null) == 0)) {
                        // line 126
                        yield "\t\t\t\t\t\t</div>
\t\t\t\t\t";
                    }
                    // line 128
                    yield "\t\t\t\t";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_key'], $context['item'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 129
                yield "\t\t\t\t";
                if ((($context["menu_level"] ?? null) == 1)) {
                    // line 130
                    yield "\t\t\t\t</ul>
\t\t\t</div>
\t\t";
                }
                // line 133
                yield "\t";
            }
            yield from [];
        })(), false))) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/motaded_theme/templates/navigation/menu--menu-mobile.html.twig";
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
        return array (  208 => 133,  203 => 130,  200 => 129,  194 => 128,  190 => 126,  188 => 125,  185 => 124,  182 => 123,  178 => 121,  176 => 120,  171 => 119,  169 => 117,  168 => 116,  167 => 115,  166 => 114,  164 => 113,  161 => 112,  158 => 86,  155 => 65,  151 => 63,  148 => 62,  145 => 61,  142 => 60,  137 => 58,  132 => 56,  128 => 55,  119 => 54,  117 => 53,  114 => 52,  108 => 50,  105 => 49,  102 => 48,  100 => 45,  99 => 44,  98 => 43,  96 => 42,  91 => 41,  84 => 38,  81 => 37,  78 => 36,  75 => 35,  61 => 34,  52 => 32,  49 => 27,  47 => 26,  44 => 5,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/motaded_theme/templates/navigation/menu--menu-mobile.html.twig", "/var/www/html/web/themes/custom/motaded_theme/templates/navigation/menu--menu-mobile.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["import" => 26, "macro" => 34, "if" => 36, "for" => 41, "set" => 43];
        static $filters = ["escape" => 38, "replace" => 48, "lower" => 48];
        static $functions = ["create_attribute" => 114, "link" => 119];

        try {
            $this->sandbox->checkSecurity(
                ['import', 'macro', 'if', 'for', 'set'],
                ['escape', 'replace', 'lower'],
                ['create_attribute', 'link'],
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
