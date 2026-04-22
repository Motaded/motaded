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

/* modules/contrib/better_exposed_filters/templates/bef-radios.html.twig */
class __TwigTemplate_81f3a38e7db0c873268dbf0b38b808bd extends Template
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
        // line 14
        $context["classes"] = ["form-radios", (((($tmp =         // line 16
($context["is_nested"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("bef-nested") : ("")), (((($tmp =         // line 17
($context["display_inline"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("form--inline") : (""))];
        // line 20
        yield "<div";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["wrapper_attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 20), "html", null, true);
        yield ">
  ";
        // line 21
        $context["current_nesting_level"] = 0;
        // line 22
        yield "  ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["children"] ?? null));
        $context['loop'] = [
          'parent' => $context['_parent'],
          'index0' => 0,
          'index'  => 1,
          'first'  => true,
        ];
        if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
            $length = count($context['_seq']);
            $context['loop']['revindex0'] = $length - 1;
            $context['loop']['revindex'] = $length;
            $context['loop']['length'] = $length;
            $context['loop']['last'] = 1 === $length;
        }
        foreach ($context['_seq'] as $context["_key"] => $context["child"]) {
            // line 23
            yield "    ";
            $context["item"] = CoreExtension::getAttribute($this->env, $this->source, ($context["element"] ?? null), $context["child"], [], "any", false, false, true, 23);
            // line 24
            yield "    ";
            if ((($tmp = ($context["is_nested"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 25
                yield "      ";
                $context["new_nesting_level"] = CoreExtension::getAttribute($this->env, $this->source, ($context["depth"] ?? null), $context["child"], [], "any", false, false, true, 25);
                // line 26
                yield "      ";
                yield from $this->load("@better_exposed_filters/bef-nested-elements.html.twig", 26)->unwrap()->yield($context);
                // line 27
                yield "      ";
                $context["current_nesting_level"] = ($context["new_nesting_level"] ?? null);
                // line 28
                yield "    ";
            } else {
                // line 29
                yield "      ";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["item"] ?? null), "html", null, true);
                yield "
    ";
            }
            // line 31
            yield "  ";
            ++$context['loop']['index0'];
            ++$context['loop']['index'];
            $context['loop']['first'] = false;
            if (isset($context['loop']['revindex0'], $context['loop']['revindex'])) {
                --$context['loop']['revindex0'];
                --$context['loop']['revindex'];
                $context['loop']['last'] = 0 === $context['loop']['revindex0'];
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['child'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 32
        yield "</div>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["is_nested", "display_inline", "wrapper_attributes", "children", "element", "depth"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/contrib/better_exposed_filters/templates/bef-radios.html.twig";
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
        return array (  111 => 32,  97 => 31,  91 => 29,  88 => 28,  85 => 27,  82 => 26,  79 => 25,  76 => 24,  73 => 23,  55 => 22,  53 => 21,  48 => 20,  46 => 17,  45 => 16,  44 => 14,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/contrib/better_exposed_filters/templates/bef-radios.html.twig", "/var/www/html/web/modules/contrib/better_exposed_filters/templates/bef-radios.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 14, "for" => 22, "if" => 24, "include" => 26];
        static $filters = ["escape" => 20];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'for', 'if', 'include'],
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
