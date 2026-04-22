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

/* @motaded_theme/includes/header.html.twig */
class __TwigTemplate_3a9c95c51b0d900b449c44bdb9ddeaf2 extends Template
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
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("motaded_theme/alpine"), "html", null, true);
        yield "

<header
\tx-data=\"{ openMenu: null, mobileOpen: false, mobileSection: null }\" class=\"sticky top-0 z-[201] h-[91px] bg-white border-b border-gray-200\" @keydown.escape.window=\"openMenu=null; mobileOpen=false; mobileSection=null\">
\t<!-- Desktop -->
\t<div class=\"max-w-[1320px] mx-auto px-4 hidden md:block h-full\">
\t\t<div class=\"flex items-center h-full py-1 gap-4\">
\t\t\t<!-- Left: Logo -->
\t\t\t<div class=\"shrink-0 flex items-center\">
\t\t\t\t<a href=\"";
        // line 14
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["front_page"] ?? null), "html", null, true);
        yield "\" class=\"block\">
\t\t\t\t\t<img alt=\"Motaded\" class=\"h-9 w-auto max-w-[120px] object-contain object-left\" src=\"";
        // line 15
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["logo_path"] ?? null), "html", null, true);
        yield "\" width=\"500\" height=\"92\"/>
\t\t\t\t</a>
\t\t\t</div>

\t\t\t<!-- Center: Nav -->
\t\t\t<nav class=\"hidden lg:flex flex-1 min-w-0 justify-start items-center h-full\" @click.outside=\"openMenu=null\">
\t\t\t\t";
        // line 21
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "main_menu", [], "any", false, false, true, 21), "html", null, true);
        yield "
\t\t\t</nav>

\t\t\t";
        // line 24
        $context["search_url"] = ($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getUrl("<front>")) . "/search/content");
        // line 25
        yield "\t\t\t<!-- Right: CTA / Search / Lang -->
\t\t\t<div class=\"shrink-0 flex items-center justify-end gap-2\">
\t\t\t\t";
        // line 27
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "header_cta", [], "any", false, false, true, 27)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 28
            yield "\t\t\t\t\t<div class=\"flex items-center shrink-0\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "header_cta", [], "any", false, false, true, 28), "html", null, true);
            yield "</div>
\t\t\t\t";
        }
        // line 30
        yield "\t\t\t\t<div
\t\t\t\t\tclass=\"flex gap-1.5 lg:gap-2 text-xs\">
\t\t\t\t\t<!-- Search -->
\t\t\t\t\t<div class=\"relative flex items-center\">
\t\t\t\t\t\t<a type=\"button\" class=\"inline-flex items-center justify-center font-medium transition rounded-sm text-black text-xs size-8 focus-visible:outline focus-visible:outline-2 focus-visible:outline-gray-800\" aria-label=\"Search\" href=\"";
        // line 34
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::replace(($context["search_url"] ?? null), ["//s" => "/s"]), "html", null, true);
        yield "\">
\t\t\t\t\t\t\t<img alt=\"search\" width=\"24\" height=\"24\" src=\"/themes/custom/motaded_theme/img/icon-search.svg\"/>
\t\t\t\t\t\t</a>
\t\t\t\t\t\t<p class=\"text-xs font-medium leading-[1.6] hidden xl:block ml-1 mb-0\">
\t\t\t\t\t\t\t";
        // line 38
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Search"));
        yield "
\t\t\t\t\t\t</p>
\t\t\t\t\t</div>

\t\t\t\t\t<!-- Language -->
\t\t\t\t\t<span class=\"flex justify-center px-0 text-xs\">
\t\t\t\t\t\t<div class=\"flex gap-1.5 items-center\">
\t\t\t\t\t\t\t";
        // line 45
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "secondary_menu", [], "any", false, false, true, 45), "html", null, true);
        yield "
\t\t\t\t\t\t</div>
\t\t\t\t\t</span>
\t\t\t\t</div>

\t\t\t\t<!-- Burger (desktop hidden anyway) -->
\t\t\t\t<div class=\"lg:hidden\">
\t\t\t\t\t<button aria-label=\"Menu\" class=\"w-12 h-12 grid place-items-center\">
\t\t\t\t\t\t<img alt=\"menu\" width=\"24\" height=\"24\" src=\"/themes/custom/motaded_theme/img/icon-menu.svg\"/>
\t\t\t\t\t</button>
\t\t\t\t</div>
\t\t\t</div>
\t\t</div>
\t</div>

\t<!-- Mobile (structure preserved, no mega here) -->
\t<div class=\"block md:hidden py-5\">
\t\t<div
\t\t\tclass=\"max-w-[1320px] mx-auto px-4 relative flex items-center justify-between py-2\">
\t\t\t<!-- Left: Search + Lang -->
\t\t\t<div class=\"flex items-center gap-2\">
\t\t\t\t<a type=\"button\" class=\"inline-flex items-center justify-center transition text-black text-sm size-10 rounded-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-gray-800\" aria-label=\"Search\" href=\"";
        // line 66
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::replace(($context["search_url"] ?? null), ["//s" => "/s"]), "html", null, true);
        yield "\">
\t\t\t\t\t<img alt=\"search\" width=\"24\" height=\"24\" src=\"/themes/custom/motaded_theme/img/icon-search.svg\"/>
\t\t\t\t</a>

\t\t\t\t<span class=\"flex justify-center px-0\">
\t\t\t\t\t<div class=\"flex gap-1.5 items-center\">
\t\t\t\t\t\t";
        // line 72
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "secondary_menu", [], "any", false, false, true, 72), "html", null, true);
        yield "
\t\t\t\t\t</div>
\t\t\t\t</a>
\t\t\t</div>

\t\t\t<!-- Right: Burger -->
\t\t\t<div class=\"flex items-center gap-2\">
\t\t\t\t<button aria-label=\"Menu\" class=\"w-12 h-12 grid place-items-center\" @click=\"mobileOpen = true\">
\t\t\t\t\t<img alt=\"menu\" width=\"24\" height=\"24\" src=\"/themes/custom/motaded_theme/img/icon-menu.svg\"/>
\t\t\t\t</button>
\t\t\t</div>
\t\t</div>

\t\t<!-- Drawer: overlay -->
\t\t<div x-show=\"mobileOpen\" x-transition.opacity class=\"fixed inset-0 z-[202] bg-black/40\" @click=\"mobileOpen=false\" x-cloak></div>

\t\t<!-- Drawer: panel -->
\t\t<aside
\t\t\tx-show=\"mobileOpen\" x-cloak x-transition:enter=\"transition ease-out duration-200\" x-transition:enter-start=\"-translate-x-full opacity-0\" x-transition:enter-end=\"translate-x-0 opacity-100\" x-transition:leave=\"transition ease-in duration-150\" x-transition:leave-start=\"translate-x-0 opacity-100\" x-transition:leave-end=\"-translate-x-full opacity-0\" class=\"fixed inset-y-0 left-0 z-[203] w-[88%] max-w-[360px] bg-white shadow-xl flex flex-col\" @click.stop role=\"dialog\" aria-modal=\"true\" aria-label=\"Mobile menu\">
\t\t\t<!-- Drawer header -->
\t\t\t<div class=\"flex items-center justify-between px-4 py-3 border-b border-gray-200\">
\t\t\t\t<a href=\"";
        // line 93
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["front_page"] ?? null), "html", null, true);
        yield "\" class=\"inline-flex items-center gap-2 shrink-0\">
\t\t\t\t\t<img alt=\"Motaded\" class=\"h-8 w-auto max-w-[100px] object-contain\" src=\"";
        // line 94
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["logo_path"] ?? null), "html", null, true);
        yield "\" width=\"500\" height=\"92\"/>
\t\t\t\t</a>
\t\t\t\t<button class=\"size-10 grid place-items-center rounded-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-gray-800\" @click=\"mobileOpen=false; mobileSection=null\" aria-label=\"Close menu\">
\t\t\t\t\t<img alt=\"close\" width=\"20\" height=\"20\" src=\"/themes/custom/motaded_theme/img/icon-close.svg\"/>
\t\t\t\t</button>
\t\t\t</div>

\t\t\t<!-- Drawer body (scrollable) -->
\t\t\t<div class=\"flex-1 min-h-0 flex flex-col justify-between overflow-y-auto px-2 py-2\">
\t\t\t\t";
        // line 103
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "menu_mobile", [], "any", false, false, true, 103), "html", null, true);
        yield "
\t\t\t</div>

\t\t\t<!-- Drawer CTA (above footer, fixed) -->
\t\t\t";
        // line 107
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "drawer_cta", [], "any", false, false, true, 107)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 108
            yield "\t\t\t<div class=\"drawer-cta shrink-0 py-3 pl-4 pr-4 border-t border-gray-200\">
\t\t\t\t";
            // line 109
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "drawer_cta", [], "any", false, false, true, 109), "html", null, true);
            yield "
\t\t\t</div>
\t\t\t";
        }
        // line 112
        yield "
\t\t\t<!-- Drawer footer (optional) -->
\t\t\t<div class=\"mt-auto px-4 py-3 border-t border-gray-200\">
\t\t\t\t<div class=\"flex items-center gap-2\">
\t\t\t\t\t<a href=\"";
        // line 116
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::replace(($context["search_url"] ?? null), ["//s" => "/s"]), "html", null, true);
        yield "\" type=\"button\" class=\"inline-flex items-center justify-center font-medium transition text-black text-sm px-3 py-2 rounded-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-gray-800\" aria-label=\"Search\">
\t\t\t\t\t\t<img alt=\"search\" width=\"20\" height=\"20\" src=\"/themes/custom/motaded_theme/img/icon-search.svg\"/>
\t\t\t\t\t\t<span class=\"ml-2\">";
        // line 118
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Search"));
        yield "</span>
\t\t\t\t\t</a>
\t\t\t\t\t<span class=\"inline-flex items-center gap-2 px-3 py-2 rounded-md hover:bg-gray-100\">
\t\t\t\t\t\t";
        // line 121
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "secondary_menu", [], "any", false, false, true, 121), "html", null, true);
        yield "
\t\t\t\t\t</a>
\t\t\t\t</div>
\t\t\t</div>

\t\t</aside>
\t</div>
</header>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["front_page", "logo_path", "page"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "@motaded_theme/includes/header.html.twig";
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
        return array (  220 => 121,  214 => 118,  209 => 116,  203 => 112,  197 => 109,  194 => 108,  192 => 107,  185 => 103,  173 => 94,  169 => 93,  145 => 72,  136 => 66,  112 => 45,  102 => 38,  95 => 34,  89 => 30,  83 => 28,  81 => 27,  77 => 25,  75 => 24,  69 => 21,  60 => 15,  56 => 14,  44 => 5,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "@motaded_theme/includes/header.html.twig", "/var/www/html/web/themes/custom/motaded_theme/templates/includes/header.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 24, "if" => 27];
        static $filters = ["escape" => 5, "render" => 24, "replace" => 34, "t" => 38];
        static $functions = ["attach_library" => 5, "url" => 24];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if'],
                ['escape', 'render', 'replace', 't'],
                ['attach_library', 'url'],
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
