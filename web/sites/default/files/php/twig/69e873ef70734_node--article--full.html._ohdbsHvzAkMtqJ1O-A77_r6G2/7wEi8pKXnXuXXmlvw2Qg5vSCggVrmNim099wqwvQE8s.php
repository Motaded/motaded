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

/* themes/custom/motaded_theme/templates/content/node--article--full.html.twig */
class __TwigTemplate_9515ddebd154f8dd39eec0b07e2d3763 extends Template
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
($context["view_mode"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (("node--view-mode-" . \Drupal\Component\Utility\Html::getClass(($context["view_mode"] ?? null)))) : ("")), "node--article-full", "mb-12"];
        // line 24
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("motaded_theme/node"), "html", null, true);
        yield "
";
        // line 25
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("motaded_theme/toc"), "html", null, true);
        yield "
<article";
        // line 26
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 26), "html", null, true);
        yield "
    id=\"toc-root\"
    x-data=\"window.articlePageEN ? window.articlePageEN() : {
      tocOpen: false,
      activeId: null,
      toc: [],
      _booted: false,
      init() {
        if (this._booted) return;
        const boot = () => {
          if (this._booted || !window.articlePageEN) return;
          const real = window.articlePageEN();
          this.tocOpen = real.tocOpen ?? this.tocOpen;
          this.activeId = real.activeId ?? this.activeId;
          this.toc = real.toc ?? this.toc;
          this.scrollTo = typeof real.scrollTo === 'function' ? real.scrollTo.bind(this) : this.scrollTo;
          this._booted = true;
          if (typeof real.init === 'function') real.init.call(this);
        };
        boot();
        setTimeout(boot, 50);
        setTimeout(boot, 250);
        setTimeout(boot, 1000);
      },
      scrollTo() {},
    }\"
    x-init=\"init()\"
    data-inline-cta-eyebrow=\"";
        // line 53
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Next step"));
        yield "\"
    data-inline-cta-title=\"";
        // line 54
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Talk to Motaded experts today"));
        yield "\"
    data-inline-cta-text=\"";
        // line 55
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Book a quick consultation and receive clear, actionable guidance tailored to your case."));
        yield "\"
    @keydown.escape.window=\"tocOpen=false\">
  <div class=\"hidden article-inline-cta-source\" aria-hidden=\"true\">
    ";
        // line 58
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Drupal\twig_tweak\TwigTweakExtension::drupalRegion("article_toc_cta"), "html", null, true);
        yield "
  </div>

  ";
        // line 61
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title_prefix"] ?? null), "html", null, true);
        yield "
  ";
        // line 62
        if ((($context["label"] ?? null) &&  !($context["page"] ?? null))) {
            // line 63
            yield "    <h2";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["title_attributes"] ?? null), "addClass", ["text-xl", "font-bold", "text-gray-900"], "method", false, false, true, 63), "html", null, true);
            yield ">
      <a href=\"";
            // line 64
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["url"] ?? null), "html", null, true);
            yield "\" rel=\"bookmark\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["label"] ?? null), "html", null, true);
            yield "</a>
    </h2>
  ";
        }
        // line 67
        yield "  ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title_suffix"] ?? null), "html", null, true);
        yield "

  ";
        // line 69
        if ((($tmp = ($context["display_submitted"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 70
            yield "    <footer class=\"node__meta visually-hidden\">
      ";
            // line 71
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["author_picture"] ?? null), "html", null, true);
            yield "
      <div";
            // line 72
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["author_attributes"] ?? null), "addClass", ["node__submitted"], "method", false, false, true, 72), "html", null, true);
            yield ">
        ";
            // line 73
            yield t("Submitted by @author_name on @date", ["@author_name" => $this->env->getExtension(\Drupal\Core\Template\TwigExtension::class)->renderVar(($context["author_name"] ?? null)), "@date" => $this->env->getExtension(\Drupal\Core\Template\TwigExtension::class)->renderVar(($context["date"] ?? null)), ]);
            // line 74
            yield "        ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["metadata"] ?? null), "html", null, true);
            yield "
      </div>
    </footer>
  ";
        }
        // line 78
        yield "
  <div class=\"article-page-grid\">
    ";
        // line 81
        yield "    <div class=\"article-page-grid__accent\" role=\"presentation\" aria-hidden=\"true\"></div>
    <div class=\"article-page-grid__lead\">
      ";
        // line 83
        if ((($context["page"] ?? null) && ($context["label"] ?? null))) {
            // line 84
            yield "        <h1";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["title_attributes"] ?? null), "addClass", ["article-page-grid__title", "text-3xl", "font-extrabold", "text-gray-900", "lg:text-4xl", "leading-tight", "m-0"], "method", false, false, true, 84), "html", null, true);
            yield ">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["label"] ?? null), "html", null, true);
            yield "</h1>
      ";
        }
        // line 86
        yield "
      ";
        // line 87
        if ((($tmp = ($context["summary"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 88
            yield "        <div class=\"article-page-grid__intro prose prose-neutral max-w-none text-base leading-relaxed text-gray-700 mt-4\">
          ";
            // line 89
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["summary"] ?? null), "html", null, true);
            yield "
        </div>
      ";
        }
        // line 92
        yield "
      <div class=\"article-page-grid__meta flex flex-wrap items-center gap-2 mt-4 text-xs sm:text-sm\">
        ";
        // line 94
        if ((($tmp = ($context["article_updated_formatted"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 95
            yield "          <span class=\"inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-800\">
            <time datetime=\"";
            // line 96
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["article_changed_iso"] ?? null), "html", null, true);
            yield "\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["article_updated_formatted"] ?? null), "html", null, true);
            yield "</time>
          </span>
        ";
        }
        // line 99
        yield "        <span class=\"inline-flex items-center rounded-full border border-primary-200 bg-primary-50 px-3 py-1 font-medium text-primary-900\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Motaded Team"));
        yield "</span>
        ";
        // line 100
        if ((($tmp = ($context["article_read_time"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 101
            yield "          <span class=\"inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 font-medium text-amber-950\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["article_read_time"] ?? null), "html", null, true);
            yield "</span>
        ";
        }
        // line 103
        yield "        ";
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "addtoany", [], "any", false, false, true, 103)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 104
            yield "          <span class=\"article-meta__addtoany inline-flex items-center rounded-full border border-slate-200/90 bg-white/95 px-1.5 py-0.5 shadow-sm\">
            ";
            // line 105
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "addtoany", [], "any", false, false, true, 105), "html", null, true);
            yield "
          </span>
        ";
        }
        // line 108
        yield "      </div>
    </div>

    <aside class=\"article-page-grid__rail\" aria-label=\"";
        // line 111
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Article tools"));
        yield "\">
      ";
        // line 112
        if ((($tmp = ($context["article_hero_media"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 113
            yield "        <div class=\"article-hero-media overflow-hidden rounded-xl border border-gray-200 bg-gray-50 shadow-sm\">
          ";
            // line 114
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["article_hero_media"] ?? null), "html", null, true);
            yield "
        </div>
      ";
        }
        // line 117
        yield "
      <div class=\"article-toc-wrap\">
        <button
          class=\"md:hidden w-full flex items-center justify-between rounded-md border border-gray-300 bg-gray-50 px-4 py-3 text-sm font-semibold\"
          @click=\"tocOpen=!tocOpen\"
          :aria-expanded=\"tocOpen\"
          aria-controls=\"toc-panel\"
        >
          <span>";
        // line 125
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("On this page"));
        yield "</span>
          <img
            src=\"/themes/custom/motaded_theme/img/icon-arrow-down.svg\"
            alt=\"\"
            width=\"16\"
            height=\"16\"
            :class=\"tocOpen ? 'rotate-180' : ''\"
            class=\"transition-transform\"
          />
        </button>

        <div
          id=\"toc-panel\"
          class=\"table-of-content md:block mt-3 md:mt-0 px-4 py-4 bg-gray-50 rounded-lg border border-gray-200\"
          :class=\"tocOpen ? 'block' : 'hidden md:block'\"
        >
          <div class=\"mb-3\">
            <p class=\"text-sm font-semibold text-gray-800 m-0\">";
        // line 142
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("On this page"));
        yield "</p>
          </div>

          <nav class=\"space-y-1\" aria-label=\"";
        // line 145
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Table of contents"));
        yield "\">
            <template x-for=\"item in toc\" :key=\"item.id\">
              <div>
                <a
                  :href=\"`#\${item.id}`\"
                  @click.prevent=\"scrollTo(item.id)\"
                  class=\"block rounded-md px-3 py-2 text-sm transition\"
                  :class=\"activeId===item.id ? 'bg-primary-50 text-primary-700 ring-1 ring-primary-600/20' : 'hover:bg-white'\"
                  x-text=\"item.label\"
                ></a>

                <template x-if=\"item.children?.length\">
                  <ul class=\"mt-1 pl-3 space-y-1\">
                    <template x-for=\"sub in item.children\" :key=\"sub.id\">
                      <li>
                        <a
                          :href=\"`#\${sub.id}`\"
                          @click.prevent=\"scrollTo(sub.id)\"
                          class=\"block rounded-md px-3 py-1.5 text-[13px] text-gray-700 hover:bg-white\"
                          :class=\"activeId===sub.id ? 'bg-primary-50 text-primary-700 ring-1 ring-primary-600/20' : ''\"
                          x-text=\"sub.label\"
                        ></a>
                      </li>
                    </template>
                  </ul>
                </template>
              </div>
            </template>
          </nav>
        </div>

        <div class=\"mt-4 article-rail-cta-source\" :class=\"tocOpen ? 'block' : 'hidden md:block'\">
          ";
        // line 177
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Drupal\twig_tweak\TwigTweakExtension::drupalRegion("article_toc_cta"), "html", null, true);
        yield "
        </div>
      </div>
    </aside>

    <div
      ";
        // line 183
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["content_attributes"] ?? null), "addClass", ["article-page-grid__main", "article-body", "node__content", "prose", "prose-neutral", "max-w-none"], "method", false, false, true, 183), "html", null, true);
        yield "
      id=\"article-root\"
    >
      ";
        // line 186
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->withoutFilter(($context["content"] ?? null), "addtoany"), "html", null, true);
        yield "
    </div>
  </div>

</article>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["node", "view_mode", "attributes", "title_prefix", "label", "page", "title_attributes", "url", "title_suffix", "display_submitted", "author_picture", "author_attributes", "author_name", "date", "metadata", "summary", "article_updated_formatted", "article_changed_iso", "article_read_time", "content", "article_hero_media", "content_attributes"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/motaded_theme/templates/content/node--article--full.html.twig";
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
        return array (  340 => 186,  334 => 183,  325 => 177,  290 => 145,  284 => 142,  264 => 125,  254 => 117,  248 => 114,  245 => 113,  243 => 112,  239 => 111,  234 => 108,  228 => 105,  225 => 104,  222 => 103,  216 => 101,  214 => 100,  209 => 99,  201 => 96,  198 => 95,  196 => 94,  192 => 92,  186 => 89,  183 => 88,  181 => 87,  178 => 86,  170 => 84,  168 => 83,  164 => 81,  160 => 78,  152 => 74,  150 => 73,  146 => 72,  142 => 71,  139 => 70,  137 => 69,  131 => 67,  123 => 64,  118 => 63,  116 => 62,  112 => 61,  106 => 58,  100 => 55,  96 => 54,  92 => 53,  62 => 26,  58 => 25,  54 => 24,  52 => 19,  51 => 18,  50 => 17,  49 => 16,  48 => 15,  47 => 13,  44 => 5,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/motaded_theme/templates/content/node--article--full.html.twig", "/var/www/html/web/themes/custom/motaded_theme/templates/content/node--article--full.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 13, "if" => 62, "trans" => 73];
        static $filters = ["clean_class" => 15, "escape" => 24, "t" => 53, "without" => 186];
        static $functions = ["attach_library" => 24, "drupal_region" => 58];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if', 'trans'],
                ['clean_class', 'escape', 't', 'without'],
                ['attach_library', 'drupal_region'],
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
