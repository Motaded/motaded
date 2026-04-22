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

/* themes/custom/motaded_theme/templates/block/block--bundle--global_cta.html.twig */
class __TwigTemplate_3e439f585a5a62d871deab04d49d14d0 extends Template
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
            'content' => [$this, 'block_content'],
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 6
        $context["show_title"] = (((array_key_exists("show_title_on_button", $context) &&  !(null === $context["show_title_on_button"]))) ? ($context["show_title_on_button"]) : (true));
        // line 8
        $context["base_classes"] = ["block", "block-cta", ("block-" . \Drupal\Component\Utility\Html::getClass(CoreExtension::getAttribute($this->env, $this->source,         // line 11
($context["configuration"] ?? null), "provider", [], "any", false, false, true, 11)))];
        // line 14
        $context["classes"] = (((($tmp =  !($context["show_title"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (Twig\Extension\CoreExtension::merge(($context["base_classes"] ?? null), ["block-cta--icon-only"])) : (($context["base_classes"] ?? null)));
        // line 15
        yield "<div";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 15), "html", null, true);
        yield ">
  ";
        // line 16
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title_prefix"] ?? null), "html", null, true);
        yield "
  ";
        // line 17
        if ((($context["label"] ?? null) && CoreExtension::getAttribute($this->env, $this->source, ($context["configuration"] ?? null), "label_display", [], "any", false, false, true, 17))) {
            // line 18
            yield "    <h2";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title_attributes"] ?? null), "html", null, true);
            yield ">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["label"] ?? null), "html", null, true);
            yield "</h2>
  ";
        }
        // line 20
        yield "  ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title_suffix"] ?? null), "html", null, true);
        yield "
  ";
        // line 21
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 78
        yield "</div>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["show_title_on_button", "configuration", "attributes", "title_prefix", "label", "title_attributes", "title_suffix", "cta_subtitle", "cta_heading_line1", "cta_heading_line2", "cta_heading", "content"]);        yield from [];
    }

    // line 21
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 22
        yield "    ";
        if ((array_key_exists("cta_subtitle", $context) &&  !Twig\Extension\CoreExtension::testEmpty(($context["cta_subtitle"] ?? null)))) {
            // line 23
            yield "      <span class=\"block-cta__badge inline-flex items-center rounded-full bg-primary-100 px-4 py-1.5 text-sm font-medium text-primary-700\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["cta_subtitle"] ?? null), "html", null, true);
            yield "</span>
    ";
        }
        // line 25
        yield "    ";
        if ((array_key_exists("cta_heading_line1", $context) &&  !Twig\Extension\CoreExtension::testEmpty(($context["cta_heading_line1"] ?? null)))) {
            // line 26
            yield "      <div class=\"block-cta__heading\">
        <span class=\"block-cta__heading-line1\">";
            // line 27
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["cta_heading_line1"] ?? null), "html", null, true);
            yield "</span>
        ";
            // line 28
            if ((array_key_exists("cta_heading_line2", $context) &&  !Twig\Extension\CoreExtension::testEmpty(($context["cta_heading_line2"] ?? null)))) {
                // line 29
                yield "          <span class=\"block-cta__heading-line2\">";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["cta_heading_line2"] ?? null), "html", null, true);
                yield "</span>
        ";
            }
            // line 31
            yield "      </div>
    ";
        } elseif ((        // line 32
array_key_exists("cta_heading", $context) &&  !Twig\Extension\CoreExtension::testEmpty(($context["cta_heading"] ?? null)))) {
            // line 33
            yield "      <p class=\"block-cta__heading block-cta__heading-single\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["cta_heading"] ?? null), "html", null, true);
            yield "</p>
    ";
        }
        // line 35
        yield "    <div class=\"block-cta__buttons flex flex-wrap items-center gap-2\">
      ";
        // line 36
        if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_whatsapp", [], "any", false, false, true, 36), 0, [], "any", false, false, true, 36))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 37
            yield "        ";
            $context["wa"] = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_whatsapp", [], "any", false, false, true, 37), 0, [], "any", false, false, true, 37);
            // line 38
            yield "        ";
            $context["wa_rel"] = ((CoreExtension::getAttribute($this->env, $this->source, (($_v0 = ($context["wa"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess && in_array($_v0::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v0["#options"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["wa"] ?? null), "#options", [], "array", false, false, true, 38)), "rel", [], "any", false, false, true, 38)) ? (CoreExtension::getAttribute($this->env, $this->source, (($_v1 = ($context["wa"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess && in_array($_v1::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v1["#options"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["wa"] ?? null), "#options", [], "array", false, false, true, 38)), "rel", [], "any", false, false, true, 38)) : ("nofollow noopener noreferrer"));
            // line 39
            yield "        <a href=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, (($_v2 = ($context["wa"] ?? null)) && is_array($_v2) || $_v2 instanceof ArrayAccess && in_array($_v2::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v2["#url"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["wa"] ?? null), "#url", [], "array", false, false, true, 39)), "toString", [], "method", false, false, true, 39), "html", null, true);
            yield "\"
           class=\"block-cta__btn block-cta__btn--green\"
           aria-label=\"";
            // line 41
            yield (((($_v3 = ($context["wa"] ?? null)) && is_array($_v3) || $_v3 instanceof ArrayAccess && in_array($_v3::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v3["#title"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["wa"] ?? null), "#title", [], "array", false, false, true, 41))) ? ($this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, (($_v4 = ($context["wa"] ?? null)) && is_array($_v4) || $_v4 instanceof ArrayAccess && in_array($_v4::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v4["#title"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["wa"] ?? null), "#title", [], "array", false, false, true, 41)), "html", null, true)) : (t("WhatsApp")));
            yield "\"
           ";
            // line 42
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (($_v5 = ($context["wa"] ?? null)) && is_array($_v5) || $_v5 instanceof ArrayAccess && in_array($_v5::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v5["#options"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["wa"] ?? null), "#options", [], "array", false, false, true, 42)), "target", [], "any", false, false, true, 42)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield "target=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, (($_v6 = ($context["wa"] ?? null)) && is_array($_v6) || $_v6 instanceof ArrayAccess && in_array($_v6::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v6["#options"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["wa"] ?? null), "#options", [], "array", false, false, true, 42)), "target", [], "any", false, false, true, 42), "html", null, true);
                yield "\"";
            }
            // line 43
            yield "           rel=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["wa_rel"] ?? null), "html", null, true);
            yield "\">
          <svg class=\"block-cta__icon\" fill=\"currentColor\" viewBox=\"0 0 24 24\" aria-hidden=\"true\">
            <path d=\"M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z\"/>
          </svg>
          ";
            // line 47
            if ((($tmp = ($context["show_title"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield "<span class=\"block-cta__text\">";
                yield (((($_v7 = ($context["wa"] ?? null)) && is_array($_v7) || $_v7 instanceof ArrayAccess && in_array($_v7::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v7["#title"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["wa"] ?? null), "#title", [], "array", false, false, true, 47))) ? ($this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, (($_v8 = ($context["wa"] ?? null)) && is_array($_v8) || $_v8 instanceof ArrayAccess && in_array($_v8::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v8["#title"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["wa"] ?? null), "#title", [], "array", false, false, true, 47)), "html", null, true)) : (t("WhatsApp")));
                yield "</span>";
            }
            // line 48
            yield "        </a>
      ";
        }
        // line 50
        yield "      ";
        if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_email_link", [], "any", false, false, true, 50), 0, [], "any", false, false, true, 50))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 51
            yield "        ";
            $context["em"] = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_email_link", [], "any", false, false, true, 51), 0, [], "any", false, false, true, 51);
            // line 52
            yield "        <a href=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, (($_v9 = ($context["em"] ?? null)) && is_array($_v9) || $_v9 instanceof ArrayAccess && in_array($_v9::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v9["#url"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["em"] ?? null), "#url", [], "array", false, false, true, 52)), "toString", [], "method", false, false, true, 52), "html", null, true);
            yield "\"
           class=\"block-cta__btn block-cta__btn--navy\"
           aria-label=\"";
            // line 54
            yield (((($_v10 = ($context["em"] ?? null)) && is_array($_v10) || $_v10 instanceof ArrayAccess && in_array($_v10::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v10["#title"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["em"] ?? null), "#title", [], "array", false, false, true, 54))) ? ($this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, (($_v11 = ($context["em"] ?? null)) && is_array($_v11) || $_v11 instanceof ArrayAccess && in_array($_v11::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v11["#title"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["em"] ?? null), "#title", [], "array", false, false, true, 54)), "html", null, true)) : (t("Email")));
            yield "\"
           ";
            // line 55
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (($_v12 = ($context["em"] ?? null)) && is_array($_v12) || $_v12 instanceof ArrayAccess && in_array($_v12::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v12["#options"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["em"] ?? null), "#options", [], "array", false, false, true, 55)), "target", [], "any", false, false, true, 55)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield "target=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, (($_v13 = ($context["em"] ?? null)) && is_array($_v13) || $_v13 instanceof ArrayAccess && in_array($_v13::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v13["#options"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["em"] ?? null), "#options", [], "array", false, false, true, 55)), "target", [], "any", false, false, true, 55), "html", null, true);
                yield "\"";
            }
            // line 56
            yield "           ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (($_v14 = ($context["em"] ?? null)) && is_array($_v14) || $_v14 instanceof ArrayAccess && in_array($_v14::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v14["#options"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["em"] ?? null), "#options", [], "array", false, false, true, 56)), "rel", [], "any", false, false, true, 56)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield "rel=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, (($_v15 = ($context["em"] ?? null)) && is_array($_v15) || $_v15 instanceof ArrayAccess && in_array($_v15::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v15["#options"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["em"] ?? null), "#options", [], "array", false, false, true, 56)), "rel", [], "any", false, false, true, 56), "html", null, true);
                yield "\"";
            }
            yield ">
          <svg class=\"block-cta__icon\" fill=\"currentColor\" viewBox=\"0 0 24 25\" aria-hidden=\"true\">
            <path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M14.92 3.19159C12.967 3.14252 11.033 3.14252 9.07999 3.19158L9.02182 3.19304C7.497 3.23133 6.27002 3.26214 5.2867 3.43339C4.2572 3.61268 3.42048 3.95656 2.71362 4.66611C2.00971 5.37269 1.66764 6.19738 1.49176 7.21019C1.32429 8.17456 1.29878 9.37159 1.26719 10.8544L1.26593 10.9132C1.24469 11.9095 1.24469 12.9001 1.26594 13.8964L1.26719 13.9551C1.29879 15.438 1.32429 16.635 1.49176 17.5994C1.66764 18.6122 2.00972 19.4369 2.71362 20.1435C3.42048 20.853 4.2572 21.1969 5.2867 21.3762C6.27001 21.5474 7.49697 21.5782 9.02177 21.6165L9.07999 21.618C11.033 21.6671 12.967 21.6671 14.92 21.618L14.9782 21.6165C16.503 21.5782 17.73 21.5474 18.7133 21.3762C19.7428 21.1969 20.5795 20.853 21.2864 20.1435C21.9903 19.4369 22.3324 18.6122 22.5082 17.5994C22.6757 16.635 22.7012 15.438 22.7328 13.9551L22.7341 13.8964C22.7553 12.9001 22.7553 11.9095 22.7341 10.9132L22.7328 10.8545C22.7012 9.37161 22.6757 8.17458 22.5082 7.2102C22.3324 6.1974 21.9903 5.37271 21.2864 4.66613C20.5795 3.95657 19.7428 3.61269 18.7133 3.4334C17.73 3.26215 16.503 3.23134 14.9782 3.19305L14.92 3.19159ZM9.11766 4.69111C11.0456 4.64267 12.9544 4.64268 14.8823 4.69112C16.479 4.73123 17.5947 4.76117 18.4559 4.91116C19.2835 5.05528 19.7994 5.29889 20.2237 5.72477C20.3977 5.89937 20.5405 6.08733 20.6582 6.30308L14.7173 9.66923C13.4621 10.3805 12.7003 10.6548 12.0001 10.6548C11.2999 10.6548 10.5381 10.3805 9.28285 9.66923L3.34181 6.30299C3.4595 6.08727 3.60237 5.89934 3.77629 5.72475C4.20055 5.29888 4.71652 5.05526 5.54405 4.91114C6.40529 4.76116 7.52099 4.73122 9.11766 4.69111ZM2.92102 7.78861C2.81754 8.58043 2.79468 9.58125 2.76559 10.9452C2.7448 11.9202 2.7448 12.8894 2.7656 13.8644C2.79877 15.4199 2.82385 16.5032 2.96964 17.3427C3.10923 18.1466 3.34907 18.656 3.77629 19.0848C4.20056 19.5107 4.71653 19.7543 5.54406 19.8984C6.4053 20.0484 7.521 20.0783 9.11767 20.1185C11.0456 20.1669 12.9544 20.1669 14.8823 20.1185C16.479 20.0783 17.5947 20.0484 18.4559 19.8984C19.2835 19.7543 19.7994 19.5107 20.2237 19.0848C20.6509 18.656 20.8908 18.1466 21.0304 17.3427C21.1762 16.5032 21.2012 15.4199 21.2344 13.8644C21.2552 12.8894 21.2552 11.9202 21.2344 10.9452C21.2053 9.58132 21.1825 8.58052 21.079 7.78872L15.4568 10.9743C14.1635 11.7071 13.1126 12.1548 12.0001 12.1548C10.8876 12.1548 9.83667 11.7071 8.54339 10.9743L2.92102 7.78861Z\"/>
          </svg>
          ";
            // line 60
            if ((($tmp = ($context["show_title"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield "<span class=\"block-cta__text\">";
                yield (((($_v16 = ($context["em"] ?? null)) && is_array($_v16) || $_v16 instanceof ArrayAccess && in_array($_v16::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v16["#title"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["em"] ?? null), "#title", [], "array", false, false, true, 60))) ? ($this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, (($_v17 = ($context["em"] ?? null)) && is_array($_v17) || $_v17 instanceof ArrayAccess && in_array($_v17::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v17["#title"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["em"] ?? null), "#title", [], "array", false, false, true, 60)), "html", null, true)) : (t("Email")));
                yield "</span>";
            }
            // line 61
            yield "        </a>
      ";
        }
        // line 63
        yield "      ";
        if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_phone", [], "any", false, false, true, 63), 0, [], "any", false, false, true, 63))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 64
            yield "        ";
            $context["ph"] = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_phone", [], "any", false, false, true, 64), 0, [], "any", false, false, true, 64);
            // line 65
            yield "        <a href=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, (($_v18 = ($context["ph"] ?? null)) && is_array($_v18) || $_v18 instanceof ArrayAccess && in_array($_v18::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v18["#url"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["ph"] ?? null), "#url", [], "array", false, false, true, 65)), "toString", [], "method", false, false, true, 65), "html", null, true);
            yield "\"
           class=\"block-cta__btn block-cta__btn--amber\"
           aria-label=\"";
            // line 67
            yield (((($_v19 = ($context["ph"] ?? null)) && is_array($_v19) || $_v19 instanceof ArrayAccess && in_array($_v19::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v19["#title"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["ph"] ?? null), "#title", [], "array", false, false, true, 67))) ? ($this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, (($_v20 = ($context["ph"] ?? null)) && is_array($_v20) || $_v20 instanceof ArrayAccess && in_array($_v20::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v20["#title"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["ph"] ?? null), "#title", [], "array", false, false, true, 67)), "html", null, true)) : (t("Call Now")));
            yield "\"
           ";
            // line 68
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (($_v21 = ($context["ph"] ?? null)) && is_array($_v21) || $_v21 instanceof ArrayAccess && in_array($_v21::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v21["#options"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["ph"] ?? null), "#options", [], "array", false, false, true, 68)), "target", [], "any", false, false, true, 68)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield "target=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, (($_v22 = ($context["ph"] ?? null)) && is_array($_v22) || $_v22 instanceof ArrayAccess && in_array($_v22::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v22["#options"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["ph"] ?? null), "#options", [], "array", false, false, true, 68)), "target", [], "any", false, false, true, 68), "html", null, true);
                yield "\"";
            }
            // line 69
            yield "           ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (($_v23 = ($context["ph"] ?? null)) && is_array($_v23) || $_v23 instanceof ArrayAccess && in_array($_v23::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v23["#options"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["ph"] ?? null), "#options", [], "array", false, false, true, 69)), "rel", [], "any", false, false, true, 69)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield "rel=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, (($_v24 = ($context["ph"] ?? null)) && is_array($_v24) || $_v24 instanceof ArrayAccess && in_array($_v24::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v24["#options"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["ph"] ?? null), "#options", [], "array", false, false, true, 69)), "rel", [], "any", false, false, true, 69), "html", null, true);
                yield "\"";
            }
            yield ">
          <svg class=\"block-cta__icon\" fill=\"currentColor\" viewBox=\"0 0 24 25\" aria-hidden=\"true\">
            <path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M5.31677 1.69111C5.88346 1.80823 6.33476 2.18897 6.61515 2.692L7.50836 4.29444C7.83737 4.88465 8.11424 5.38134 8.29505 5.81311C8.48686 6.27112 8.60078 6.72278 8.5487 7.22214C8.49663 7.72151 8.29197 8.13994 8.0098 8.54853C7.74379 8.93371 7.3704 9.36258 6.92669 9.87221L4.69884 12.4312C6.46568 15.3272 9.07461 17.9373 11.9736 19.706L14.5326 17.4781C15.0422 17.0344 15.4711 16.661 15.8563 16.395C16.2649 16.1128 16.6833 15.9082 17.1827 15.8561C17.682 15.804 18.1337 15.9179 18.5917 16.1098C19.0235 16.2906 19.5202 16.5674 20.1104 16.8965L21.7128 17.7897C22.2158 18.07 22.5966 18.5213 22.7137 19.088C22.832 19.6606 22.6575 20.2362 22.2719 20.7092C20.873 22.4256 18.6317 23.5184 16.2805 23.0441C14.8353 22.7526 13.4093 22.2669 11.6846 21.2777C8.21921 19.2904 5.11214 16.1816 3.12706 12.7202C2.13795 10.9955 1.65223 9.56954 1.36069 8.12428C0.886392 5.77306 1.97923 3.53178 3.69559 2.13287C4.16862 1.74733 4.74417 1.57276 5.31677 1.69111ZM13.3707 20.4784C14.5371 21.0532 15.5516 21.3668 16.5771 21.5737C18.2732 21.9159 19.9854 21.1403 21.1092 19.7615C21.2568 19.5805 21.2577 19.4541 21.2447 19.3916C21.2306 19.3232 21.1733 19.2062 20.9825 19.0999L19.416 18.2267C18.7803 17.8724 18.3572 17.6378 18.0123 17.4933C17.6849 17.3562 17.4971 17.3315 17.3382 17.348C17.1794 17.3646 17.0007 17.4276 16.7087 17.6293C16.401 17.8417 16.0354 18.1586 15.4865 18.6365L13.3707 20.4784ZM3.92636 11.0341L5.76835 8.91831C6.24618 8.36944 6.56306 8.00379 6.77553 7.69613C6.97721 7.4041 7.04023 7.2254 7.05679 7.06656C7.07336 6.90773 7.04857 6.71987 6.91148 6.39251C6.76705 6.04764 6.53243 5.62447 6.17812 4.98883L5.30494 3.42232C5.19856 3.23147 5.08158 3.1742 5.01317 3.16006C4.95066 3.14714 4.82429 3.14805 4.64326 3.2956C3.26448 4.41936 2.48894 6.13162 2.83107 7.82767C3.03795 8.85325 3.35164 9.86773 3.92636 11.0341Z\"/>
          </svg>
          ";
            // line 73
            if ((($tmp = ($context["show_title"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield "<span class=\"block-cta__text\">";
                yield (((($_v25 = ($context["ph"] ?? null)) && is_array($_v25) || $_v25 instanceof ArrayAccess && in_array($_v25::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v25["#title"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["ph"] ?? null), "#title", [], "array", false, false, true, 73))) ? ($this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, (($_v26 = ($context["ph"] ?? null)) && is_array($_v26) || $_v26 instanceof ArrayAccess && in_array($_v26::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v26["#title"] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["ph"] ?? null), "#title", [], "array", false, false, true, 73)), "html", null, true)) : (t("Call Now")));
                yield "</span>";
            }
            // line 74
            yield "        </a>
      ";
        }
        // line 76
        yield "    </div>
  ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/motaded_theme/templates/block/block--bundle--global_cta.html.twig";
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
        return array (  263 => 76,  259 => 74,  253 => 73,  241 => 69,  235 => 68,  231 => 67,  225 => 65,  222 => 64,  219 => 63,  215 => 61,  209 => 60,  197 => 56,  191 => 55,  187 => 54,  181 => 52,  178 => 51,  175 => 50,  171 => 48,  165 => 47,  157 => 43,  151 => 42,  147 => 41,  141 => 39,  138 => 38,  135 => 37,  133 => 36,  130 => 35,  124 => 33,  122 => 32,  119 => 31,  113 => 29,  111 => 28,  107 => 27,  104 => 26,  101 => 25,  95 => 23,  92 => 22,  85 => 21,  78 => 78,  76 => 21,  71 => 20,  63 => 18,  61 => 17,  57 => 16,  52 => 15,  50 => 14,  48 => 11,  47 => 8,  45 => 6,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/motaded_theme/templates/block/block--bundle--global_cta.html.twig", "/var/www/html/web/themes/custom/motaded_theme/templates/block/block--bundle--global_cta.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 6, "if" => 17, "block" => 21];
        static $filters = ["clean_class" => 11, "merge" => 14, "escape" => 15, "t" => 41];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if', 'block'],
                ['clean_class', 'merge', 'escape', 't'],
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
