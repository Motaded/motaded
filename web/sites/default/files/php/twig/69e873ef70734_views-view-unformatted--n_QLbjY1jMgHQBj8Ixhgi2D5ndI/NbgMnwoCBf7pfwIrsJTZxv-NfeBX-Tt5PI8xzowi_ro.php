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

/* themes/custom/motaded_theme/templates/views/views-view-unformatted--news--block_4.html.twig */
class __TwigTemplate_27bb5d7c1412eab284cb8a97ecc6683f extends Template
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
        // line 17
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("motaded_theme/news-carousel"), "html", null, true);
        yield "
<!-- Desktop / Tablet -->
<div
\tclass=\"hidden md:grid grid-cols-1 md:grid-cols-2 gap-5 mt-5 w-full\">
\t<!-- Left column: compact list -->
\t<div class=\"hidden md:grid sm:grid-cols-2 md:grid-cols-1 gap-5\">
\t\t";
        // line 23
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(Twig\Extension\CoreExtension::slice($this->env->getCharset(), ($context["rows"] ?? null), 0, (Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["rows"] ?? null)) - 1)));
        foreach ($context['_seq'] as $context["_key"] => $context["row"]) {
            // line 24
            yield "\t\t\t<!-- Card 1 -->
\t\t\t<a href=\"";
            // line 25
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "url", [], "any", false, false, true, 25), "html", null, true);
            yield "\" class=\"block\">
\t\t\t\t<div class=\"bg-white rounded-xl p-4 h-full w-full border border-transparent hover:border-primary-600 shadow-sm transition\">
\t\t\t\t\t<div class=\"flex justify-between gap-4 h-full\">
\t\t\t\t\t\t<div class=\"flex-1\">
\t\t\t\t\t\t\t<div class=\"flex flex-col justify-between h-full\">
\t\t\t\t\t\t\t\t<p class=\"text-md font-semibold leading-[1.6]\">
\t\t\t\t\t\t\t\t\t";
            // line 31
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "title", [], "any", false, false, true, 31), "html", null, true);
            yield "
\t\t\t\t\t\t\t\t</p>
\t\t\t\t\t\t\t\t<p class=\"text-sm text-gray-600 mt-3 leading-[1.6]\">
\t\t\t\t\t\t\t\t\t";
            // line 34
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "created", [], "any", false, false, true, 34), "html", null, true);
            yield "
\t\t\t\t\t\t\t\t</p>
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t</div>
\t\t\t\t\t\t<div class=\"flex-shrink-0 w-[151px] h-[120px]\">
\t\t\t\t\t\t\t";
            // line 39
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 39), "srcset", [], "any", false, false, true, 39)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 40
                yield "\t\t\t\t\t\t\t\t<img loading=\"lazy\" decoding=\"async\" alt=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 40), "alt", [], "any", false, false, true, 40), "html", null, true);
                yield "\" src=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 40), "src", [], "any", false, false, true, 40), "html", null, true);
                yield "\" srcset=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 40), "srcset", [], "any", false, false, true, 40), "html", null, true);
                yield "\" sizes=\"(max-width: 767px) 100vw, 151px\" width=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 40), "width", [], "any", false, false, true, 40), "html", null, true);
                yield "\" height=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 40), "height", [], "any", false, false, true, 40), "html", null, true);
                yield "\" class=\"w-full h-full object-cover rounded-lg\"/>
\t\t\t\t\t\t\t";
            } else {
                // line 42
                yield "\t\t\t\t\t\t\t\t<img loading=\"lazy\" decoding=\"async\" alt=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 42), "alt", [], "any", false, false, true, 42), "html", null, true);
                yield "\" width=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 42), "width", [], "any", false, false, true, 42), "html", null, true);
                yield "\" height=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 42), "height", [], "any", false, false, true, 42), "html", null, true);
                yield "\" class=\"w-full h-full object-cover rounded-lg\" src=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 42), "src", [], "any", false, false, true, 42), "html", null, true);
                yield "\"/>
\t\t\t\t\t\t\t";
            }
            // line 44
            yield "\t\t\t\t\t\t</div>
\t\t\t\t\t</div>
\t\t\t\t</div>
\t\t\t</a>
\t\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['row'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 49
        yield "\t</div>

\t<!-- Right column: feature card -->
\t";
        // line 52
        $context["row"] = Twig\Extension\CoreExtension::last($this->env->getCharset(), ($context["rows"] ?? null));
        // line 53
        yield "\t<div class=\"hidden md:block h-full\">
\t\t<a href=\"";
        // line 54
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["row"] ?? null), "url", [], "any", false, false, true, 54), "html", null, true);
        yield "\" class=\"block h-full\">
\t\t\t<div class=\"bg-white rounded-xl p-4 h-full w-full border border-transparent hover:border-primary-600 shadow-sm transition\">
\t\t\t\t<div
\t\t\t\t\tclass=\"relative w-full h-full rounded-lg overflow-hidden\">
\t\t\t\t\t<!-- image -->
\t\t\t\t\t";
        // line 59
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["row"] ?? null), "image", [], "any", false, false, true, 59), "srcset", [], "any", false, false, true, 59)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 60
            yield "\t\t\t\t\t\t<img loading=\"lazy\" decoding=\"async\" alt=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["row"] ?? null), "image", [], "any", false, false, true, 60), "alt", [], "any", false, false, true, 60), "html", null, true);
            yield "\" src=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["row"] ?? null), "image", [], "any", false, false, true, 60), "src", [], "any", false, false, true, 60), "html", null, true);
            yield "\" srcset=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["row"] ?? null), "image", [], "any", false, false, true, 60), "srcset", [], "any", false, false, true, 60), "html", null, true);
            yield "\" sizes=\"(max-width: 767px) 100vw, (min-width: 768px) min(50vw, 640px)\" width=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["row"] ?? null), "image", [], "any", false, false, true, 60), "width", [], "any", false, false, true, 60), "html", null, true);
            yield "\" height=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["row"] ?? null), "image", [], "any", false, false, true, 60), "height", [], "any", false, false, true, 60), "html", null, true);
            yield "\" class=\"absolute inset-0 w-full h-full object-cover\"/>
\t\t\t\t\t";
        } else {
            // line 62
            yield "\t\t\t\t\t\t<img loading=\"lazy\" decoding=\"async\" alt=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["row"] ?? null), "image", [], "any", false, false, true, 62), "alt", [], "any", false, false, true, 62), "html", null, true);
            yield "\" width=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["row"] ?? null), "image", [], "any", false, false, true, 62), "width", [], "any", false, false, true, 62), "html", null, true);
            yield "\" height=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["row"] ?? null), "image", [], "any", false, false, true, 62), "height", [], "any", false, false, true, 62), "html", null, true);
            yield "\" class=\"absolute inset-0 w-full h-full object-cover\" src=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["row"] ?? null), "image", [], "any", false, false, true, 62), "src", [], "any", false, false, true, 62), "html", null, true);
            yield "\"/>
\t\t\t\t\t";
        }
        // line 64
        yield "\t\t\t\t\t<!-- gradient overlay -->
\t\t\t\t\t<div class=\"absolute inset-0 bg-gradient-to-t from-primary-700/90 via-primary-700/20 to-transparent\"></div>
\t\t\t\t\t<!-- text -->
\t\t\t\t\t<div class=\"absolute bottom-0 left-0 w-full px-5 py-4\">
\t\t\t\t\t\t<p class=\"text-lg font-medium text-white leading-[1.6]\">
\t\t\t\t\t\t\t";
        // line 69
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["row"] ?? null), "title", [], "any", false, false, true, 69), "html", null, true);
        yield "
\t\t\t\t\t\t</p>
\t\t\t\t\t\t<p class=\"text-sm text-white/90 mt-3 leading-[1.6]\">
\t\t\t\t\t\t\t";
        // line 72
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["row"] ?? null), "created", [], "any", false, false, true, 72), "html", null, true);
        yield "
\t\t\t\t\t\t</p>
\t\t\t\t\t</div>
\t\t\t\t</div>
\t\t\t</div>
\t\t</a>
\t</div>
</div>

<!-- Mobile Carousel -->
<div class=\"block md:hidden my-10\" x-data=\"newsCarousel\" data-carousel-length=\"";
        // line 82
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["rows"] ?? null)), "html", null, true);
        yield "\">
\t<div class=\"relative\">
\t\t<div class=\"overflow-hidden rounded-xl\">
\t\t\t";
        // line 85
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["rows"] ?? null));
        foreach ($context['_seq'] as $context["key"] => $context["row"]) {
            // line 86
            yield "\t\t\t\t<a href=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "url", [], "any", false, false, true, 86), "html", null, true);
            yield "\" class=\"block min-h-72\" x-show=\"current === ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $context["key"], "html", null, true);
            yield "\" x-transition.opacity x-cloak>
\t\t\t\t\t<div class=\"bg-white rounded-xl p-4 h-full border border-transparent hover:border-primary-600 shadow-sm transition\">
\t\t\t\t\t\t<div class=\"flex flex-col justify-between h-full\">
\t\t\t\t\t\t\t<div class=\"relative w-full\">
\t\t\t\t\t\t\t\t";
            // line 90
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 90), "srcset", [], "any", false, false, true, 90)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 91
                yield "\t\t\t\t\t\t\t\t\t<img loading=\"lazy\" decoding=\"async\" alt=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 91), "alt", [], "any", false, false, true, 91), "html", null, true);
                yield "\" src=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 91), "src", [], "any", false, false, true, 91), "html", null, true);
                yield "\" srcset=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 91), "srcset", [], "any", false, false, true, 91), "html", null, true);
                yield "\" sizes=\"100vw\" width=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 91), "width", [], "any", false, false, true, 91), "html", null, true);
                yield "\" height=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 91), "height", [], "any", false, false, true, 91), "html", null, true);
                yield "\" class=\"rounded-lg w-full h-[210px] object-cover\"/>
\t\t\t\t\t\t\t\t";
            } else {
                // line 93
                yield "\t\t\t\t\t\t\t\t\t<img loading=\"lazy\" decoding=\"async\" alt=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 93), "alt", [], "any", false, false, true, 93), "html", null, true);
                yield "\" width=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 93), "width", [], "any", false, false, true, 93), "html", null, true);
                yield "\" height=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 93), "height", [], "any", false, false, true, 93), "html", null, true);
                yield "\" class=\"rounded-lg w-full h-[210px] object-cover\" src=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 93), "src", [], "any", false, false, true, 93), "html", null, true);
                yield "\"/>
\t\t\t\t\t\t\t\t";
            }
            // line 95
            yield "\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t<div class=\"mt-5 flex flex-col justify-between flex-1\">
\t\t\t\t\t\t\t\t<p class=\"text-md font-medium leading-[1.6]\">";
            // line 97
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "title", [], "any", false, false, true, 97), "html", null, true);
            yield "</p>
\t\t\t\t\t\t\t\t<p class=\"text-sm text-gray-600 mt-3 leading-[1.6]\">
\t\t\t\t\t\t\t\t\t";
            // line 99
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "created", [], "any", false, false, true, 99), "html", null, true);
            yield "
\t\t\t\t\t\t\t\t</p>
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t</div>
\t\t\t\t\t</div>
\t\t\t\t</a>
\t\t\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['key'], $context['row'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 106
        yield "\t\t</div>

\t\t<!-- Controls -->
\t\t<div class=\"mt-4 flex items-center justify-between\">
\t\t\t<button type=\"button\" class=\"px-3 py-1.5 rounded border text-sm hover:bg-gray-50\" @click=\"prev()\">
\t\t\t\t";
        // line 111
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Prev"));
        yield "
\t\t\t</button>
\t\t\t<div class=\"flex gap-3\">
\t\t\t\t";
        // line 114
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["rows"] ?? null));
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
        foreach ($context['_seq'] as $context["_key"] => $context["row"]) {
            // line 115
            yield "\t\t\t\t\t<button type=\"button\" class=\"size-3 rounded-full\" aria-label=\"news-carousel-";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "index0", [], "any", false, false, true, 115), "html", null, true);
            yield "\" :class=\"current === ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "index0", [], "any", false, false, true, 115), "html", null, true);
            yield " ? 'bg-primary-600' : 'bg-gray-300'\" @click=\"go(";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "index0", [], "any", false, false, true, 115), "html", null, true);
            yield ")\"></button>
\t\t\t\t";
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
        unset($context['_seq'], $context['_key'], $context['row'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 117
        yield "\t\t\t</div>
\t\t\t<button type=\"button\" class=\"px-3 py-1.5 rounded border text-sm hover:bg-gray-50\" @click=\"next()\">
\t\t\t\t";
        // line 119
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Next"));
        yield "
\t\t\t</button>
\t\t</div>
\t</div>
</div>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["rows", "loop"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/motaded_theme/templates/views/views-view-unformatted--news--block_4.html.twig";
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
        return array (  318 => 119,  314 => 117,  293 => 115,  276 => 114,  270 => 111,  263 => 106,  250 => 99,  245 => 97,  241 => 95,  229 => 93,  215 => 91,  213 => 90,  203 => 86,  199 => 85,  193 => 82,  180 => 72,  174 => 69,  167 => 64,  155 => 62,  141 => 60,  139 => 59,  131 => 54,  128 => 53,  126 => 52,  121 => 49,  111 => 44,  99 => 42,  85 => 40,  83 => 39,  75 => 34,  69 => 31,  60 => 25,  57 => 24,  53 => 23,  44 => 17,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/motaded_theme/templates/views/views-view-unformatted--news--block_4.html.twig", "/var/www/html/web/themes/custom/motaded_theme/templates/views/views-view-unformatted--news--block_4.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["for" => 23, "if" => 39, "set" => 52];
        static $filters = ["escape" => 17, "slice" => 23, "length" => 23, "last" => 52, "t" => 111];
        static $functions = ["attach_library" => 17];

        try {
            $this->sandbox->checkSecurity(
                ['for', 'if', 'set'],
                ['escape', 'slice', 'length', 'last', 't'],
                ['attach_library'],
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
