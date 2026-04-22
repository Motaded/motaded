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

/* motaded_theme:card */
class __TwigTemplate_fa68a9c60cba9431e868d959f4beca40 extends Template
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
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("core/components.motaded_theme--card"));
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\ComponentsTwigExtension']->addAdditionalContext($context, "motaded_theme:card"));
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\ComponentsTwigExtension']->validateProps($context, "motaded_theme:card"));
        // line 5
        yield "
";
        // line 45
        yield "
<div class=\"bg-white border border-gray-200 rounded-lg mx-2 mt-2 mb-4 p-4 flex flex-col h-full transition hover:shadow-md\">
  <div class=\"mb-4 icon-wrap bg-primary-50 rounded-full size-[56px] flex items-center justify-center\">
    <img class=\"p-4\" alt=\"";
        // line 48
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
        yield "\" src=\"";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["icon_url"] ?? null), "html", null, true);
        yield "\"/>
  </div>
  <h3 class=\"mb-4 font-bold text-lg text-gray-900\">
    ";
        // line 51
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
        yield "
  </h3>
  <p class=\"mb-4 text-gray-600 text-sm leading-relaxed\">
    ";
        // line 54
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["desc"] ?? null), "html", null, true);
        yield "
  </p>
  <div class=\"flex flex-wrap gap-2 mb-4\">
    ";
        // line 57
        if ((($tmp = ($context["tags"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 58
            yield "      <div class=\"flex flex-wrap gap-2 mb-4\">
        ";
            // line 59
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["tags"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["tag"]) {
                // line 60
                yield "          ";
                if ((($tmp = $context["tag"]) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 61
                    yield "            <span class=\"border px-2 py-1 rounded text-xs font-medium bg-gray-50 text-gray-700 border-gray-200\">";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $context["tag"], "html", null, true);
                    yield "</span>
          ";
                }
                // line 63
                yield "        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['tag'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 64
            yield "      </div>
    ";
        }
        // line 66
        yield "  </div>
  ";
        // line 67
        if ((($context["cta1"] ?? null) || ($context["cta2"] ?? null))) {
            // line 68
            yield "    <div class=\"mt-auto flex gap-2\">
      ";
            // line 69
            if ((($tmp = ($context["cta1"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 70
                yield "        <a href=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["cta1"] ?? null), "url", [], "any", false, false, true, 70), "html", null, true);
                yield "\" class=\"px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-100\">
          ";
                // line 71
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["cta1"] ?? null), "title", [], "any", false, false, true, 71), "html", null, true);
                yield "
        </a>
      ";
            }
            // line 74
            yield "      ";
            if ((($tmp = ($context["cta2"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 75
                yield "        <a href=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["cta2"] ?? null), "url", [], "any", false, false, true, 75), "html", null, true);
                yield "\" class=\"px-4 py-2 rounded-md bg-primary-600 text-white text-sm hover:bg-primary-700\">
          ";
                // line 76
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["cta2"] ?? null), "title", [], "any", false, false, true, 76), "html", null, true);
                yield "
        </a>
      ";
            }
            // line 79
            yield "    </div>
  ";
        }
        // line 81
        yield "</div>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["title", "icon_url", "desc", "tags", "cta1", "cta2"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "motaded_theme:card";
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
        return array (  143 => 81,  139 => 79,  133 => 76,  128 => 75,  125 => 74,  119 => 71,  114 => 70,  112 => 69,  109 => 68,  107 => 67,  104 => 66,  100 => 64,  94 => 63,  88 => 61,  85 => 60,  81 => 59,  78 => 58,  76 => 57,  70 => 54,  64 => 51,  56 => 48,  51 => 45,  48 => 5,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "motaded_theme:card", "themes/custom/motaded_theme/components/card/card.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["if" => 57, "for" => 59];
        static $filters = ["escape" => 48];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['if', 'for'],
                ['escape'],
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
