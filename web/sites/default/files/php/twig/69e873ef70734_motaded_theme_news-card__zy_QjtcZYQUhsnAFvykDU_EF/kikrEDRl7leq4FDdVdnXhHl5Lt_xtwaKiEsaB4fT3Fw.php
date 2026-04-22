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

/* motaded_theme:news-card */
class __TwigTemplate_1ecd66082008129f3e089fdbd4d896f5 extends Template
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
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("core/components.motaded_theme--news-card"));
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\ComponentsTwigExtension']->addAdditionalContext($context, "motaded_theme:news-card"));
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\ComponentsTwigExtension']->validateProps($context, "motaded_theme:news-card"));
        // line 5
        $context["title_plain"] = Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(($context["title"] ?? null))));
        // line 6
        $context["img_alt"] = Twig\Extension\CoreExtension::trim((((array_key_exists("image_alt", $context) &&  !(null === $context["image_alt"]))) ? ($context["image_alt"]) : ("")));
        // line 7
        if (Twig\Extension\CoreExtension::testEmpty(($context["img_alt"] ?? null))) {
            // line 8
            yield "  ";
            $context["img_alt"] = ($context["title_plain"] ?? null);
        }
        // line 10
        if (Twig\Extension\CoreExtension::testEmpty(($context["img_alt"] ?? null))) {
            // line 11
            yield "  ";
            $context["img_alt"] = t("News image");
        }
        // line 13
        yield "
<a href=\"";
        // line 14
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["view_link"] ?? null), "html", null, true);
        yield "\" class=\"block\">
  <div
    class=\"bg-white rounded-xl p-4 h-full w-full border border-transparent hover:border-primary-600 shadow-sm transition\"
  >
    <div class=\"flex justify-between gap-4 h-full\">
      <div class=\"flex-1\">
        <div class=\"flex flex-col justify-between h-full\">
          <p class=\"text-md font-semibold leading-[1.6]\">";
        // line 21
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
        yield "</p>
          <p class=\"text-sm text-gray-600 mt-3 leading-[1.6]\">";
        // line 22
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["date"] ?? null), "html", null, true);
        yield "</p>
        </div>
      </div>
      <div class=\"flex-shrink-0 w-[151px] h-[120px]\">
        <img
          alt=\"";
        // line 27
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["img_alt"] ?? null));
        yield "\"
          width=\"";
        // line 28
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((array_key_exists("media_width", $context)) ? (Twig\Extension\CoreExtension::default(($context["media_width"] ?? null), 151)) : (151)), "html", null, true);
        yield "\"
          height=\"";
        // line 29
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((array_key_exists("media_height", $context)) ? (Twig\Extension\CoreExtension::default(($context["media_height"] ?? null), 120)) : (120)), "html", null, true);
        yield "\"
          class=\"w-full h-full object-cover rounded-lg\"
          src=\"";
        // line 31
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["media_url"] ?? null), "html", null, true);
        yield "\"
          ";
        // line 32
        if ((($tmp = ((array_key_exists("media_srcset", $context)) ? (Twig\Extension\CoreExtension::default(($context["media_srcset"] ?? null), "")) : (""))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 33
            yield "          srcset=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["media_srcset"] ?? null), "html", null, true);
            yield "\"
          sizes=\"";
            // line 34
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((array_key_exists("media_sizes", $context)) ? (Twig\Extension\CoreExtension::default(($context["media_sizes"] ?? null), "151px")) : ("151px")), "html", null, true);
            yield "\"
          ";
        }
        // line 36
        yield "          loading=\"lazy\"
          decoding=\"async\"
        />
      </div>
    </div>
  </div>
</a>";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["title", "image_alt", "view_link", "date", "media_width", "media_height", "media_url", "media_srcset", "media_sizes"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "motaded_theme:news-card";
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
        return array (  118 => 36,  113 => 34,  108 => 33,  106 => 32,  102 => 31,  97 => 29,  93 => 28,  89 => 27,  81 => 22,  77 => 21,  67 => 14,  64 => 13,  60 => 11,  58 => 10,  54 => 8,  52 => 7,  50 => 6,  48 => 5,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "motaded_theme:news-card", "themes/custom/motaded_theme/components/news-card/news-card.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 5, "if" => 7];
        static $filters = ["trim" => 5, "striptags" => 5, "render" => 5, "t" => 11, "escape" => 14, "e" => 27, "default" => 28];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if'],
                ['trim', 'striptags', 'render', 't', 'escape', 'e', 'default'],
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
