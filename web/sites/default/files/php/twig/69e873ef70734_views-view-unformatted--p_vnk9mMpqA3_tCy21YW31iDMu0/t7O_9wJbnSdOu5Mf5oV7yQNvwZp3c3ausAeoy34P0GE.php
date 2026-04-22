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

/* themes/custom/motaded_theme/templates/views/views-view-unformatted--partners--block_2.html.twig */
class __TwigTemplate_22b77f8031bed404e7c9ed5ca5aeb18e extends Template
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
        // line 4
        yield "
";
        // line 5
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("motaded_theme/partner-carousel"), "html", null, true);
        yield "

<div class=\"partners-swiper swiper\" role=\"region\" aria-label=\"";
        // line 7
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Partner logos carousel"));
        yield "\">
  <div class=\"swiper-wrapper\">
    ";
        // line 9
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["rows"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["row"]) {
            // line 10
            yield "    <div class=\"swiper-slide\">
      <div class=\"border rounded-lg p-2 bg-white hover:shadow-sm transition h-full\">
        ";
            // line 13
            yield "        <a href=\"#\" class=\"block\">
          <div class=\"mx-auto grid place-items-center\">
            ";
            // line 15
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 15), "src", [], "any", false, false, true, 15)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 16
                yield "              ";
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 16), "srcset", [], "any", false, false, true, 16)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 17
                    yield "                <img class=\"p-3 max-h-16 object-contain w-full\" loading=\"lazy\" decoding=\"async\" alt=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 17), "alt", [], "any", false, false, true, 17), "html", null, true);
                    yield "\" src=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 17), "src", [], "any", false, false, true, 17), "html", null, true);
                    yield "\" srcset=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 17), "srcset", [], "any", false, false, true, 17), "html", null, true);
                    yield "\" sizes=\"(max-width: 767px) 45vw, 160px\" width=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 17), "width", [], "any", false, false, true, 17), "html", null, true);
                    yield "\" height=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 17), "height", [], "any", false, false, true, 17), "html", null, true);
                    yield "\"/>
              ";
                } else {
                    // line 19
                    yield "                <img class=\"p-3 max-h-16 object-contain w-full\" loading=\"lazy\" decoding=\"async\" alt=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 19), "alt", [], "any", false, false, true, 19), "html", null, true);
                    yield "\" src=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 19), "src", [], "any", false, false, true, 19), "html", null, true);
                    yield "\" width=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 19), "width", [], "any", false, false, true, 19), "html", null, true);
                    yield "\" height=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["row"], "image", [], "any", false, false, true, 19), "height", [], "any", false, false, true, 19), "html", null, true);
                    yield "\"/>
              ";
                }
                // line 21
                yield "            ";
            }
            // line 22
            yield "          </div>
        </a>
      </div>
    </div>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['row'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 27
        yield "  </div>
  <div class=\"swiper-pagination mt-3\"></div>
</div>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["rows"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/motaded_theme/templates/views/views-view-unformatted--partners--block_2.html.twig";
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
        return array (  113 => 27,  103 => 22,  100 => 21,  88 => 19,  74 => 17,  71 => 16,  69 => 15,  65 => 13,  61 => 10,  57 => 9,  52 => 7,  47 => 5,  44 => 4,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/motaded_theme/templates/views/views-view-unformatted--partners--block_2.html.twig", "/var/www/html/web/themes/custom/motaded_theme/templates/views/views-view-unformatted--partners--block_2.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["for" => 9, "if" => 15];
        static $filters = ["escape" => 5, "t" => 7];
        static $functions = ["attach_library" => 5];

        try {
            $this->sandbox->checkSecurity(
                ['for', 'if'],
                ['escape', 't'],
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
