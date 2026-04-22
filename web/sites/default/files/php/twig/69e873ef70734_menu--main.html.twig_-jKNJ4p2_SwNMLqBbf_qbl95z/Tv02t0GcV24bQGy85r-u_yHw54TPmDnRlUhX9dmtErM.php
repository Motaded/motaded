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

/* themes/custom/motaded_theme/templates/navigation/menu--main.html.twig */
class __TwigTemplate_f674f5b5019644b4b022b65346d44c7c extends Template
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
            ->checkDeprecations($context, ["_self", "items", "attributes", "menu_level", "loop"]);        yield from [];
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
                    yield "\t\t\t<div x-show=\"openMenu==='";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "menu_parent", [], "any", false, false, true, 38), "html", null, true);
                    yield "'\" x-transition.opacity @keydown.escape.window=\"openMenu=null\" class=\"absolute right-0 top-[91px] bg-white border w-full shadow-none z-50\" x-cloak>
\t\t\t\t<div class=\"max-w-[1320px] mx-auto px-4\">
\t\t\t\t\t<ul class=\"grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 lg:gap-6 py-10\">
\t\t\t\t\t";
                }
                // line 42
                yield "\t\t\t\t\t";
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(($context["items"] ?? null));
                $context['loop'] = [
                  'parent' => $context['_parent'],
                  'index0' => 0,
                  'index'  => 1,
                  'first'  => true,
                ];
                if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
                    $length = count($context['_seq']);
                    $context['loop']['revindex0'] = $length - 1;
                    $context['loop']['revindex'] = $length;
                    $context['loop']['length'] = $length;
                    $context['loop']['last'] = 1 === $length;
                }
                foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                    // line 43
                    yield "\t\t\t\t\t\t";
                    // line 44
                    $context["classes"] = [(((                    // line 45
($context["menu_level"] ?? null) == 0)) ? ("menu-item h-full flex items-center") : ("")), (((($tmp = CoreExtension::getAttribute($this->env, $this->source,                     // line 46
$context["item"], "in_active_trail", [], "any", false, false, true, 46)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("menu-item--active-trail") : (""))];
                    // line 49
                    yield "\t\t\t\t\t\t";
                    $context["title"] = (((($context["menu_level"] ?? null) == 0)) ? (("m" . CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "index0", [], "any", false, false, true, 49))) : (CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "menu_parent", [], "any", false, false, true, 49)));
                    // line 50
                    yield "\t\t\t\t\t\t";
                    if ((($context["menu_level"] ?? null) == 0)) {
                        // line 51
                        yield "\t\t\t\t\t\t\t<div";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "attributes", [], "any", false, false, true, 51), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 51), "html", null, true);
                        yield " ";
                        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["item"], "below", [], "any", false, false, true, 51)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                            yield " @click=\"openMenu = '";
                            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
                            yield "'\" data-mega ";
                        }
                        yield ">
\t\t\t\t\t\t\t";
                    }
                    // line 53
                    yield "\t\t\t\t\t\t\t";
                    if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["item"], "below", [], "any", false, false, true, 53)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 54
                        yield "\t\t\t\t\t\t\t\t";
                        $context["parent_link_classes"] = ["flex", "items-center", "gap-0", "px-2", "py-2", "w-fit", "rounded-md", "relative", "text-xs", "font-semibold", "transition", "h-10", "focus-visible:outline", "focus-visible:outline-2", "focus-visible:outline-gray-800", "whitespace-nowrap", "text-gray-900"];
                        // line 73
                        yield "\t\t\t\t\t\t\t\t<button type=\"button\" class=\"flex items-center gap-0 px-2 py-2 w-fit rounded-md relative text-xs font-semibold transition h-10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-gray-800 whitespace-nowrap text-gray-900\" :class=\"openMenu==='";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
                        yield "' && 'bg-primary-600 text-white after:content-[\\'\\'] after:absolute after:bottom-0 after:w-[80%] after:h-[4px] after:left-1/2 after:-translate-x-1/2 after:bg-primary-200 after:rounded-full'\" aria-haspopup=\"true\" :aria-expanded=\"openMenu==='";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
                        yield "'\" @click=\"openMenu = openMenu==='";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
                        yield "' ? null : '";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
                        yield "'\">
\t\t\t\t\t\t\t\t\t<span class=\"flex gap-2 px-1 items-center\">
\t\t\t\t\t\t\t\t\t\t";
                        // line 75
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "title", [], "any", false, false, true, 75), "html", null, true);
                        yield "
\t\t\t\t\t\t\t\t\t\t<span class=\"w-3\">
\t\t\t\t\t\t\t\t\t\t\t<img alt=\"chevron\" width=\"14\" height=\"14\" class=\"transition-transform\" :class=\"openMenu==='";
                        // line 77
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
                        yield "' ? 'rotate-180' : ''\" src=\"/themes/custom/motaded_theme/img/icon-arrow-down.svg\"/>
\t\t\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t</button>
\t\t\t\t\t\t\t\t";
                        // line 82
                        yield "\t\t\t\t\t\t\t\t";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($macros["menus"]->getTemplateForMacro("macro_menu_links", $context, 82, $this->getSourceContext())->macro_menu_links(...[CoreExtension::getAttribute($this->env, $this->source, $context["item"], "below", [], "any", false, false, true, 82), CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "setAttribute", ["menu_parent", ($context["title"] ?? null)], "method", false, false, true, 82), (($context["menu_level"] ?? null) + 1)]));
                        yield "
\t\t\t\t\t\t\t";
                    } else {
                        // line 84
                        yield "\t\t\t\t\t\t\t\t";
                        $context["internal_path"] = ((((($context["menu_level"] ?? null) == 1) && CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "url", [], "any", false, false, true, 84), "isRouted", [], "any", false, false, true, 84))) ? (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "url", [], "any", false, false, true, 84), "getInternalPath", [], "method", false, false, true, 84)) : (""));
                        // line 85
                        yield "\t\t\t\t\t\t\t\t";
                        $context["is_services_hub"] = ((($context["menu_level"] ?? null) == 1) && (($context["internal_path"] ?? null) == "services"));
                        // line 86
                        yield "\t\t\t\t\t\t\t\t";
                        if ((($context["menu_level"] ?? null) == 1)) {
                            // line 87
                            yield "\t\t\t\t\t\t\t\t\t<li";
                            if ((($tmp = ($context["is_services_hub"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                                yield " class=\"col-span-full flex justify-start mb-2 lg:mb-4 list-none\"";
                            }
                            yield ">
\t\t\t\t\t\t\t\t\t";
                        }
                        // line 89
                        yield "\t\t\t\t\t\t\t\t\t";
                        $context["link_classes"] = ["flex", "items-center", "gap-0", "px-2", "py-2", "w-fit", "m-0.5", "rounded-md", "text-xs", "leading-[1.2]", "font-medium", "transition", "h-full", "relative", "focus-visible:outline", "focus-visible:outline-2", "focus-visible:outline-gray-800", "whitespace-nowrap", "text-gray-900"];
                        // line 111
                        yield "\t\t\t\t\t\t\t\t\t";
                        $context["mega_hub_classes"] = ["inline-flex", "items-center", "justify-center", "rounded-full", "bg-primary-600", "text-white", "px-8", "py-3.5", "text-xs", "font-normal", "no-underline", "shadow-sm", "transition", "hover:bg-primary-700", "hover:no-underline", "focus-visible:outline", "focus-visible:outline-2", "focus-visible:outline-offset-2", "focus-visible:outline-gray-800"];
                        // line 132
                        yield "\t\t\t\t\t\t\t\t\t";
                        $context["mega_default_classes"] = ["hover:underline", "block"];
                        // line 133
                        yield "\t\t\t\t\t\t\t\t\t";
                        $context["tmp"] = (((($context["menu_level"] ?? null) == 0)) ? ((("<span>" . CoreExtension::getAttribute($this->env, $this->source, $context["item"], "title", [], "any", false, false, true, 133)) . "</span>")) : ((((($tmp = ($context["is_services_hub"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ((("<span class=\"leading-none\">" . CoreExtension::getAttribute($this->env, $this->source, $context["item"], "title", [], "any", false, false, true, 133)) . "</span>")) : ((("<p class=\"text-xs font-medium leading-[1.6]\">" . CoreExtension::getAttribute($this->env, $this->source, $context["item"], "title", [], "any", false, false, true, 133)) . "</p>")))));
                        // line 134
                        yield "\t\t\t\t\t\t\t\t\t";
                        $context["link_text"] = ('' === $tmp = implode('', iterator_to_array((function () use (&$context, $macros, $blocks) {
                            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(($context["tmp"] ?? null));
                            yield from [];
                        })(), false))) ? '' : new Markup($tmp, $this->env->getCharset());
                        // line 135
                        yield "\t\t\t\t\t\t\t\t\t";
                        $context["link_attributes"] = (((($context["menu_level"] ?? null) == 0)) ? ($this->extensions['Drupal\Core\Template\TwigExtension']->createAttribute(["class" =>                         // line 136
($context["link_classes"] ?? null)])) : ((((($tmp =                         // line 137
($context["is_services_hub"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->extensions['Drupal\Core\Template\TwigExtension']->createAttribute(["class" =>                         // line 138
($context["mega_hub_classes"] ?? null), "style" => "min-width: 180px;"])) : ($this->extensions['Drupal\Core\Template\TwigExtension']->createAttribute(["class" =>                         // line 139
($context["mega_default_classes"] ?? null)])))));
                        // line 141
                        yield "\t\t\t\t\t\t\t\t\t";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->getLink(($context["link_text"] ?? null), CoreExtension::getAttribute($this->env, $this->source, $context["item"], "url", [], "any", false, false, true, 141), ($context["link_attributes"] ?? null)), "html", null, true);
                        yield "
\t\t\t\t\t\t\t\t\t";
                        // line 142
                        if ((($context["menu_level"] ?? null) == 1)) {
                            // line 143
                            yield "\t\t\t\t\t\t\t\t\t</li>
\t\t\t\t\t\t\t\t";
                        }
                        // line 145
                        yield "\t\t\t\t\t\t\t";
                    }
                    // line 146
                    yield "\t\t\t\t\t\t\t";
                    if ((($context["menu_level"] ?? null) == 0)) {
                        // line 147
                        yield "\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t";
                    }
                    // line 149
                    yield "\t\t\t\t\t";
                    ++$context['loop']['index0'];
                    ++$context['loop']['index'];
                    $context['loop']['first'] = false;
                    if (isset($context['loop']['revindex0'], $context['loop']['revindex'])) {
                        --$context['loop']['revindex0'];
                        --$context['loop']['revindex'];
                        $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                    }
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 150
                yield "\t\t\t\t\t";
                if ((($context["menu_level"] ?? null) == 1)) {
                    // line 151
                    yield "\t\t\t\t\t</ul>
\t\t\t\t</div>
\t\t\t</div>
\t\t";
                }
                // line 155
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
        return "themes/custom/motaded_theme/templates/navigation/menu--main.html.twig";
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
        return array (  256 => 155,  250 => 151,  247 => 150,  233 => 149,  229 => 147,  226 => 146,  223 => 145,  219 => 143,  217 => 142,  212 => 141,  210 => 139,  209 => 138,  208 => 137,  207 => 136,  205 => 135,  199 => 134,  196 => 133,  193 => 132,  190 => 111,  187 => 89,  179 => 87,  176 => 86,  173 => 85,  170 => 84,  164 => 82,  157 => 77,  152 => 75,  140 => 73,  137 => 54,  134 => 53,  122 => 51,  119 => 50,  116 => 49,  114 => 46,  113 => 45,  112 => 44,  110 => 43,  92 => 42,  84 => 38,  81 => 37,  78 => 36,  75 => 35,  61 => 34,  52 => 32,  49 => 27,  47 => 26,  44 => 5,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/motaded_theme/templates/navigation/menu--main.html.twig", "/var/www/html/web/themes/custom/motaded_theme/templates/navigation/menu--main.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["import" => 26, "macro" => 34, "if" => 36, "for" => 42, "set" => 44];
        static $filters = ["escape" => 38, "raw" => 134];
        static $functions = ["create_attribute" => 136, "link" => 141];

        try {
            $this->sandbox->checkSecurity(
                ['import', 'macro', 'if', 'for', 'set'],
                ['escape', 'raw'],
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
