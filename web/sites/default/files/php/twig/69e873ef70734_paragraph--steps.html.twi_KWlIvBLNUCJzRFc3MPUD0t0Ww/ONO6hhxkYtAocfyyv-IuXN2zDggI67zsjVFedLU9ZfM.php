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

/* themes/custom/motaded_theme/templates/paragraphs/paragraph--steps.html.twig */
class __TwigTemplate_f434a960d290ee84b15791e5b30fdbac extends Template
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
            'paragraph' => [$this, 'block_paragraph'],
            'content' => [$this, 'block_content'],
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
        // line 46
        yield "
";
        // line 48
        $context["classes"] = ["paragraph", ("paragraph--type--" . \Drupal\Component\Utility\Html::getClass(CoreExtension::getAttribute($this->env, $this->source,         // line 50
($context["paragraph"] ?? null), "bundle", [], "any", false, false, true, 50))), (((($tmp =         // line 51
($context["view_mode"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (("paragraph--view-mode--" . \Drupal\Component\Utility\Html::getClass(($context["view_mode"] ?? null)))) : ("")), (((($tmp =  !CoreExtension::getAttribute($this->env, $this->source,         // line 52
($context["paragraph"] ?? null), "isPublished", [], "method", false, false, true, 52)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("paragraph--unpublished") : (""))];
        // line 55
        yield from $this->unwrap()->yieldBlock('paragraph', $context, $blocks);
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["paragraph", "view_mode", "attributes", "content"]);        yield from [];
    }

    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_paragraph(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 56
        yield "  <div";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 56), "html", null, true);
        yield ">
    ";
        // line 57
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 174
        yield "  </div>
";
        yield from [];
    }

    // line 57
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 58
        yield "      <!-- Investor Journey Timeline -->
      <div class=\"py-14 bg-gray-100\">
        <div
          class=\"max-w-[1320px] mx-auto px-4\">
          <!-- Heading / CTA -->
          <div class=\"flex justify-between items-center flex-wrap gap-2\">
            <div class=\"section-title\">
              <h2 class=\"text-[1.625rem] font-bold text-primary-600 max-sm:text-2xl leading-tight\">";
        // line 65
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_title", [], "any", false, false, true, 65), 0, [], "any", false, false, true, 65), "html", null, true);
        yield "</h2>
              ";
        // line 66
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_body", [], "any", false, false, true, 66), 0, [], "any", false, false, true, 66), "html", null, true);
        yield "
            </div>
          </div>

          <div class=\"bg-white rounded-xl p-4 max-sm:px-1 sm:p-6 mt-5\" x-data=\"investorTimeline\">
            <div
              class=\"flex flex-row items-start gap-2\">
              <!-- Prev -->
              <button type=\"button\" class=\"inline-flex items-center justify-center rounded-[4px] text-sm px-2 py-1.5\" aria-label=\"Previous step\" @click=\"prev()\" :disabled=\"current === 0\" :class=\"current===0 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100'\">
                <img alt=\"Previous\" width=\"24\" height=\"24\" class=\"sm:size-6 rtl:-rotate-90 ltr:rotate-90\" src=\"/themes/custom/motaded_theme/img/icon-arrow-down.svg\"/>
              </button>

              <!-- Progress / Dots -->
              <div class=\"flex flex-col flex-1 items-center\">
                <div class=\"hidden lg:flex xl:flex relative justify-between w-full sm:w-[95%] pt-4\">
                  <template x-for=\"(step, i) in steps\" :key=\"i\">
                    <div
                      class=\"relative flex flex-col items-center w-full group\">
                      <!-- connector line (except the last dot visually stretches to next) -->
                      <div class=\"absolute top-[7px] left-1/2 rtl:-translate-x-full ltr:-translate-x-0 h-[2px] w-full z-0\" :class=\"i < current ? 'bg-primary-600' : 'bg-gray-200'\" x-show=\"i < steps.length - 1\"></div>

                      <!-- dot -->
                      <button type=\"button\" class=\"z-10 w-4 h-4 rounded-full border-4 flex items-center justify-center transition\" :class=\"i <= current
                                        ? 'border-primary-600 bg-primary-600'
                                        : 'border-primary-600 bg-white hover:bg-gray-50'\" @click=\"go(i)\" :aria-label=\"`Go to step \${i+1}: \${step.title}`\">
                        <span class=\"sr-only\" x-text=\"step.title\"></span>
                      </button>

                      <!-- label -->
                      <div class=\"text-center mt-3 text-[13px] font-semibold text-black\">
                        <span x-text=\"step.step_title\"></span>
                      </div>
                    </div>
                  </template>
                </div>

                <!-- Panel -->
                <div class=\"mt-8 w-full\">
                  <div
                    class=\"grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6\">
                    <!-- Image -->
                    <div class=\"mx-auto grid place-items-center\">
                      <img
                        :key=\"current\"
                        class=\"motaded-steps-panel__img rounded-lg border p-5 border-gray-200 max-w-full bg-white\"
                        loading=\"eager\"
                        decoding=\"async\"
                        sizes=\"(max-width: 1023px) 100vw, 400px\"
                        :alt=\"steps[current].img_alt || steps[current].title\"
                        :width=\"steps[current].img_width\"
                        :height=\"steps[current].img_height\"
                        :src=\"steps[current].img\"
                        x-bind:srcset=\"steps[current].img_srcset ? steps[current].img_srcset : null\"
                      />
                    </div>

                    <!-- Content -->
                    <div class=\"col-span-1 sm:col-span-2\">
                      <div class=\"flex flex-col gap-3 sm:gap-4\">
                        <p class=\"text-lg font-semibold text-primary-600 leading-[1.6]\">
                          ";
        // line 127
        yield "                          <span x-text=\"`";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Step @current of @length", ["@current" => "\${current+1}", "@length" => "\${steps.length}"]));
        yield "`\"></span>
                        </p>
                        <h2 class=\"text-[16px] font-bold\" x-text=\"steps[current].title\"></h2>
                        <div class=\"leading-[1.75]\" x-html=\"steps[current].desc\"></div>

                        <!-- Quick step badges row (optional, compact) -->
                        <!-- <div class=\"flex flex-wrap gap-1 mt-2\">
                                            <template x-for=\"(s, i) in steps\" :key=\"'badge'+i\">
                                              <button
                                                type=\"button\"
                                                class=\"text-xs px-2 py-1 rounded border transition\"
                                                :class=\"i===current ? 'bg-primary-50 border-primary-600 text-primary-700' : 'border-gray-300 hover:bg-gray-50'\"
                                                @click=\"go(i)\"
                                                x-text=\"i+1\"
                                                :aria-label=\"`Go to step \${i+1}`\"
                                              ></button>
                                            </template>
                                          </div> -->

                        <div
                          class=\"flex justify-between items-center mt-4\">
                          <!-- Prev -->
                          <button type=\"button\" class=\"cursor-pointer border-primary-600 border-1 inline-flex items-center justify-center rounded-[4px] text-sm px-2 py-1.5\" aria-label=\"Previous step\" @click=\"prev()\" :disabled=\"current === 0\" :class=\"current===0 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100'\">
                            <img alt=\"Previous\" width=\"24\" height=\"24\" class=\"sm:size-6 rtl:-rotate-90 ltr:rotate-90\" src=\"/themes/custom/motaded_theme/img/icon-arrow-down.svg\"/>
                            ";
        // line 151
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Previous step"));
        yield "
                          </button>
                          <!-- Next -->
                          <button type=\"button\" class=\"cursor-pointer border-primary-600 border-1 inline-flex items-center justify-center rounded-[4px] text-sm px-2 py-1.5\" aria-label=\"Next step\" @click=\"next()\" :disabled=\"current === steps.length - 1\" :class=\"current===steps.length-1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100'\">
                            ";
        // line 155
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Next step"));
        yield "
                            <img alt=\"Next\" width=\"24\" height=\"24\" class=\"sm:size-6 ltr:-rotate-90 rtl:rotate-90\" src=\"/themes/custom/motaded_theme/img/icon-arrow-down.svg\"/>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Next -->
              <button type=\"button\" class=\"inline-flex items-center justify-center rounded-[4px] text-sm px-2 py-1.5\" aria-label=\"Next step\" @click=\"next()\" :disabled=\"current === steps.length - 1\" :class=\"current===steps.length-1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100'\">
                <img alt=\"Next\" width=\"24\" height=\"24\" class=\"sm:size-6 ltr:-rotate-90 rtl:rotate-90\" src=\"/themes/custom/motaded_theme/img/icon-arrow-down.svg\"/>
              </button>
            </div>
          </div>
        </div>
      </div>
    ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/motaded_theme/templates/paragraphs/paragraph--steps.html.twig";
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
        return array (  200 => 155,  193 => 151,  165 => 127,  102 => 66,  98 => 65,  89 => 58,  82 => 57,  76 => 174,  74 => 57,  69 => 56,  57 => 55,  55 => 52,  54 => 51,  53 => 50,  52 => 48,  49 => 46,  46 => 5,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/motaded_theme/templates/paragraphs/paragraph--steps.html.twig", "/var/www/html/web/themes/custom/motaded_theme/templates/paragraphs/paragraph--steps.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 48, "block" => 55];
        static $filters = ["clean_class" => 50, "escape" => 56, "t" => 127];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'block'],
                ['clean_class', 'escape', 't'],
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
