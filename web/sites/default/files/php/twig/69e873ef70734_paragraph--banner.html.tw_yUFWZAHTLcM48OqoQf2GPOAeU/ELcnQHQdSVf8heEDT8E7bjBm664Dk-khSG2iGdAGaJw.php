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

/* themes/custom/motaded_theme/templates/paragraphs/paragraph--banner.html.twig */
class __TwigTemplate_989026a5bec3870139839d4ac91b98b2 extends Template
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
            'paragraph' => [$this, 'block_paragraph'],
            'content' => [$this, 'block_content'],
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
        // line 47
        $context["classes"] = ["paragraph", ("paragraph--type--" . \Drupal\Component\Utility\Html::getClass(CoreExtension::getAttribute($this->env, $this->source,         // line 49
($context["paragraph"] ?? null), "bundle", [], "any", false, false, true, 49))), (((($tmp =         // line 50
($context["view_mode"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (("paragraph--view-mode--" . \Drupal\Component\Utility\Html::getClass(($context["view_mode"] ?? null)))) : ("")), (((($tmp =  !CoreExtension::getAttribute($this->env, $this->source,         // line 51
($context["paragraph"] ?? null), "isPublished", [], "method", false, false, true, 51)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("paragraph--unpublished") : (""))];
        // line 54
        yield from $this->unwrap()->yieldBlock('paragraph', $context, $blocks);
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["paragraph", "view_mode", "attributes", "content", "loop"]);        yield from [];
    }

    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_paragraph(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 55
        yield "  <div";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 55), "html", null, true);
        yield ">
    ";
        // line 56
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 212
        yield "
    ";
        // line 214
        yield "    ";
        if ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (($_v0 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_show_quick_links", [], "any", false, false, true, 214)) && is_array($_v0) || $_v0 instanceof ArrayAccess && in_array($_v0::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v0["#items"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_show_quick_links", [], "any", false, false, true, 214), "#items", [], "array", false, false, true, 214)), 0, [], "any", false, false, true, 214), "value", [], "any", false, false, true, 214) == 1)) {
            // line 215
            yield "      ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("motaded_theme/quick-links"), "html", null, true);
            yield "

      <section class=\"max-w-[1320px] layout-container mx-auto px-4 mt-6\" x-data=\"quickLinks()\" x-init=\"init()\">
        <div class=\"flex flex-wrap gap-2 border-b border-gray-200 pb-4\">
          ";
            // line 220
            yield "          <template x-for=\"item in links\" :key=\"item.id\">
            <a
              :href=\"`#\${item.id}`\"
              @click.prevent=\"scrollTo(item.id)\"
              class=\"inline-flex items-center rounded-full border border-gray-300 px-3 py-1.5 text-sm bg-white hover:bg-gray-50\"
              x-text=\"item.label\"
            ></a>
        </template>
        </div>
      </section>
    ";
        }
        // line 231
        yield "  
  </div>
";
        yield from [];
    }

    // line 56
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 57
        yield "    ";
        $context["banner_type"] = (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_banner_type", [], "any", false, true, true, 57), "#items", [], "array", false, true, true, 57), 0, [], "any", false, true, true, 57), "value", [], "any", true, true, true, 57) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (($_v1 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_banner_type", [], "any", false, false, true, 57)) && is_array($_v1) || $_v1 instanceof ArrayAccess && in_array($_v1::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v1["#items"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_banner_type", [], "any", false, false, true, 57), "#items", [], "array", false, false, true, 57)), 0, [], "any", false, false, true, 57), "value", [], "any", false, false, true, 57)))) ? (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (($_v2 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_banner_type", [], "any", false, false, true, 57)) && is_array($_v2) || $_v2 instanceof ArrayAccess && in_array($_v2::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v2["#items"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_banner_type", [], "any", false, false, true, 57), "#items", [], "array", false, false, true, 57)), 0, [], "any", false, false, true, 57), "value", [], "any", false, false, true, 57)) : (""));
        // line 58
        yield "    ";
        if ((($context["banner_type"] ?? null) == "banner_style_1")) {
            // line 59
            yield "      <section
        class=\"relative isolate banner-hero-fullbleed\">
        <div class=\"banner-hero-fullbleed__bg absolute inset-0 z-0 bg-gray-200 md:bg-transparent\">
          ";
            // line 62
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["paragraph"] ?? null), "field_media", [], "any", false, false, true, 62), "entity", [], "any", false, false, true, 62)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 63
                yield "            <div class=\"banner-hero-fullbleed__media\">
              ";
                // line 64
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Drupal\twig_tweak\TwigTweakExtension::drupalEntity("media", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["paragraph"] ?? null), "field_media", [], "any", false, false, true, 64), "entity", [], "any", false, false, true, 64), "id", [], "any", false, false, true, 64), "hero_banner"), "html", null, true);
                yield "
            </div>
          ";
            }
            // line 67
            yield "          <!-- dark-to-transparent gradient for legibility -->
          <div class=\"pointer-events-none absolute inset-0 z-[1] bg-gradient-to-b from-black/50 via-black/40 to-black/20\"></div>
        </div>

        <!-- Hero copy -->
        <div class=\"relative z-10\">
          <div class=\"mx-auto max-w-[72rem] px-4 pt-20 pb-12 sm:pt-24 sm:pb-16 lg:pt-28\">
            <div class=\"mx-auto text-center max-w-[46rem]\">

              ";
            // line 76
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_title", [], "any", false, false, true, 76), 0, [], "any", false, false, true, 76))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 77
                yield "                <h1 class=\"text-white/95 font-semibold leading-tight [font-size:clamp(1.75rem,3vw,2.5rem)]\">
                  ";
                // line 78
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_title", [], "any", false, false, true, 78), 0, [], "any", false, false, true, 78), "html", null, true);
                yield "
                </h1>
              ";
            }
            // line 81
            yield "              ";
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 81), 0, [], "any", false, false, true, 81))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 82
                yield "                <div class=\"mt-4 text-white/90 leading-relaxed [font-size:clamp(0.95rem,1.5vw,1.125rem)]\">
                  ";
                // line 83
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 83), 0, [], "any", false, false, true, 83), "html", null, true);
                yield "
                </div>
              ";
            }
            // line 86
            yield "

              <!-- CTAs -->
              ";
            // line 89
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_links", [], "any", false, false, true, 89)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 90
                yield "                <div class=\"mt-6 flex flex-wrap items-center justify-center gap-3\">

                  ";
                // line 92
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["paragraph"] ?? null), "field_links", [], "any", false, false, true, 92));
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
                foreach ($context['_seq'] as $context["key"] => $context["item"]) {
                    // line 93
                    yield "                    ";
                    $context["link"] = (($_v3 = (($_v4 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_links", [], "any", false, false, true, 93)) && is_array($_v4) || $_v4 instanceof ArrayAccess && in_array($_v4::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v4[$context["key"]] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_links", [], "any", false, false, true, 93), $context["key"], [], "array", false, false, true, 93))) && is_array($_v3) || $_v3 instanceof ArrayAccess && in_array($_v3::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v3["#url"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, (($_v5 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_links", [], "any", false, false, true, 93)) && is_array($_v5) || $_v5 instanceof ArrayAccess && in_array($_v5::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v5[$context["key"]] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_links", [], "any", false, false, true, 93), $context["key"], [], "array", false, false, true, 93)), "#url", [], "array", false, false, true, 93));
                    // line 94
                    yield "                    ";
                    $context["title"] = (($_v6 = (($_v7 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_links", [], "any", false, false, true, 94)) && is_array($_v7) || $_v7 instanceof ArrayAccess && in_array($_v7::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v7[$context["key"]] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_links", [], "any", false, false, true, 94), $context["key"], [], "array", false, false, true, 94))) && is_array($_v6) || $_v6 instanceof ArrayAccess && in_array($_v6::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v6["#title"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, (($_v8 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_links", [], "any", false, false, true, 94)) && is_array($_v8) || $_v8 instanceof ArrayAccess && in_array($_v8::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v8[$context["key"]] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_links", [], "any", false, false, true, 94), $context["key"], [], "array", false, false, true, 94)), "#title", [], "array", false, false, true, 94));
                    // line 95
                    yield "                    ";
                    if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "last", [], "any", false, false, true, 95)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 96
                        yield "                      <a href=\"";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["link"] ?? null), "html", null, true);
                        yield "\" class=\"inline-flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-sm font-semibold text-white backdrop-blur hover:bg-white/15 hover:shadow-sm hover:-translate-y-[1px] active:translate-y-0 transition will-change-transform focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/80\" aria-label=\"";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
                        yield "\">
                        ";
                        // line 97
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
                        yield "
                        <img alt=\"\" width=\"18\" height=\"18\" class=\"-rotate-90 rtl:rotate-90\" src=\"/themes/custom/motaded_theme/img/icon-arrow-down.svg\"/>
                      </a>
                    ";
                    } else {
                        // line 101
                        yield "                      <a href=\"";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["link"] ?? null), "html", null, true);
                        yield "\" class=\"inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-primary-600 shadow-sm hover:shadow-md hover:-translate-y-[1px] active:translate-y-0 transition will-change-transform focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/80\" aria-label=\"";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
                        yield "\">
                        ";
                        // line 102
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
                        yield "
                        <img alt=\"\" width=\"18\" height=\"18\" class=\"-rotate-90 rtl:rotate-90\" src=\"/themes/custom/motaded_theme/img/icon-arrow-down.svg\"/>
                      </a>
                    ";
                    }
                    // line 106
                    yield "                  ";
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
                unset($context['_seq'], $context['key'], $context['item'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 107
                yield "                </div>
              ";
            }
            // line 109
            yield "

              <!-- subtle scroll cue (hidden on users who prefer no motion) -->
              <div class=\"mt-8 hidden sm:flex items-center justify-center motion-safe:animate-bounce\">
                <span class=\"sr-only\">Scroll</span>
                <img alt=\"\" width=\"22\" height=\"22\" src=\"/themes/custom/motaded_theme/img/icon-scroll.svg\" class=\"opacity-50 invert-[1.2]\"/>
              </div>
            </div>
          </div>
        </div>

        <!-- 3 key reasons -->
        <div class=\"relative z-10\">
          <div class=\"mx-auto max-w-[82.5rem] px-4 translate-y-12 sm:translate-y-16\">
            <div
              class=\"grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3\">
              <!-- Card -->
              ";
            // line 126
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_paragraphs", [], "any", false, false, true, 126), "html", null, true);
            yield "
            </div>
          </div>

          <!-- spacer to offset translated cards -->
          <div class=\"h-24 sm:h-28\"></div>
        </div>
      </section>
    ";
        } elseif ((        // line 134
($context["banner_type"] ?? null) == "banner_style_3")) {
            // line 135
            yield "      ";
            yield from $this->load("@motaded_theme/paragraphs/hero--packages.html.twig", 135)->unwrap()->yield($context);
            // line 136
            yield "    ";
        } else {
            // line 137
            yield "
      <header class=\"max-w-[1320px] mx-auto px-4 mt-6\">
        <div
          class=\"relative overflow-hidden rounded-2xl border border-gray-200 bg-white\"
        >
          <div class=\"grid md:grid-cols-2\">
            <div class=\"p-8 md:p-12\">
              ";
            // line 144
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_title", [], "any", false, false, true, 144), 0, [], "any", false, false, true, 144))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 145
                yield "              <h1
                class=\"text-3xl md:text-5xl font-extrabold leading-tight text-[#0F2A1D]\"
              >
                  ";
                // line 148
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_title", [], "any", false, false, true, 148), 0, [], "any", false, false, true, 148), "html", null, true);
                yield "
              </h1>
              ";
            }
            // line 151
            yield "              ";
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 151), 0, [], "any", false, false, true, 151))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 152
                yield "              <p class=\"mt-4 text-lg text-gray-800\">
               ";
                // line 153
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 153), 0, [], "any", false, false, true, 153), "html", null, true);
                yield "
              </p>
              ";
            }
            // line 156
            yield "
              ";
            // line 157
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_links", [], "any", false, false, true, 157)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 158
                yield "              <div class=\"mt-6 flex flex-wrap gap-3\">
                ";
                // line 159
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["paragraph"] ?? null), "field_links", [], "any", false, false, true, 159));
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
                foreach ($context['_seq'] as $context["key"] => $context["item"]) {
                    // line 160
                    yield "                ";
                    $context["link"] = (($_v9 = (($_v10 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_links", [], "any", false, false, true, 160)) && is_array($_v10) || $_v10 instanceof ArrayAccess && in_array($_v10::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v10[$context["key"]] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_links", [], "any", false, false, true, 160), $context["key"], [], "array", false, false, true, 160))) && is_array($_v9) || $_v9 instanceof ArrayAccess && in_array($_v9::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v9["#url"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, (($_v11 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_links", [], "any", false, false, true, 160)) && is_array($_v11) || $_v11 instanceof ArrayAccess && in_array($_v11::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v11[$context["key"]] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_links", [], "any", false, false, true, 160), $context["key"], [], "array", false, false, true, 160)), "#url", [], "array", false, false, true, 160));
                    // line 161
                    yield "                ";
                    $context["title"] = (($_v12 = (($_v13 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_links", [], "any", false, false, true, 161)) && is_array($_v13) || $_v13 instanceof ArrayAccess && in_array($_v13::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v13[$context["key"]] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_links", [], "any", false, false, true, 161), $context["key"], [], "array", false, false, true, 161))) && is_array($_v12) || $_v12 instanceof ArrayAccess && in_array($_v12::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v12["#title"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, (($_v14 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_links", [], "any", false, false, true, 161)) && is_array($_v14) || $_v14 instanceof ArrayAccess && in_array($_v14::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v14[$context["key"]] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_links", [], "any", false, false, true, 161), $context["key"], [], "array", false, false, true, 161)), "#title", [], "array", false, false, true, 161));
                    // line 162
                    yield "                ";
                    if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "last", [], "any", false, false, true, 162)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 163
                        yield "                  <a
                  href=\"";
                        // line 164
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["link"] ?? null), "html", null, true);
                        yield "\"
                  class=\"inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-5 py-3 text-sm font-semibold hover:bg-gray-50\"
                  >";
                        // line 166
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
                        yield "</a
                >
                ";
                    } else {
                        // line 169
                        yield "                <a
                  href=\"";
                        // line 170
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["link"] ?? null), "html", null, true);
                        yield "\"
                  class=\"inline-flex items-center gap-2 rounded-md bg-primary-600 text-white px-5 py-3 text-sm font-semibold hover:bg-primary-700\"
                  >";
                        // line 172
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
                        yield "</a
                >
                ";
                    }
                    // line 175
                    yield "                ";
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
                unset($context['_seq'], $context['key'], $context['item'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 176
                yield "              </div>
              ";
            }
            // line 178
            yield "
              <!-- Value badges -->
              <dl class=\"mt-8 grid grid-cols-2 gap-4 text-sm\">
              ";
            // line 181
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_paragraphs", [], "any", false, false, true, 181));
            foreach ($context['_seq'] as $context["key"] => $context["item"]) {
                // line 182
                yield "              ";
                $context["para"] = (($_v15 = $context["item"]) && is_array($_v15) || $_v15 instanceof ArrayAccess && in_array($_v15::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v15["#paragraph"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, $context["item"], "#paragraph", [], "array", false, false, true, 182));
                // line 183
                yield "                ";
                if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(($context["para"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 184
                    yield "               <div class=\"rounded-xl border border-green-200 bg-green-50 p-4\">
                  <dt class=\"font-semibold text-green-900\">";
                    // line 185
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["para"] ?? null), "field_title", [], "any", false, false, true, 185), "value", [], "any", false, false, true, 185), "html", null, true);
                    yield "</dt>
                  <dd class=\"mt-1 text-green-800\">
                    ";
                    // line 187
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["para"] ?? null), "field_body", [], "any", false, false, true, 187), "value", [], "any", false, false, true, 187));
                    yield "
                  </dd>
                </div>
                ";
                }
                // line 191
                yield "              ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['key'], $context['item'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 192
            yield "
              </dl>
            </div>
            <div class=\"relative\">
              <img
                src=\"";
            // line 197
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->getFileUrl(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (($_v16 = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_media", [], "any", false, false, true, 197), 0, [], "any", false, false, true, 197)) && is_array($_v16) || $_v16 instanceof ArrayAccess && in_array($_v16::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v16["#media"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_media", [], "any", false, false, true, 197), 0, [], "any", false, false, true, 197), "#media", [], "array", false, false, true, 197)), "field_media_image", [], "any", false, false, true, 197), "entity", [], "any", false, false, true, 197), "uri", [], "any", false, false, true, 197), "value", [], "any", false, false, true, 197)), "html", null, true);
            yield "\"
                alt=\"Riyadh skyline at dusk\"
                class=\"h-full w-full object-cover\"
                loading=\"lazy\"
              />
              <div
                class=\"absolute inset-0 bg-gradient-to-t from-black/30 to-transparent\"
              ></div>
            </div>
          </div>
        </div>
      </header>

     ";
        }
        // line 211
        yield "    ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/motaded_theme/templates/paragraphs/paragraph--banner.html.twig";
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
        return array (  465 => 211,  448 => 197,  441 => 192,  435 => 191,  428 => 187,  423 => 185,  420 => 184,  417 => 183,  414 => 182,  410 => 181,  405 => 178,  401 => 176,  387 => 175,  381 => 172,  376 => 170,  373 => 169,  367 => 166,  362 => 164,  359 => 163,  356 => 162,  353 => 161,  350 => 160,  333 => 159,  330 => 158,  328 => 157,  325 => 156,  319 => 153,  316 => 152,  313 => 151,  307 => 148,  302 => 145,  300 => 144,  291 => 137,  288 => 136,  285 => 135,  283 => 134,  272 => 126,  253 => 109,  249 => 107,  235 => 106,  228 => 102,  221 => 101,  214 => 97,  207 => 96,  204 => 95,  201 => 94,  198 => 93,  181 => 92,  177 => 90,  175 => 89,  170 => 86,  164 => 83,  161 => 82,  158 => 81,  152 => 78,  149 => 77,  147 => 76,  136 => 67,  130 => 64,  127 => 63,  125 => 62,  120 => 59,  117 => 58,  114 => 57,  107 => 56,  100 => 231,  87 => 220,  79 => 215,  76 => 214,  73 => 212,  71 => 56,  66 => 55,  54 => 54,  52 => 51,  51 => 50,  50 => 49,  49 => 47,  46 => 5,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/motaded_theme/templates/paragraphs/paragraph--banner.html.twig", "/var/www/html/web/themes/custom/motaded_theme/templates/paragraphs/paragraph--banner.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 47, "block" => 54, "if" => 214, "for" => 92, "include" => 135];
        static $filters = ["clean_class" => 49, "escape" => 55, "raw" => 187];
        static $functions = ["attach_library" => 215, "drupal_entity" => 64, "file_url" => 197];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'block', 'if', 'for', 'include'],
                ['clean_class', 'escape', 'raw'],
                ['attach_library', 'drupal_entity', 'file_url'],
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
