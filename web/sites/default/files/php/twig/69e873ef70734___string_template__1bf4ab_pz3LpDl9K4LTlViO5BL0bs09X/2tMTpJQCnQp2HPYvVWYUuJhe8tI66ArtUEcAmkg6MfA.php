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

/* __string_template__1bf4abf4ef04fdf611f417f565d7911b */
class __TwigTemplate_5026aecc6beb8708bf82ba3d883e95e3 extends Template
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
        yield "  ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(Twig\Extension\CoreExtension::include($this->env, $context, "motaded_theme:card", ["title" =>         // line 2
($context["title"] ?? null), "desc" =>         // line 3
($context["body"] ?? null), "icon_url" => "/themes/custom/motaded_theme/img/icon-checkmark-circle.svg", "tags" => (((($tmp = (!Twig\Extension\CoreExtension::testEmpty(        // line 5
($context["field_tags"] ?? null)))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (Twig\Extension\CoreExtension::split($this->env->getCharset(), ($context["field_tags"] ?? null), ",")) : (false)), "cta1" => ["url" =>         // line 6
($context["view_node"] ?? null), "title" => t("Read more")], "cta2" => ["url" => ("/contact-us?service-id=" .         // line 7
($context["nid"] ?? null)), "title" => t("Book now")], "icon_position" => "topLeft"], false));
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["title", "body", "field_tags", "view_node", "nid"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "__string_template__1bf4abf4ef04fdf611f417f565d7911b";
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
        return array (  50 => 7,  49 => 6,  48 => 5,  47 => 3,  46 => 2,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "__string_template__1bf4abf4ef04fdf611f417f565d7911b", "");
    }
    
    public function checkSecurity()
    {
        static $tags = [];
        static $filters = ["split" => 5, "t" => 6];
        static $functions = ["include" => 1];

        try {
            $this->sandbox->checkSecurity(
                [],
                ['split', 't'],
                ['include'],
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
