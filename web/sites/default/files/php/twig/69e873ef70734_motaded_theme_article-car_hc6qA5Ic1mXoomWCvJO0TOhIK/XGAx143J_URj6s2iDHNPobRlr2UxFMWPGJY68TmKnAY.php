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

/* motaded_theme:article-card */
class __TwigTemplate_3773bb23f1849c7033d12145f854c200 extends Template
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
        // line 1
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("core/components.motaded_theme--article-card"));
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\ComponentsTwigExtension']->addAdditionalContext($context, "motaded_theme:article-card"));
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\ComponentsTwigExtension']->validateProps($context, "motaded_theme:article-card"));
        // line 5
        $context["card_title_text"] = Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(($context["title"] ?? null))));
        // line 6
        $context["service_type_text"] = Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(((array_key_exists("service_type", $context)) ? (Twig\Extension\CoreExtension::default(($context["service_type"] ?? null), "")) : ("")))));
        // line 7
        $context["card_img_alt"] = Twig\Extension\CoreExtension::trim((((array_key_exists("image_alt", $context) &&  !(null === $context["image_alt"]))) ? ($context["image_alt"]) : ("")));
        // line 8
        if (Twig\Extension\CoreExtension::testEmpty(($context["card_img_alt"] ?? null))) {
            // line 9
            yield "  ";
            $context["card_img_alt"] = ($context["card_title_text"] ?? null);
        }
        // line 11
        if (Twig\Extension\CoreExtension::testEmpty(($context["card_img_alt"] ?? null))) {
            // line 12
            yield "  ";
            $context["card_img_alt"] = t("Article image");
        }
        // line 14
        yield "
";
        // line 15
        if ((($tmp = ($context["view_link"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 16
            yield "<a
  href=\"";
            // line 17
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["view_link"] ?? null), "html", null, true);
            yield "\"
  class=\"article-card article-card--link group block h-full pt-3";
            // line 18
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($tmp = ($context["service_type_text"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (" article-card--has-service-type") : ("")));
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
";
        } else {
            // line 22
            yield "<div class=\"article-card article-card--static group block h-full";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($tmp = ($context["service_type_text"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (" article-card--has-service-type") : ("")));
            yield "\">
";
        }
        // line 24
        yield "  <article class=\"article-card__inner h-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition duration-200\">
    <div class=\"article-card__media relative h-[180px] w-full overflow-hidden md:h-[200px]\">
      <img
        alt=\"";
        // line 27
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["card_img_alt"] ?? null));
        yield "\"
        class=\"article-card__image h-full w-full object-cover transition duration-300\"
        src=\"";
        // line 29
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["media_url"] ?? null), "html", null, true);
        yield "\"
        ";
        // line 30
        if ((($tmp = ((array_key_exists("media_srcset", $context)) ? (Twig\Extension\CoreExtension::default(($context["media_srcset"] ?? null), "")) : (""))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 31
            yield "        srcset=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["media_srcset"] ?? null), "html", null, true);
            yield "\"
        sizes=\"";
            // line 32
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((array_key_exists("media_sizes", $context)) ? (Twig\Extension\CoreExtension::default(($context["media_sizes"] ?? null), "(max-width: 767px) 100vw, (min-width: 768px) and (max-width: 1023px) 50vw, 25vw")) : ("(max-width: 767px) 100vw, (min-width: 768px) and (max-width: 1023px) 50vw, 25vw")), "html", null, true);
            yield "\"
        ";
        }
        // line 34
        yield "        width=\"";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((array_key_exists("media_width", $context)) ? (Twig\Extension\CoreExtension::default(($context["media_width"] ?? null), 650)) : (650)), "html", null, true);
        yield "\"
        height=\"";
        // line 35
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((array_key_exists("media_height", $context)) ? (Twig\Extension\CoreExtension::default(($context["media_height"] ?? null), 650)) : (650)), "html", null, true);
        yield "\"
        loading=\"lazy\"
        decoding=\"async\"
      />
      ";
        // line 39
        if ((($tmp = ($context["service_type_text"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 40
            yield "        <span class=\"article-card__tag article-card__tag--media article-card__tag--service\">
          ";
            // line 41
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["service_type"] ?? null), "html", null, true);
            yield "
        </span>
      ";
        }
        // line 44
        yield "      <span class=\"article-card__media-glow\" aria-hidden=\"true\"></span>
    </div>

    <div class=\"article-card__content flex flex-1 flex-col p-4\">
      ";
        // line 48
        if ((($tmp = ($context["date"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 49
            yield "        <div class=\"article-card__meta-row mb-2 flex justify-end\">
          <span class=\"article-card__date\">
            ";
            // line 51
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["date"] ?? null), "html", null, true);
            yield "
          </span>
        </div>
      ";
        }
        // line 55
        yield "
      <div class=\"article-card__title-row mb-3 flex min-w-0 items-center gap-2\">
        ";
        // line 57
        $context["card_tag"] = Twig\Extension\CoreExtension::trim(((array_key_exists("tag", $context)) ? (Twig\Extension\CoreExtension::default(($context["tag"] ?? null), "")) : ("")));
        // line 58
        yield "        ";
        if ((($context["card_tag"] ?? null) &&  !($context["service_type_text"] ?? null))) {
            // line 59
            yield "          <span class=\"article-card__tag shrink-0\">
            ";
            // line 60
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["card_tag"] ?? null), "html", null, true);
            yield "
          </span>
        ";
        }
        // line 63
        yield "        <h3 class=\"article-card__title m-0 min-w-0 flex-1 text-lg font-bold leading-snug text-gray-900 transition\">
          ";
        // line 64
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
        yield "
        </h3>
      </div>

      ";
        // line 68
        if ((($tmp = ($context["excerpt"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 69
            yield "        <p class=\"article-card__excerpt mb-4 mt-0 flex-1 text-sm leading-6 text-gray-600\">
          ";
            // line 70
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["excerpt"] ?? null), "html", null, true);
            yield "
        </p>
      ";
        }
        // line 73
        yield "
      ";
        // line 74
        if ((($tmp = ($context["view_link"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 75
            yield "        <div class=\"article-card__cta-row mt-4 flex items-center justify-between border-t border-gray-100 pt-3\">
          <span class=\"article-card__cta text-sm font-semibold text-primary-600\">
            ";
            // line 77
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Read more"));
            yield "
          </span>
          <span class=\"article-card__cta-icon text-primary-600\" aria-hidden=\"true\">
            &#8594;
          </span>
        </div>
      ";
        }
        // line 84
        yield "    </div>
  </article>
";
        // line 86
        if ((($tmp = ($context["view_link"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 87
            yield "</a>
";
        } else {
            // line 89
            yield "</div>
";
        }
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["title", "service_type", "image_alt", "view_link", "media_url", "media_srcset", "media_sizes", "media_width", "media_height", "date", "tag", "excerpt"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "motaded_theme:article-card";
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
        return array (  234 => 89,  230 => 87,  228 => 86,  224 => 84,  214 => 77,  210 => 75,  208 => 74,  205 => 73,  199 => 70,  196 => 69,  194 => 68,  187 => 64,  184 => 63,  178 => 60,  175 => 59,  172 => 58,  170 => 57,  166 => 55,  159 => 51,  155 => 49,  153 => 48,  147 => 44,  141 => 41,  138 => 40,  136 => 39,  129 => 35,  124 => 34,  119 => 32,  114 => 31,  112 => 30,  108 => 29,  103 => 27,  98 => 24,  92 => 22,  82 => 19,  78 => 18,  74 => 17,  71 => 16,  69 => 15,  66 => 14,  62 => 12,  60 => 11,  56 => 9,  54 => 8,  52 => 7,  50 => 6,  48 => 5,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "motaded_theme:article-card", "themes/custom/motaded_theme/components/article-card/article-card.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 5, "if" => 8];
        static $filters = ["trim" => 5, "striptags" => 5, "render" => 5, "default" => 6, "t" => 12, "escape" => 17, "e" => 27];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if'],
                ['trim', 'striptags', 'render', 'default', 't', 'escape', 'e'],
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
