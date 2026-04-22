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

/* themes/custom/motaded_theme/templates/paragraphs/paragraph--featured-contents.html.twig */
class __TwigTemplate_c4719fc1c17ce4ea03c07ac94159b53c extends Template
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
        // line 46
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("motaded_theme/featured-contents"), "html", null, true);
        yield "
";
        // line 47
        $context["fc_count"] = Twig\Extension\CoreExtension::length($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source, ($context["paragraph"] ?? null), "field_contents", [], "any", false, false, true, 47));
        // line 48
        $context["fc_last"] = (((($context["fc_count"] ?? null) > 0)) ? ((($context["fc_count"] ?? null) - 1)) : (0));
        // line 50
        $context["classes"] = ["paragraph", ("paragraph--type--" . \Drupal\Component\Utility\Html::getClass(CoreExtension::getAttribute($this->env, $this->source,         // line 52
($context["paragraph"] ?? null), "bundle", [], "any", false, false, true, 52))), (((($tmp =         // line 53
($context["view_mode"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (("paragraph--view-mode--" . \Drupal\Component\Utility\Html::getClass(($context["view_mode"] ?? null)))) : ("")), (((($tmp =  !CoreExtension::getAttribute($this->env, $this->source,         // line 54
($context["paragraph"] ?? null), "isPublished", [], "method", false, false, true, 54)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("paragraph--unpublished") : ("")), "pb-12 mt-[140px]"];
        // line 58
        yield from $this->unwrap()->yieldBlock('paragraph', $context, $blocks);
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["paragraph", "view_mode", "attributes", "content", "featured_card_images"]);        yield from [];
    }

    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_paragraph(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 59
        yield "  <div";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 59), "html", null, true);
        yield ">
    ";
        // line 60
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 170
        yield "  </div>
";
        yield from [];
    }

    // line 60
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 61
        yield "      <!-- Header -->
      <div class=\"max-w-[1320px] mx-auto px-4 mb-8\">
        <div class=\"flex flex-wrap md:flex-nowrap justify-between items-start gap-3\">
          <div class=\"text-base md:w-2/3\">
            ";
        // line 65
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 65), 0, [], "any", false, false, true, 65), "html", null, true);
        yield "
          </div>
          ";
        // line 67
        if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_link", [], "any", false, false, true, 67))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 68
            yield "            <a href=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, (($_v0 = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_link", [], "any", false, false, true, 68), 0, [], "any", false, false, true, 68)) && is_array($_v0) || $_v0 instanceof ArrayAccess && in_array($_v0::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v0["#url"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_link", [], "any", false, false, true, 68), 0, [], "any", false, false, true, 68), "#url", [], "array", false, false, true, 68)), "html", null, true);
            yield "\" class=\"ml-auto md:ml-0 inline-flex items-center justify-center rounded-md border px-4 py-2 text-sm font-medium hover:bg-gray-50 border-gray-300\">
              ";
            // line 69
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, (($_v1 = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_link", [], "any", false, false, true, 69), 0, [], "any", false, false, true, 69)) && is_array($_v1) || $_v1 instanceof ArrayAccess && in_array($_v1::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v1["#title"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_link", [], "any", false, false, true, 69), 0, [], "any", false, false, true, 69), "#title", [], "array", false, false, true, 69)), "html", null, true);
            yield "
            </a>
          ";
        }
        // line 72
        yield "        </div>
      </div>

      <!-- Content -->
      <div
        class=\"max-w-[1320px] mx-auto px-4\" x-data=\"articlesCarousel\" data-articles-length=\"";
        // line 77
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["fc_count"] ?? null), "html", null, true);
        yield "\">
        <!-- Desktop / Tablet grid -->
        <div class=\"hidden md:grid grid-cols-1 md:grid-cols-3 gap-5\">
          ";
        // line 80
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["paragraph"] ?? null), "field_contents", [], "any", false, false, true, 80));
        foreach ($context['_seq'] as $context["key"] => $context["item"]) {
            // line 81
            yield "            ";
            $context["article"] = (($_v2 = (($_v3 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_contents", [], "any", false, false, true, 81)) && is_array($_v3) || $_v3 instanceof ArrayAccess && in_array($_v3::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v3[$context["key"]] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_contents", [], "any", false, false, true, 81), $context["key"], [], "array", false, false, true, 81))) && is_array($_v2) || $_v2 instanceof ArrayAccess && in_array($_v2::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v2["#node"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, (($_v4 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_contents", [], "any", false, false, true, 81)) && is_array($_v4) || $_v4 instanceof ArrayAccess && in_array($_v4::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v4[$context["key"]] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_contents", [], "any", false, false, true, 81), $context["key"], [], "array", false, false, true, 81)), "#node", [], "array", false, false, true, 81));
            // line 82
            yield "            ";
            $context["card_img"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["featured_card_images"] ?? null), CoreExtension::getAttribute($this->env, $this->source, ($context["article"] ?? null), "id", [], "method", false, false, true, 82), [], "array", true, true, true, 82) &&  !(null === (($_v5 = ($context["featured_card_images"] ?? null)) && is_array($_v5) || $_v5 instanceof ArrayAccess && in_array($_v5::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v5[CoreExtension::getAttribute($this->env, $this->source, ($context["article"] ?? null), "id", [], "method", false, false, true, 82)] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["featured_card_images"] ?? null), CoreExtension::getAttribute($this->env, $this->source, ($context["article"] ?? null), "id", [], "method", false, false, true, 82), [], "array", false, false, true, 82))))) ? ((($_v6 = ($context["featured_card_images"] ?? null)) && is_array($_v6) || $_v6 instanceof ArrayAccess && in_array($_v6::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v6[CoreExtension::getAttribute($this->env, $this->source, ($context["article"] ?? null), "id", [], "method", false, false, true, 82)] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["featured_card_images"] ?? null), CoreExtension::getAttribute($this->env, $this->source, ($context["article"] ?? null), "id", [], "method", false, false, true, 82), [], "array", false, false, true, 82))) : ([]));
            // line 83
            yield "            <a href=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->getPath("entity.node.canonical", ["node" => CoreExtension::getAttribute($this->env, $this->source, ($context["article"] ?? null), "id", [], "method", false, false, true, 83)]), "html", null, true);
            yield "\" class=\"group\">
              <article class=\"bg-white border border-gray-200 rounded-lg mx-2 mt-2 mb-4 p-4 flex flex-col h-full transition hover:shadow-md\">
                <div class=\"mb-4 rounded-md overflow-hidden\">
                  ";
            // line 86
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "src", [], "any", false, false, true, 86)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 87
                yield "                    ";
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "srcset", [], "any", false, false, true, 87)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 88
                    yield "                      <img loading=\"lazy\" decoding=\"async\" src=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "src", [], "any", false, false, true, 88), "html", null, true);
                    yield "\" srcset=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "srcset", [], "any", false, false, true, 88), "html", null, true);
                    yield "\" sizes=\"(max-width: 767px) 100vw, (min-width: 768px) 33vw\" width=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "width", [], "any", false, false, true, 88), "html", null, true);
                    yield "\" height=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "height", [], "any", false, false, true, 88), "html", null, true);
                    yield "\" alt=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "alt", [], "any", false, false, true, 88), "html", null, true);
                    yield "\" class=\"w-full h-44 object-cover group-hover:opacity-95 transition\"/>
                    ";
                } else {
                    // line 90
                    yield "                      <img loading=\"lazy\" decoding=\"async\" src=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "src", [], "any", false, false, true, 90), "html", null, true);
                    yield "\" width=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "width", [], "any", false, false, true, 90), "html", null, true);
                    yield "\" height=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "height", [], "any", false, false, true, 90), "html", null, true);
                    yield "\" alt=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "alt", [], "any", false, false, true, 90), "html", null, true);
                    yield "\" class=\"w-full h-44 object-cover group-hover:opacity-95 transition\"/>
                    ";
                }
                // line 92
                yield "                  ";
            }
            // line 93
            yield "                </div>
                <h3 class=\"font-bold text-lg mb-2\">";
            // line 94
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["article"] ?? null), "label", [], "any", false, false, true, 94), "html", null, true);
            yield "</h3>
                <div
                  class=\"text-black/80 mb-4\">
                  ";
            // line 98
            yield "                  ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["article"] ?? null), "body", [], "any", false, false, true, 98), "summary", [], "any", false, false, true, 98)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 99
                yield "                    ";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, (Drupal\Component\Utility\Unicode::truncate(Twig\Extension\CoreExtension::striptags(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["article"] ?? null), "body", [], "any", false, false, true, 99), "summary", [], "any", false, false, true, 99)), 120) . "..."), "html", null, true);
                yield "
                  ";
            } else {
                // line 101
                yield "                    ";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Drupal\Component\Utility\Unicode::truncate(Twig\Extension\CoreExtension::striptags(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["article"] ?? null), "body", [], "any", false, false, true, 101), "value", [], "any", false, false, true, 101)), 120), "html", null, true);
                yield "
                  ";
            }
            // line 103
            yield "                </div>
                <span class=\"inline-flex items-center gap-2 text-sm underline\">
                  ";
            // line 105
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Read More"));
            yield "
                  <img src=\"/themes/custom/motaded_theme/img/icon-arrow-down.svg\" alt=\"\" width=\"18\" height=\"18\" class=\"rtl:rotate-90 ltr:-rotate-90 -scale-x-100\"/>
                </span>
              </article>
            </a>
          ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['key'], $context['item'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 111
        yield "        </div>

        <!-- Mobile carousel -->
        <div class=\"md:hidden\">
          <div
            class=\"relative\">
            <!-- Track -->
            <div class=\"overflow-hidden\">
              <div class=\"flex transition-transform duration-500\" :style=\"isRtl ? `transform: translateX(\${current * 100}%);` : `transform: translateX(-\${current * 100}%);`\" @touchstart=\"onTouchStart(\$event)\" @touchmove.prevent=\"onTouchMove(\$event)\" @touchend=\"onTouchEnd()\">
                ";
        // line 120
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["paragraph"] ?? null), "field_contents", [], "any", false, false, true, 120));
        foreach ($context['_seq'] as $context["key"] => $context["item"]) {
            // line 121
            yield "                  ";
            $context["article"] = (($_v7 = (($_v8 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_contents", [], "any", false, false, true, 121)) && is_array($_v8) || $_v8 instanceof ArrayAccess && in_array($_v8::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v8[$context["key"]] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_contents", [], "any", false, false, true, 121), $context["key"], [], "array", false, false, true, 121))) && is_array($_v7) || $_v7 instanceof ArrayAccess && in_array($_v7::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v7["#node"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, (($_v9 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_contents", [], "any", false, false, true, 121)) && is_array($_v9) || $_v9 instanceof ArrayAccess && in_array($_v9::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v9[$context["key"]] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_contents", [], "any", false, false, true, 121), $context["key"], [], "array", false, false, true, 121)), "#node", [], "array", false, false, true, 121));
            // line 122
            yield "                  ";
            $context["card_img"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["featured_card_images"] ?? null), CoreExtension::getAttribute($this->env, $this->source, ($context["article"] ?? null), "id", [], "method", false, false, true, 122), [], "array", true, true, true, 122) &&  !(null === (($_v10 = ($context["featured_card_images"] ?? null)) && is_array($_v10) || $_v10 instanceof ArrayAccess && in_array($_v10::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v10[CoreExtension::getAttribute($this->env, $this->source, ($context["article"] ?? null), "id", [], "method", false, false, true, 122)] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["featured_card_images"] ?? null), CoreExtension::getAttribute($this->env, $this->source, ($context["article"] ?? null), "id", [], "method", false, false, true, 122), [], "array", false, false, true, 122))))) ? ((($_v11 = ($context["featured_card_images"] ?? null)) && is_array($_v11) || $_v11 instanceof ArrayAccess && in_array($_v11::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v11[CoreExtension::getAttribute($this->env, $this->source, ($context["article"] ?? null), "id", [], "method", false, false, true, 122)] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["featured_card_images"] ?? null), CoreExtension::getAttribute($this->env, $this->source, ($context["article"] ?? null), "id", [], "method", false, false, true, 122), [], "array", false, false, true, 122))) : ([]));
            // line 123
            yield "                  <div class=\"min-w-full px-1\">
                    <a href=\"";
            // line 124
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->getPath("entity.node.canonical", ["node" => CoreExtension::getAttribute($this->env, $this->source, ($context["article"] ?? null), "id", [], "method", false, false, true, 124)]), "html", null, true);
            yield "\" class=\"block min-h-72\">
                      <article class=\"bg-white border rounded-xl p-4 h-full hover:border-primary-600 transition\">
                        <div class=\"flex flex-col h-full\">
                          <div class=\"w-full\">
                            ";
            // line 128
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "src", [], "any", false, false, true, 128)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 129
                yield "                              ";
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "srcset", [], "any", false, false, true, 129)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 130
                    yield "                                <img loading=\"lazy\" decoding=\"async\" src=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "src", [], "any", false, false, true, 130), "html", null, true);
                    yield "\" srcset=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "srcset", [], "any", false, false, true, 130), "html", null, true);
                    yield "\" sizes=\"100vw\" width=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "width", [], "any", false, false, true, 130), "html", null, true);
                    yield "\" height=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "height", [], "any", false, false, true, 130), "html", null, true);
                    yield "\" alt=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "alt", [], "any", false, false, true, 130), "html", null, true);
                    yield "\" class=\"rounded-lg w-full h-48 object-cover\"/>
                              ";
                } else {
                    // line 132
                    yield "                                <img loading=\"lazy\" decoding=\"async\" src=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "src", [], "any", false, false, true, 132), "html", null, true);
                    yield "\" width=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "width", [], "any", false, false, true, 132), "html", null, true);
                    yield "\" height=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "height", [], "any", false, false, true, 132), "html", null, true);
                    yield "\" alt=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_img"] ?? null), "alt", [], "any", false, false, true, 132), "html", null, true);
                    yield "\" class=\"rounded-lg w-full h-48 object-cover\"/>
                              ";
                }
                // line 134
                yield "                            ";
            }
            // line 135
            yield "                          </div>
                          <div class=\"mt-5 flex-1\">
                            <p class=\"font-bold text-base leading-[1.6]\">";
            // line 137
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["article"] ?? null), "label", [], "any", false, false, true, 137), "html", null, true);
            yield "</p>
                            <p class=\"text-black/80 mt-2\">";
            // line 138
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Drupal\Component\Utility\Unicode::truncate(Twig\Extension\CoreExtension::striptags(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["article"] ?? null), "body", [], "any", false, false, true, 138), "summary", [], "any", false, false, true, 138)), 150), "html", null, true);
            yield "</p>
                          </div>
                        </div>
                      </article>
                    </a>
                  </div>
                ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['key'], $context['item'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 145
        yield "              </div>
            </div>

            <!-- Controls -->
            <div class=\"mt-3 flex items-center justify-between\">
              <button type=\"button\" class=\"w-9 h-9 grid place-items-center rounded-full border hover:bg-gray-50\" @click=\"prev()\" :disabled=\"current===0\" :class=\"current===0 && 'opacity-40 cursor-not-allowed'\" aria-label=\"Previous\">
                <img src=\"/themes/custom/motaded_theme/img/icon-arrow-down.svg\" class=\"-rotate-90 rtl:rotate-90\" alt=\"\" width=\"18\" height=\"18\"/>
              </button>

              <!-- Dots -->
              <div class=\"flex items-center gap-3\">
                ";
        // line 156
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["paragraph"] ?? null), "field_contents", [], "any", false, false, true, 156));
        foreach ($context['_seq'] as $context["key"] => $context["item"]) {
            // line 157
            yield "                  <button type=\"button\" class=\"size-3 rounded-full transition\" :class=\"current === ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $context["key"], "html", null, true);
            yield " ? 'bg-primary-600' : 'bg-gray-300'\" @click=\"go(";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $context["key"], "html", null, true);
            yield ")\" :aria-label=\"`Go to slide ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["key"] + 1), "html", null, true);
            yield "`\"></button>
                ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['key'], $context['item'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 159
        yield "              </div>

              <button type=\"button\" class=\"w-9 h-9 grid place-items-center rounded-full border hover:bg-gray-50\" @click=\"next()\" :disabled=\"current === ";
        // line 161
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["fc_last"] ?? null), "html", null, true);
        yield "\" :class=\"current === ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["fc_last"] ?? null), "html", null, true);
        yield " && 'opacity-40 cursor-not-allowed'\" aria-label=\"Next\">
                <img src=\"/themes/custom/motaded_theme/img/icon-arrow-down.svg\" class=\"rotate-90 -scale-x-100 rtl:-rotate-90\" alt=\"\" width=\"18\" height=\"18\" class=\"-scale-x-100 rtl:rotate-180\"/>
              </button>
            </div>
          </div>
        </div>
      </div>

    ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/motaded_theme/templates/paragraphs/paragraph--featured-contents.html.twig";
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
        return array (  342 => 161,  338 => 159,  325 => 157,  321 => 156,  308 => 145,  295 => 138,  291 => 137,  287 => 135,  284 => 134,  272 => 132,  258 => 130,  255 => 129,  253 => 128,  246 => 124,  243 => 123,  240 => 122,  237 => 121,  233 => 120,  222 => 111,  210 => 105,  206 => 103,  200 => 101,  194 => 99,  191 => 98,  185 => 94,  182 => 93,  179 => 92,  167 => 90,  153 => 88,  150 => 87,  148 => 86,  141 => 83,  138 => 82,  135 => 81,  131 => 80,  125 => 77,  118 => 72,  112 => 69,  107 => 68,  105 => 67,  100 => 65,  94 => 61,  87 => 60,  81 => 170,  79 => 60,  74 => 59,  62 => 58,  60 => 54,  59 => 53,  58 => 52,  57 => 50,  55 => 48,  53 => 47,  49 => 46,  46 => 5,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/motaded_theme/templates/paragraphs/paragraph--featured-contents.html.twig", "/var/www/html/web/themes/custom/motaded_theme/templates/paragraphs/paragraph--featured-contents.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 47, "block" => 58, "if" => 67, "for" => 80];
        static $filters = ["escape" => 46, "length" => 47, "clean_class" => 52, "truncate" => 99, "striptags" => 99, "t" => 105];
        static $functions = ["attach_library" => 46, "path" => 83];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'block', 'if', 'for'],
                ['escape', 'length', 'clean_class', 'truncate', 'striptags', 't'],
                ['attach_library', 'path'],
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
