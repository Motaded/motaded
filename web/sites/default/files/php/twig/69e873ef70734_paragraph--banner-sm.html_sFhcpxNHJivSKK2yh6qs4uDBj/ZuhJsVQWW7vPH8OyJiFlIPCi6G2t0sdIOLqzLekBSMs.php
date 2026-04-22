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

/* themes/custom/motaded_theme/templates/paragraphs/paragraph--banner-sm.html.twig */
class __TwigTemplate_f4db6fc0a9779212c98ccf0ee62182ed extends Template
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
($context["paragraph"] ?? null), "isPublished", [], "method", false, false, true, 51)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("paragraph--unpublished") : ("")), "max-w-[1320px] mx-auto px-4 py-12 space-y-12"];
        // line 55
        $context["no_image"] = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (($_v0 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_show_quick_links", [], "any", false, false, true, 55)) && is_array($_v0) || $_v0 instanceof ArrayAccess && in_array($_v0::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v0["#items"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_show_quick_links", [], "any", false, false, true, 55), "#items", [], "array", false, false, true, 55)), 0, [], "any", false, false, true, 55), "value", [], "any", false, false, true, 55);
        // line 56
        $context["gradientColor"] = (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_bg_color", [], "any", false, true, true, 56), "#items", [], "array", false, true, true, 56), 0, [], "any", false, true, true, 56), "value", [], "any", true, true, true, 56) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (($_v1 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_bg_color", [], "any", false, false, true, 56)) && is_array($_v1) || $_v1 instanceof ArrayAccess && in_array($_v1::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v1["#items"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_bg_color", [], "any", false, false, true, 56), "#items", [], "array", false, false, true, 56)), 0, [], "any", false, false, true, 56), "value", [], "any", false, false, true, 56)))) ? (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (($_v2 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_bg_color", [], "any", false, false, true, 56)) && is_array($_v2) || $_v2 instanceof ArrayAccess && in_array($_v2::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v2["#items"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_bg_color", [], "any", false, false, true, 56), "#items", [], "array", false, false, true, 56)), 0, [], "any", false, false, true, 56), "value", [], "any", false, false, true, 56)) : ("amber"));
        // line 57
        yield from $this->unwrap()->yieldBlock('paragraph', $context, $blocks);
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["paragraph", "view_mode", "content", "attributes"]);        yield from [];
    }

    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_paragraph(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 58
        yield "  ";
        if ((($context["no_image"] ?? null) == 1)) {
            // line 59
            yield "    <section class=\"mx-auto mt-20 max-w-4xl px-4 text-center mb-16\">
      <div class=\"rounded-3xl bg-primary-50 px-10 py-12 shadow-inner\">
        <h2 class=\"text-3xl font-semibold text-primary-900\">
          ";
            // line 62
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_title", [], "any", false, false, true, 62), 0, [], "any", false, false, true, 62), "html", null, true);
            yield "
        </h2>
        <p class=\"mt-4 text-base text-primary-800\">
          ";
            // line 65
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::striptags((($_v3 = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 65), 0, [], "any", false, false, true, 65)) && is_array($_v3) || $_v3 instanceof ArrayAccess && in_array($_v3::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v3["#text"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 65), 0, [], "any", false, false, true, 65), "#text", [], "array", false, false, true, 65))), "html", null, true);
            yield "
        </p>
        <a
          class=\"mt-8 inline-flex items-center justify-center rounded-full bg-primary-600 px-8 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-primary-700\"
          href=\"";
            // line 69
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, (($_v4 = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_link", [], "any", false, false, true, 69), 0, [], "any", false, false, true, 69)) && is_array($_v4) || $_v4 instanceof ArrayAccess && in_array($_v4::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v4["#url"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_link", [], "any", false, false, true, 69), 0, [], "any", false, false, true, 69), "#url", [], "array", false, false, true, 69)), "html", null, true);
            yield "\"
          target=\"_blank\"
          rel=\"noopener\"
        >
          ";
            // line 73
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, (($_v5 = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_link", [], "any", false, false, true, 73), 0, [], "any", false, false, true, 73)) && is_array($_v5) || $_v5 instanceof ArrayAccess && in_array($_v5::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v5["#title"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_link", [], "any", false, false, true, 73), 0, [], "any", false, false, true, 73), "#title", [], "array", false, false, true, 73)), "html", null, true);
            yield "
        </a>
      </div>
    </section>
  ";
        } else {
            // line 78
            yield "    <section";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 78), "html", null, true);
            yield ">
      ";
            // line 79
            yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
            // line 133
            yield "    </section>
  ";
        }
        yield from [];
    }

    // line 79
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 80
        yield "        <!-- Banner -->
        ";
        // line 81
        if ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (($_v6 = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_style", [], "any", false, false, true, 81)) && is_array($_v6) || $_v6 instanceof ArrayAccess && in_array($_v6::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v6["#items"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_style", [], "any", false, false, true, 81), "#items", [], "array", false, false, true, 81)), 0, [], "any", false, false, true, 81), "value", [], "any", false, false, true, 81) == "left")) {
            // line 82
            yield "          <div class=\"relative overflow-hidden rounded-2xl bg-gradient-to-r from-";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["gradientColor"] ?? null), "html", null, true);
            yield "-50 to-white border border-";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["gradientColor"] ?? null), "html", null, true);
            yield "-200 flex flex-col md:flex-row items-center justify-between\">
            ";
            // line 83
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["paragraph"] ?? null), "field_media", [], "any", false, false, true, 83), "entity", [], "any", false, false, true, 83)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 84
                yield "              <div class=\"banner-sm__media mt-6 w-full md:mt-0 md:w-1/3 shrink-0\">
                ";
                // line 85
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Drupal\twig_tweak\TwigTweakExtension::drupalEntity("media", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["paragraph"] ?? null), "field_media", [], "any", false, false, true, 85), "entity", [], "any", false, false, true, 85), "id", [], "any", false, false, true, 85), "banner_sm"), "html", null, true);
                yield "
              </div>
            ";
            }
            // line 88
            yield "            <div class=\"max-w-xl p-8\">
              <h2 class=\"text-2xl md:text-3xl font-bold text-gray-900\">
                ";
            // line 90
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_title", [], "any", false, false, true, 90), 0, [], "any", false, false, true, 90), "html", null, true);
            yield "
              </h2>
              ";
            // line 92
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 92), 0, [], "any", false, false, true, 92))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 93
                yield "                ";
                if (((($_v7 = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 93), 0, [], "any", false, false, true, 93)) && is_array($_v7) || $_v7 instanceof ArrayAccess && in_array($_v7::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v7["#format"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 93), 0, [], "any", false, false, true, 93), "#format", [], "array", false, false, true, 93)) == "plain_text")) {
                    // line 94
                    yield "                  <p class=\"mt-3 text-gray-700\">
                    ";
                    // line 95
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::striptags((($_v8 = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 95), 0, [], "any", false, false, true, 95)) && is_array($_v8) || $_v8 instanceof ArrayAccess && in_array($_v8::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v8["#text"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 95), 0, [], "any", false, false, true, 95), "#text", [], "array", false, false, true, 95))), "html", null, true);
                    yield "
                  </p>
                ";
                } else {
                    // line 98
                    yield "                  <div class=\"mt-3\">";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 98), 0, [], "any", false, false, true, 98), "html", null, true);
                    yield "</div>
                ";
                }
                // line 100
                yield "              ";
            }
            // line 101
            yield "              <div class=\"mt-5\">
                <a href=\"";
            // line 102
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, (($_v9 = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_link", [], "any", false, false, true, 102), 0, [], "any", false, false, true, 102)) && is_array($_v9) || $_v9 instanceof ArrayAccess && in_array($_v9::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v9["#url"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_link", [], "any", false, false, true, 102), 0, [], "any", false, false, true, 102), "#url", [], "array", false, false, true, 102)), "html", null, true);
            yield "\" class=\"inline-flex items-center gap-2 px-5 py-2 rounded-md bg-primary-600 text-white text-sm font-medium hover:bg-primary-700\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, (($_v10 = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_link", [], "any", false, false, true, 102), 0, [], "any", false, false, true, 102)) && is_array($_v10) || $_v10 instanceof ArrayAccess && in_array($_v10::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v10["#title"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_link", [], "any", false, false, true, 102), 0, [], "any", false, false, true, 102), "#title", [], "array", false, false, true, 102)), "html", null, true);
            yield "</a>
              </div>
            </div>
          </div>
        ";
        } else {
            // line 107
            yield "          <div class=\"relative overflow-hidden rounded-2xl bg-gradient-to-r from-";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["gradientColor"] ?? null), "html", null, true);
            yield "-50 to-white border border-";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["gradientColor"] ?? null), "html", null, true);
            yield "-200 flex flex-col md:flex-row items-center justify-between\">
            <div class=\"max-w-xl p-8\">
              <h2 class=\"text-2xl md:text-3xl font-bold text-gray-900\">
                ";
            // line 110
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_title", [], "any", false, false, true, 110), 0, [], "any", false, false, true, 110), "html", null, true);
            yield "
              </h2>
              ";
            // line 112
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 112), 0, [], "any", false, false, true, 112))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 113
                yield "                ";
                if (((($_v11 = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 113), 0, [], "any", false, false, true, 113)) && is_array($_v11) || $_v11 instanceof ArrayAccess && in_array($_v11::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v11["#format"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 113), 0, [], "any", false, false, true, 113), "#format", [], "array", false, false, true, 113)) == "plain_text")) {
                    // line 114
                    yield "                  <p class=\"mt-3 text-gray-700\">
                    ";
                    // line 115
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::striptags((($_v12 = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 115), 0, [], "any", false, false, true, 115)) && is_array($_v12) || $_v12 instanceof ArrayAccess && in_array($_v12::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v12["#text"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 115), 0, [], "any", false, false, true, 115), "#text", [], "array", false, false, true, 115))), "html", null, true);
                    yield "
                  </p>
                ";
                } else {
                    // line 118
                    yield "                  <div class=\"mt-3\">";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 118), 0, [], "any", false, false, true, 118), "html", null, true);
                    yield "</div>
                ";
                }
                // line 120
                yield "              ";
            }
            // line 121
            yield "              <div class=\"mt-5\">
                <a href=\"";
            // line 122
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, (($_v13 = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_link", [], "any", false, false, true, 122), 0, [], "any", false, false, true, 122)) && is_array($_v13) || $_v13 instanceof ArrayAccess && in_array($_v13::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v13["#url"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_link", [], "any", false, false, true, 122), 0, [], "any", false, false, true, 122), "#url", [], "array", false, false, true, 122)), "html", null, true);
            yield "\" class=\"inline-flex items-center gap-2 px-5 py-2 rounded-md bg-primary-600 text-white text-sm font-medium hover:bg-primary-700\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, (($_v14 = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_link", [], "any", false, false, true, 122), 0, [], "any", false, false, true, 122)) && is_array($_v14) || $_v14 instanceof ArrayAccess && in_array($_v14::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v14["#title"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_link", [], "any", false, false, true, 122), 0, [], "any", false, false, true, 122), "#title", [], "array", false, false, true, 122)), "html", null, true);
            yield "</a>
              </div>
            </div>
            ";
            // line 125
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["paragraph"] ?? null), "field_media", [], "any", false, false, true, 125), "entity", [], "any", false, false, true, 125)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 126
                yield "              <div class=\"banner-sm__media mt-6 w-full md:mt-0 md:w-1/3 shrink-0\">
                ";
                // line 127
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Drupal\twig_tweak\TwigTweakExtension::drupalEntity("media", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["paragraph"] ?? null), "field_media", [], "any", false, false, true, 127), "entity", [], "any", false, false, true, 127), "id", [], "any", false, false, true, 127), "banner_sm"), "html", null, true);
                yield "
              </div>
            ";
            }
            // line 130
            yield "          </div>
        ";
        }
        // line 132
        yield "      ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/motaded_theme/templates/paragraphs/paragraph--banner-sm.html.twig";
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
        return array (  258 => 132,  254 => 130,  248 => 127,  245 => 126,  243 => 125,  235 => 122,  232 => 121,  229 => 120,  223 => 118,  217 => 115,  214 => 114,  211 => 113,  209 => 112,  204 => 110,  195 => 107,  185 => 102,  182 => 101,  179 => 100,  173 => 98,  167 => 95,  164 => 94,  161 => 93,  159 => 92,  154 => 90,  150 => 88,  144 => 85,  141 => 84,  139 => 83,  132 => 82,  130 => 81,  127 => 80,  120 => 79,  113 => 133,  111 => 79,  106 => 78,  98 => 73,  91 => 69,  84 => 65,  78 => 62,  73 => 59,  70 => 58,  58 => 57,  56 => 56,  54 => 55,  52 => 51,  51 => 50,  50 => 49,  49 => 47,  46 => 5,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/motaded_theme/templates/paragraphs/paragraph--banner-sm.html.twig", "/var/www/html/web/themes/custom/motaded_theme/templates/paragraphs/paragraph--banner-sm.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 47, "block" => 57, "if" => 58];
        static $filters = ["clean_class" => 49, "escape" => 62, "striptags" => 65];
        static $functions = ["drupal_entity" => 85];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'block', 'if'],
                ['clean_class', 'escape', 'striptags'],
                ['drupal_entity'],
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
