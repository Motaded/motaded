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

/* themes/custom/motaded_theme/templates/views/views-view-fields--news--block_3.html.twig */
class __TwigTemplate_995245e58e0e4dcec1aeace5bcedd9e7 extends Template
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
        $context["card_image"] = ((array_key_exists("card_image", $context)) ? (Twig\Extension\CoreExtension::default(($context["card_image"] ?? null), [])) : ([]));
        // line 6
        $context["view_link"] = Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["fields"] ?? null), "view_node", [], "any", false, false, true, 6), "content", [], "any", false, false, true, 6))));
        // line 7
        $context["card_tag"] = Twig\Extension\CoreExtension::trim((((array_key_exists("card_type_label", $context) &&  !(null === $context["card_type_label"]))) ? ($context["card_type_label"]) : ("")));
        // line 8
        $context["card_title_text"] = Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["fields"] ?? null), "title", [], "any", false, false, true, 8), "content", [], "any", false, false, true, 8))));
        // line 9
        $context["card_date"] = Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["fields"] ?? null), "created", [], "any", false, false, true, 9), "content", [], "any", false, false, true, 9))));
        // line 10
        $context["excerpt_text"] = Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["fields"] ?? null), "body", [], "any", false, false, true, 10), "content", [], "any", false, false, true, 10))));
        // line 11
        $context["has_image"] =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "src", [], "any", false, false, true, 11));
        // line 12
        yield "
<a
  href=\"";
        // line 14
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["view_link"] ?? null), "html", null, true);
        yield "\"
  class=\"article-card article-card--link group block h-full pt-3\"
  aria-label=\"";
        // line 16
        if ((($tmp = ($context["card_title_text"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("More about @title", ["@title" => ($context["card_title_text"] ?? null)]));
        } else {
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Read more"));
        }
        yield "\"
>
  <article class=\"article-card__inner h-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition duration-200\">
    <div class=\"article-card__media relative h-[180px] w-full overflow-hidden md:h-[200px]\">
      ";
        // line 20
        if ((($tmp = ($context["has_image"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 21
            yield "        <img
          alt=\"";
            // line 22
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "alt", [], "any", true, true, true, 22)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "alt", [], "any", false, false, true, 22), ($context["card_title_text"] ?? null))) : (($context["card_title_text"] ?? null))));
            yield "\"
          class=\"article-card__image h-full w-full object-cover transition duration-300\"
          src=\"";
            // line 24
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "src", [], "any", false, false, true, 24), "html", null, true);
            yield "\"
          ";
            // line 25
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "srcset", [], "any", false, false, true, 25)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 26
                yield "          srcset=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "srcset", [], "any", false, false, true, 26), "html", null, true);
                yield "\"
          sizes=\"(max-width: 767px) 100vw, (min-width: 768px) and (max-width: 1023px) 50vw, 25vw\"
          ";
            }
            // line 29
            yield "          width=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "width", [], "any", true, true, true, 29)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "width", [], "any", false, false, true, 29), 151)) : (151)), "html", null, true);
            yield "\"
          height=\"";
            // line 30
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "height", [], "any", true, true, true, 30)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "height", [], "any", false, false, true, 30), 120)) : (120)), "html", null, true);
            yield "\"
          loading=\"lazy\"
          decoding=\"async\"
        />
      ";
        } else {
            // line 35
            yield "        <div class=\"article-card__media article-card__media--placeholder relative z-0 w-full h-full\" aria-hidden=\"true\"></div>
      ";
        }
        // line 37
        yield "      <span class=\"article-card__media-glow\" aria-hidden=\"true\"></span>
    </div>

    <div class=\"article-card__content flex flex-1 flex-col p-4\">
      ";
        // line 41
        if ((($tmp = ($context["card_date"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 42
            yield "        <div class=\"article-card__meta-row mb-2 flex justify-end\">
          <span class=\"article-card__date\">
            ";
            // line 44
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["card_date"] ?? null), "html", null, true);
            yield "
          </span>
        </div>
      ";
        }
        // line 48
        yield "
      <div class=\"article-card__title-row mb-3 flex min-w-0 items-center gap-2\">
        ";
        // line 50
        if ((($tmp = ($context["card_tag"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 51
            yield "          <span class=\"article-card__tag shrink-0\">
            ";
            // line 52
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["card_tag"] ?? null), "html", null, true);
            yield "
          </span>
        ";
        }
        // line 55
        yield "        <h3 class=\"article-card__title m-0 min-w-0 flex-1 text-lg font-bold leading-snug text-gray-900 transition\">
          ";
        // line 56
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["fields"] ?? null), "title", [], "any", false, false, true, 56), "content", [], "any", false, false, true, 56), "html", null, true);
        yield "
        </h3>
      </div>

      ";
        // line 60
        if ((($tmp = ($context["excerpt_text"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 61
            yield "        <p class=\"article-card__excerpt mb-4 mt-0 flex-1 text-sm leading-6 text-gray-600\">
          ";
            // line 62
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["excerpt_text"] ?? null), "html", null, true);
            yield "
        </p>
      ";
        }
        // line 65
        yield "
      <div class=\"article-card__cta-row mt-4 flex items-center justify-between border-t border-gray-100 pt-3\">
        <span class=\"article-card__cta text-sm font-semibold text-primary-600\">
          ";
        // line 68
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Read more"));
        yield "
        </span>
        <span class=\"article-card__cta-icon text-primary-600\" aria-hidden=\"true\">
          &#8594;
        </span>
      </div>
    </div>
  </article>
</a>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["fields", "card_type_label"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/motaded_theme/templates/views/views-view-fields--news--block_3.html.twig";
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
        return array (  178 => 68,  173 => 65,  167 => 62,  164 => 61,  162 => 60,  155 => 56,  152 => 55,  146 => 52,  143 => 51,  141 => 50,  137 => 48,  130 => 44,  126 => 42,  124 => 41,  118 => 37,  114 => 35,  106 => 30,  101 => 29,  94 => 26,  92 => 25,  88 => 24,  83 => 22,  80 => 21,  78 => 20,  67 => 16,  62 => 14,  58 => 12,  56 => 11,  54 => 10,  52 => 9,  50 => 8,  48 => 7,  46 => 6,  44 => 5,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/motaded_theme/templates/views/views-view-fields--news--block_3.html.twig", "/var/www/html/web/themes/custom/motaded_theme/templates/views/views-view-fields--news--block_3.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 5, "if" => 16];
        static $filters = ["default" => 5, "trim" => 6, "striptags" => 6, "render" => 6, "escape" => 14, "t" => 16, "e" => 22];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if'],
                ['default', 'trim', 'striptags', 'render', 'escape', 't', 'e'],
                [],
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
