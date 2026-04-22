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

/* themes/custom/motaded_theme/templates/views/views-view-fields--services--block_2.html.twig */
class __TwigTemplate_330bedb6e08f45284d77b793a3d7bb87 extends Template
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
        // line 38
        yield "
<article class=\"icon-card flex gap-4 rounded-2xl border border-gray-200 bg-white p-6 flex-col\">
    <div class=\"icon-wrapper flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50\">
      <img
        src=\"/themes/custom/motaded_theme/img/icon-target.svg\"
        alt=\"";
        // line 43
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["fields"] ?? null), "title", [], "any", false, false, true, 43), "content", [], "any", false, false, true, 43), "html", null, true);
        yield "\"
        class=\"h-6 w-6\"
      />
    </div>
  <div class=\"flex flex-col flex-1 gap-2\">
    <h3 class=\"text-lg font-semibold leading-7 text-neutral-900\">
      ";
        // line 49
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["fields"] ?? null), "title", [], "any", false, false, true, 49), "content", [], "any", false, false, true, 49), "html", null, true);
        yield "
    </h3>
    <p class=\"text-sm leading-5 text-gray-700\">";
        // line 51
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["fields"] ?? null), "body", [], "any", false, false, true, 51), "content", [], "any", false, false, true, 51), "html", null, true);
        yield "</p>
    ";
        // line 52
        $context["tags"] = Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["fields"] ?? null), "field_tags", [], "any", false, false, true, 52), "content", [], "any", false, false, true, 52)));
        // line 53
        yield "
    ";
        // line 54
        if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(($context["tags"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 55
            yield "      <div class=\"flex flex-wrap gap-2 mb-4\">
      ";
            // line 56
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(Twig\Extension\CoreExtension::split($this->env->getCharset(), ($context["tags"] ?? null), ","));
            foreach ($context['_seq'] as $context["_key"] => $context["tag"]) {
                // line 57
                yield "        ";
                if ((($tmp = $context["tag"]) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 58
                    yield "          <span
            class=\"border px-2 py-1 rounded text-xs font-medium bg-gray-50 text-gray-700 border-gray-200\"
          >";
                    // line 60
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $context["tag"], "html", null, true);
                    yield "</span>
        ";
                }
                // line 62
                yield "      ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['tag'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 63
            yield "      </div>
    ";
        }
        // line 65
        yield "    <div class=\"mt-auto flex gap-2\">
      <a
        href=\"";
        // line 67
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["fields"] ?? null), "view_node", [], "any", false, false, true, 67), "content", [], "any", false, false, true, 67))), "html", null, true);
        yield "\"
        class=\"px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-100\"
      >
        ";
        // line 70
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Explore benefits"));
        yield "
      </a>

      ";
        // line 73
        $context["contact_url"] = ($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getUrl("<front>")) . "/contact-us");
        // line 74
        yield "
      <form action=\"";
        // line 75
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::replace(($context["contact_url"] ?? null), ["//c" => "/c"]), "html", null, true);
        yield "\" method=\"post\">
        <input type=\"hidden\" name=\"service_id\" value=\"";
        // line 76
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::trim(Twig\Extension\CoreExtension::striptags(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["fields"] ?? null), "nid", [], "any", false, false, true, 76), "content", [], "any", false, false, true, 76))), "html", null, true);
        yield "\">
        <input type=\"submit\" class=\"px-4 py-2 rounded-md bg-primary-600 text-white text-sm hover:bg-primary-700 cursor-pointer\" value=\"";
        // line 77
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Book now"));
        yield "\">
      </form>
    </div>
  </div>
</article>


";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["fields"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/motaded_theme/templates/views/views-view-fields--services--block_2.html.twig";
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
        return array (  134 => 77,  130 => 76,  126 => 75,  123 => 74,  121 => 73,  115 => 70,  109 => 67,  105 => 65,  101 => 63,  95 => 62,  90 => 60,  86 => 58,  83 => 57,  79 => 56,  76 => 55,  74 => 54,  71 => 53,  69 => 52,  65 => 51,  60 => 49,  51 => 43,  44 => 38,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/motaded_theme/templates/views/views-view-fields--services--block_2.html.twig", "/var/www/html/web/themes/custom/motaded_theme/templates/views/views-view-fields--services--block_2.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 52, "if" => 54, "for" => 56];
        static $filters = ["escape" => 43, "trim" => 52, "striptags" => 52, "split" => 56, "t" => 70, "render" => 73, "replace" => 75];
        static $functions = ["url" => 73];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if', 'for'],
                ['escape', 'trim', 'striptags', 'split', 't', 'render', 'replace'],
                ['url'],
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
