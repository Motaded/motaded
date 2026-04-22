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

/* themes/custom/motaded_theme/templates/content/node--news--full.html.twig */
class __TwigTemplate_9f8873a151266a47a5dd168139568720 extends Template
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
        // line 13
        $context["classes"] = ["node", ("node--type-" . \Drupal\Component\Utility\Html::getClass(CoreExtension::getAttribute($this->env, $this->source,         // line 15
($context["node"] ?? null), "bundle", [], "any", false, false, true, 15))), (((($tmp = CoreExtension::getAttribute($this->env, $this->source,         // line 16
($context["node"] ?? null), "isPromoted", [], "method", false, false, true, 16)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("node--promoted") : ("")), (((($tmp = CoreExtension::getAttribute($this->env, $this->source,         // line 17
($context["node"] ?? null), "isSticky", [], "method", false, false, true, 17)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("node--sticky") : ("")), (((($tmp =  !CoreExtension::getAttribute($this->env, $this->source,         // line 18
($context["node"] ?? null), "isPublished", [], "method", false, false, true, 18)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("node--unpublished") : ("")), (((($tmp =         // line 19
($context["view_mode"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (("node--view-mode-" . \Drupal\Component\Utility\Html::getClass(($context["view_mode"] ?? null)))) : ("")), "node--news-full", "mb-12"];
        // line 24
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("motaded_theme/node"), "html", null, true);
        yield "
<article";
        // line 25
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 25), "html", null, true);
        yield ">
  ";
        // line 26
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title_prefix"] ?? null), "html", null, true);
        yield "
  ";
        // line 27
        if ((($context["label"] ?? null) &&  !($context["page"] ?? null))) {
            // line 28
            yield "    <h2";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["title_attributes"] ?? null), "addClass", ["text-xl", "font-bold", "text-gray-900"], "method", false, false, true, 28), "html", null, true);
            yield ">
      <a href=\"";
            // line 29
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["url"] ?? null), "html", null, true);
            yield "\" rel=\"bookmark\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["label"] ?? null), "html", null, true);
            yield "</a>
    </h2>
  ";
        }
        // line 32
        yield "  ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title_suffix"] ?? null), "html", null, true);
        yield "

  ";
        // line 34
        if ((($tmp = ($context["display_submitted"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 35
            yield "    <footer class=\"node__meta visually-hidden\">
      ";
            // line 36
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["author_picture"] ?? null), "html", null, true);
            yield "
      <div";
            // line 37
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["author_attributes"] ?? null), "addClass", ["node__submitted"], "method", false, false, true, 37), "html", null, true);
            yield ">
        ";
            // line 38
            yield t("Submitted by @author_name on @date", ["@author_name" => $this->env->getExtension(\Drupal\Core\Template\TwigExtension::class)->renderVar(($context["author_name"] ?? null)), "@date" => $this->env->getExtension(\Drupal\Core\Template\TwigExtension::class)->renderVar(($context["date"] ?? null)), ]);
            // line 39
            yield "        ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["metadata"] ?? null), "html", null, true);
            yield "
      </div>
    </footer>
  ";
        }
        // line 43
        yield "
  <div class=\"news-hero\" role=\"region\" aria-label=\"";
        // line 44
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("News header"));
        yield "\">
    <div class=\"news-hero__inner mx-auto w-full max-w-[1320px] px-4 py-6 sm:px-6 sm:py-8 lg:py-10\">
      <div class=\"news-hero__content flex w-full min-w-0 flex-col gap-6\">
        <div class=\"news-hero__lead flex w-full min-w-0 flex-col gap-5";
        // line 47
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($tmp = ((array_key_exists("news_hero_media", $context)) ? (Twig\Extension\CoreExtension::default(($context["news_hero_media"] ?? null), [])) : ([]))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (" news-hero__lead--has-media") : ("")));
        yield "\">
          <div class=\"news-hero__lead-text min-w-0\">
            ";
        // line 49
        if ((($context["page"] ?? null) && ($context["label"] ?? null))) {
            // line 50
            yield "              <h1";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["title_attributes"] ?? null), "addClass", ["news-hero__title", "text-3xl", "font-extrabold", "text-gray-900", "lg:text-4xl", "leading-tight", "m-0"], "method", false, false, true, 50), "html", null, true);
            yield ">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["label"] ?? null), "html", null, true);
            yield "</h1>
            ";
        }
        // line 52
        yield "
            ";
        // line 53
        if ((($tmp = ($context["summary"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 54
            yield "              <div class=\"news-hero__intro prose prose-neutral max-w-none text-base leading-relaxed text-gray-700 mt-4\">
                ";
            // line 55
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["summary"] ?? null), "html", null, true);
            yield "
              </div>
            ";
        }
        // line 58
        yield "          </div>

          ";
        // line 60
        if ((($tmp = ((array_key_exists("news_hero_media", $context)) ? (Twig\Extension\CoreExtension::default(($context["news_hero_media"] ?? null), [])) : ([]))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 61
            yield "            <div class=\"news-hero__lead-media w-full shrink-0 mx-auto lg:mx-0\">
              <div class=\"news-hero-media overflow-hidden rounded-xl border border-gray-200 bg-gray-50 shadow-sm\">
                ";
            // line 63
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["news_hero_media"] ?? null), "html", null, true);
            yield "
              </div>
            </div>
          ";
        }
        // line 67
        yield "        </div>

        <div class=\"news-hero__trail w-full min-w-0\">
          <div class=\"news-hero__meta flex flex-wrap items-center gap-2 text-xs sm:text-sm\">
            ";
        // line 71
        if ((($tmp = ($context["news_updated_formatted"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 72
            yield "              <span class=\"inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-800\">
                <time datetime=\"";
            // line 73
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["news_changed_iso"] ?? null), "html", null, true);
            yield "\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["news_updated_formatted"] ?? null), "html", null, true);
            yield "</time>
              </span>
            ";
        }
        // line 76
        yield "            <span class=\"inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 font-medium text-amber-950\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("News"));
        yield "</span>
            ";
        // line 77
        if ((CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_taxonomy", [], "any", true, true, true, 77) &&  !Twig\Extension\CoreExtension::testEmpty(Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_taxonomy", [], "any", false, false, true, 77))))))) {
            // line 78
            yield "              <span class=\"news-hero__meta-chips inline-flex flex-wrap items-center gap-2\">
                ";
            // line 79
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_taxonomy", [], "any", false, false, true, 79), "html", null, true);
            yield "
              </span>
            ";
        }
        // line 82
        yield "            ";
        if ((CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_tags", [], "any", true, true, true, 82) &&  !Twig\Extension\CoreExtension::testEmpty(Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_tags", [], "any", false, false, true, 82))))))) {
            // line 83
            yield "              <span class=\"news-hero__meta-chips inline-flex flex-wrap items-center gap-2\">
                ";
            // line 84
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_tags", [], "any", false, false, true, 84), "html", null, true);
            yield "
              </span>
            ";
        }
        // line 87
        yield "          </div>

          ";
        // line 89
        $context["show_glance"] = ((((CoreExtension::getAttribute($this->env, $this->source,         // line 90
($context["content"] ?? null), "field_effective_date", [], "any", true, true, true, 90) && Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_effective_date", [], "any", false, false, true, 90))))) || (CoreExtension::getAttribute($this->env, $this->source,         // line 91
($context["content"] ?? null), "field_applies_to", [], "any", true, true, true, 91) && Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_applies_to", [], "any", false, false, true, 91)))))) || (CoreExtension::getAttribute($this->env, $this->source,         // line 92
($context["content"] ?? null), "field_requirement", [], "any", true, true, true, 92) && Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_requirement", [], "any", false, false, true, 92)))))) || (CoreExtension::getAttribute($this->env, $this->source,         // line 93
($context["content"] ?? null), "field_source_scope", [], "any", true, true, true, 93) && Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_source_scope", [], "any", false, false, true, 93))))));
        // line 95
        yield "
          ";
        // line 96
        if ((($tmp = ($context["show_glance"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 97
            yield "            <section class=\"news-facts mt-5\" aria-label=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("At a glance"));
            yield "\">
              <p class=\"news-facts__heading\">";
            // line 98
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("At a glance"));
            yield "</p>
              ";
            // line 99
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_effective_date", [], "any", true, true, true, 99) &&  !Twig\Extension\CoreExtension::testEmpty(Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_effective_date", [], "any", false, false, true, 99))))))) {
                // line 100
                yield "                <div class=\"news-facts__row\">
                  <span class=\"news-facts__label\">";
                // line 101
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Effective date"));
                yield "</span>
                  <div class=\"news-facts__value\">";
                // line 102
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_effective_date", [], "any", false, false, true, 102), "html", null, true);
                yield "</div>
                </div>
              ";
            }
            // line 105
            yield "              ";
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_applies_to", [], "any", true, true, true, 105) &&  !Twig\Extension\CoreExtension::testEmpty(Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_applies_to", [], "any", false, false, true, 105))))))) {
                // line 106
                yield "                <div class=\"news-facts__row\">
                  <span class=\"news-facts__label\">";
                // line 107
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Applies to"));
                yield "</span>
                  <div class=\"news-facts__value\">";
                // line 108
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_applies_to", [], "any", false, false, true, 108), "html", null, true);
                yield "</div>
                </div>
              ";
            }
            // line 111
            yield "              ";
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_requirement", [], "any", true, true, true, 111) &&  !Twig\Extension\CoreExtension::testEmpty(Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_requirement", [], "any", false, false, true, 111))))))) {
                // line 112
                yield "                <div class=\"news-facts__row\">
                  <span class=\"news-facts__label\">";
                // line 113
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Requirement"));
                yield "</span>
                  <div class=\"news-facts__value\">";
                // line 114
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_requirement", [], "any", false, false, true, 114), "html", null, true);
                yield "</div>
                </div>
              ";
            }
            // line 117
            yield "              ";
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_source_scope", [], "any", true, true, true, 117) &&  !Twig\Extension\CoreExtension::testEmpty(Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_source_scope", [], "any", false, false, true, 117))))))) {
                // line 118
                yield "                <div class=\"news-facts__row\">
                  <span class=\"news-facts__label\">";
                // line 119
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Source / scope"));
                yield "</span>
                  <div class=\"news-facts__value\">";
                // line 120
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_source_scope", [], "any", false, false, true, 120), "html", null, true);
                yield "</div>
                </div>
              ";
            }
            // line 123
            yield "            </section>
          ";
        }
        // line 125
        yield "        </div>
      </div>
    </div>
  </div>

  <div
    ";
        // line 131
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["content_attributes"] ?? null), "addClass", ["news-main", "news-body", "node__content", "prose", "prose-neutral", "max-w-none"], "method", false, false, true, 131), "html", null, true);
        yield "
    id=\"news-root\"
  >
    ";
        // line 134
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->withoutFilter(($context["content"] ?? null), "addtoany", "field_media", "field_effective_date", "field_applies_to", "field_requirement", "field_source_scope", "field_meta", "field_tags", "field_taxonomy", "field_comments"), "html", null, true);
        // line 145
        yield "
  </div>

  ";
        // line 149
        yield "  ";
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "addtoany", [], "any", false, false, true, 149)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 150
            yield "    <aside class=\"news-after-body mx-auto mt-10 w-full max-w-[1320px] px-4 sm:px-6\" aria-label=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Share this article"));
            yield "\">
      <div class=\"news-after-body__card\">
        <p class=\"news-after-body__title\">";
            // line 152
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Found this helpful? Share it so colleagues and contacts can stay up to date."));
            yield "</p>
        <div class=\"news-after-body__inner\">
          ";
            // line 154
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "addtoany", [], "any", false, false, true, 154), "html", null, true);
            yield "
        </div>
      </div>
    </aside>
  ";
        }
        // line 159
        yield "</article>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["node", "view_mode", "attributes", "title_prefix", "label", "page", "title_attributes", "url", "title_suffix", "display_submitted", "author_picture", "author_attributes", "author_name", "date", "metadata", "news_hero_media", "summary", "news_updated_formatted", "news_changed_iso", "content", "content_attributes"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/motaded_theme/templates/content/node--news--full.html.twig";
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
        return array (  346 => 159,  338 => 154,  333 => 152,  327 => 150,  324 => 149,  319 => 145,  317 => 134,  311 => 131,  303 => 125,  299 => 123,  293 => 120,  289 => 119,  286 => 118,  283 => 117,  277 => 114,  273 => 113,  270 => 112,  267 => 111,  261 => 108,  257 => 107,  254 => 106,  251 => 105,  245 => 102,  241 => 101,  238 => 100,  236 => 99,  232 => 98,  227 => 97,  225 => 96,  222 => 95,  220 => 93,  219 => 92,  218 => 91,  217 => 90,  216 => 89,  212 => 87,  206 => 84,  203 => 83,  200 => 82,  194 => 79,  191 => 78,  189 => 77,  184 => 76,  176 => 73,  173 => 72,  171 => 71,  165 => 67,  158 => 63,  154 => 61,  152 => 60,  148 => 58,  142 => 55,  139 => 54,  137 => 53,  134 => 52,  126 => 50,  124 => 49,  119 => 47,  113 => 44,  110 => 43,  102 => 39,  100 => 38,  96 => 37,  92 => 36,  89 => 35,  87 => 34,  81 => 32,  73 => 29,  68 => 28,  66 => 27,  62 => 26,  58 => 25,  54 => 24,  52 => 19,  51 => 18,  50 => 17,  49 => 16,  48 => 15,  47 => 13,  44 => 5,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/motaded_theme/templates/content/node--news--full.html.twig", "/var/www/html/web/themes/custom/motaded_theme/templates/content/node--news--full.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 13, "if" => 27, "trans" => 38];
        static $filters = ["clean_class" => 15, "escape" => 24, "t" => 44, "default" => 47, "trim" => 77, "striptags" => 77, "render" => 77, "without" => 134];
        static $functions = ["attach_library" => 24];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if', 'trans'],
                ['clean_class', 'escape', 't', 'default', 'trim', 'striptags', 'render', 'without'],
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
