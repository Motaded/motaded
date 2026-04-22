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

/* themes/custom/motaded_theme/templates/views/views-view-fields--related-similar.html.twig */
class __TwigTemplate_c7fc1509bbd3b40692c9d8e3d3088bba extends Template
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
        if (Twig\Extension\CoreExtension::testEmpty(($context["excerpt_text"] ?? null))) {
            // line 12
            yield "  ";
            $context["excerpt_text"] = t("Read the full article for key insights and practical guidance.");
        }
        // line 14
        $context["has_image"] =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "src", [], "any", false, false, true, 14));
        // line 15
        yield "
<a
  href=\"";
        // line 17
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["view_link"] ?? null), "html", null, true);
        yield "\"
  class=\"article-card article-card--related article-card--link group block h-full ";
        // line 18
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($tmp = ($context["has_image"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("article-card--has-image") : ("article-card--no-image")));
        yield "\"
  aria-label=\"";
        // line 19
        if ((($tmp = ($context["card_title_text"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("More about @title", ["@title" => ($context["card_title_text"] ?? null)]));
        } else {
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Read more"));
        }
        yield "\"
>
  <article class=\"article-card__inner relative h-full overflow-hidden rounded-xl border border-gray-200 shadow-sm transition duration-200\">
    ";
        // line 22
        if ((($tmp = ($context["has_image"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 23
            yield "      <div class=\"article-card__media article-card__media--cover relative z-0 w-full overflow-hidden\">
        ";
            // line 24
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "srcset", [], "any", false, false, true, 24)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 25
                yield "          <img
            loading=\"lazy\"
            decoding=\"async\"
            src=\"";
                // line 28
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "src", [], "any", false, false, true, 28), "html", null, true);
                yield "\"
            srcset=\"";
                // line 29
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "srcset", [], "any", false, false, true, 29), "html", null, true);
                yield "\"
            sizes=\"(max-width: 767px) 100vw, (min-width: 768px) and (max-width: 1023px) 50vw, 25vw\"
            width=\"";
                // line 31
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "width", [], "any", false, false, true, 31), "html", null, true);
                yield "\"
            height=\"";
                // line 32
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "height", [], "any", false, false, true, 32), "html", null, true);
                yield "\"
            alt=\"";
                // line 33
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "alt", [], "any", false, false, true, 33));
                yield "\"
            class=\"article-card__image w-full object-cover group-hover:opacity-95 transition\"
          />
        ";
            } else {
                // line 37
                yield "          <img
            loading=\"lazy\"
            decoding=\"async\"
            src=\"";
                // line 40
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "src", [], "any", false, false, true, 40), "html", null, true);
                yield "\"
            width=\"";
                // line 41
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "width", [], "any", false, false, true, 41), "html", null, true);
                yield "\"
            height=\"";
                // line 42
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "height", [], "any", false, false, true, 42), "html", null, true);
                yield "\"
            alt=\"";
                // line 43
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card_image"] ?? null), "alt", [], "any", false, false, true, 43));
                yield "\"
            class=\"article-card__image w-full object-cover group-hover:opacity-95 transition\"
          />
        ";
            }
            // line 47
            yield "        <span class=\"article-card__media-glow\" aria-hidden=\"true\"></span>
      </div>
    ";
        } else {
            // line 50
            yield "      <div class=\"article-card__media article-card__media--placeholder relative z-0 w-full shrink-0\" aria-hidden=\"true\"></div>
    ";
        }
        // line 52
        yield "
    ";
        // line 53
        if ((($tmp = ($context["card_tag"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 54
            yield "      <span class=\"article-card__tag article-card__tag--solid article-card__tag--corner\">
        ";
            // line 55
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["card_tag"] ?? null), "html", null, true);
            yield "
      </span>
    ";
        }
        // line 58
        yield "
    <div class=\"article-card__content relative z-10 flex min-h-0 flex-1 flex-col px-4 pb-4 ";
        // line 59
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($tmp = ($context["has_image"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("pt-5") : ("pt-11")));
        yield "\">
      ";
        // line 60
        if ((($tmp = ($context["card_date"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 61
            yield "        <div class=\"article-card__meta-row mb-1 flex items-center gap-2\">
          <span class=\"article-card__date\">";
            // line 62
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["card_date"] ?? null), "html", null, true);
            yield "</span>
        </div>
      ";
        }
        // line 65
        yield "
      <h3 class=\"article-card__title text-base font-bold leading-snug transition mt-4\">
        ";
        // line 67
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["fields"] ?? null), "title", [], "any", false, false, true, 67), "content", [], "any", false, false, true, 67), "html", null, true);
        yield "
      </h3>

      <p class=\"article-card__excerpt mb-0 mt-4 flex-1 text-sm leading-relaxed mb-4\">
        ";
        // line 71
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["excerpt_text"] ?? null), "html", null, true);
        yield "
      </p>

      <div class=\"article-card__cta-row mt-auto flex items-center justify-between border-t border-gray-200 pt-3\">
        <span class=\"article-card__cta text-sm font-semibold\">
          ";
        // line 76
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Read more"));
        yield "
        </span>
        <span class=\"article-card__cta-icon\" aria-hidden=\"true\">&#8594;</span>
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
        return "themes/custom/motaded_theme/templates/views/views-view-fields--related-similar.html.twig";
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
        return array (  206 => 76,  198 => 71,  191 => 67,  187 => 65,  181 => 62,  178 => 61,  176 => 60,  172 => 59,  169 => 58,  163 => 55,  160 => 54,  158 => 53,  155 => 52,  151 => 50,  146 => 47,  139 => 43,  135 => 42,  131 => 41,  127 => 40,  122 => 37,  115 => 33,  111 => 32,  107 => 31,  102 => 29,  98 => 28,  93 => 25,  91 => 24,  88 => 23,  86 => 22,  76 => 19,  72 => 18,  68 => 17,  64 => 15,  62 => 14,  58 => 12,  56 => 11,  54 => 10,  52 => 9,  50 => 8,  48 => 7,  46 => 6,  44 => 5,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/motaded_theme/templates/views/views-view-fields--related-similar.html.twig", "/var/www/html/web/themes/custom/motaded_theme/templates/views/views-view-fields--related-similar.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 5, "if" => 11];
        static $filters = ["default" => 5, "trim" => 6, "striptags" => 6, "render" => 6, "t" => 12, "escape" => 17, "e" => 33];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if'],
                ['default', 'trim', 'striptags', 'render', 't', 'escape', 'e'],
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
