#!/usr/bin/env python3
"""Build platform_import_modon_senaei.dataset.php from client Word files."""

from __future__ import annotations

import html
import json
import re
import textwrap
import zipfile
import xml.etree.ElementTree as ET
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DATA_DIR = ROOT / "web/modules/custom/motaded_custom/data"
NS = {"w": "http://schemas.openxmlformats.org/wordprocessingml/2006/main"}


def docx_paragraphs(path: Path) -> list[str]:
    with zipfile.ZipFile(path) as zf:
        root = ET.fromstring(zf.read("word/document.xml"))
    out: list[str] = []
    for p in root.findall(".//w:p", NS):
        parts: list[str] = []
        for t in p.findall(".//w:t", NS):
            if t.text:
                parts.append(t.text)
            if t.tail:
                parts.append(t.tail)
        line = "".join(parts).strip()
        if line:
            out.append(line)
    return out


def extract_seo(paras: list[str]) -> dict[str, str]:
    seo: dict[str, str] = {}
    for i, p in enumerate(paras):
        low = p.lower()
        if low in ("title tag (60 chars)", "title tag (60 char)"):
            seo["meta_title"] = paras[i + 1] if i + 1 < len(paras) else ""
        elif low.startswith("meta description"):
            seo["meta_description"] = paras[i + 1] if i + 1 < len(paras) else ""
        elif low in ("h1",):
            seo["h1"] = paras[i + 1] if i + 1 < len(paras) else ""
        elif low.startswith("primary target keyword"):
            seo["primary_keyword"] = paras[i + 1] if i + 1 < len(paras) else ""
        elif p.startswith("/platforms/") or p.startswith("/ar/platforms/"):
            seo["path_alias"] = p.replace("/ar", "", 1)
    if "keywords" not in seo:
        for i, p in enumerate(paras):
            if p.lower() in ("secondary keywords", "الكلمات الفرعية"):
                seo["keywords"] = paras[i + 1] if i + 1 < len(paras) else ""
                break
    return seo


def find_index(paras: list[str], labels: list[str], start: int = 0) -> int | None:
    label_set = {x.lower() for x in labels}
    for i in range(start, len(paras)):
        if paras[i].strip().lower() in label_set:
            return i
    return None


def slice_until(paras: list[str], start: int, stop_labels: list[str]) -> list[str]:
    stop = {x.lower() for x in stop_labels}
    chunk: list[str] = []
    for p in paras[start:]:
        if p.strip().lower() in stop:
            break
        chunk.append(p)
    return chunk


def paras_to_html(paras: list[str]) -> str:
    blocks: list[str] = []
    for p in paras:
        p = p.strip()
        if not p:
            continue
        if p.startswith("• "):
            blocks.append(f"<li>{html.escape(p[2:].strip())}</li>")
            continue
        if re.match(r"^\d+\.\s", p):
            heading = re.sub(r"^\d+\.\s*", "", p)
            blocks.append(f"<h3>{html.escape(heading)}</h3>")
            continue
        blocks.append(f"<p>{html.escape(p)}</p>")
    html_out = ""
    if blocks and blocks[0].startswith("<li>"):
        html_out += "<ul>" + "".join(x for x in blocks if x.startswith("<li>")) + "</ul>"
        rest = [x for x in blocks if not x.startswith("<li>")]
        html_out += "".join(rest)
    else:
        html_out = "".join(blocks)
    return html_out


def extract_faq(paras: list[str], lang: str) -> list[dict[str, str]]:
    header = "Frequently Asked Questions" if lang == "en" else "الأسئلة الشائعة"
    idx = find_index(paras, [header])
    if idx is None:
        return []
    stop = [
        "When Do You Need Specialized Help",
        "متى تحتاج لمساعدة متخصصة",
        "Information Verification Sources",
        "مصادر التحقق",
        "Schema Markup",
        "Internal Links",
    ]
    items: list[dict[str, str]] = []
    i = idx + 1
    while i < len(paras):
        p = paras[i]
        if any(p.startswith(s) for s in stop):
            break
        is_question = p.endswith("?") or p.endswith("؟")
        if is_question:
            q = p
            a = paras[i + 1] if i + 1 < len(paras) else ""
            if a and not a.endswith("?") and not a.endswith("؟") and not any(a.startswith(s) for s in stop):
                items.append({"question": q, "answer": a})
                i += 2
                continue
        i += 1
    return items[:12]


def build_help_from_services(paras: list[str], lang: str) -> list[dict]:
    start_label = "Key Services at Modon Authority" if "modon" in lang else (
        "Key Services on Senaei Platform" if lang == "en" else "الخدمات الرئيسية"
    )
    labels = [
        "Key Services at Modon Authority",
        "Key Services on Senaei Platform",
        "الخدمات الرئيسية في هيئة مدن",
        "الخدمات الرئيسية في منصة صناعي",
    ]
    idx = find_index(paras, labels)
    if idx is None:
        return []
    stop = [
        "Joint Financing Programs",
        "برامج التمويل المشتركة",
        "SADAD Biller Code",
        "رمز السداد",
        "Industrial Investment Costs",
        "تكاليف الاستثمار",
        "Complete Service Fees Schedule",
        "جدول الرسوم",
    ]
    chunk = slice_until(paras, idx + 1, stop + ["Frequently Asked Questions", "الأسئلة الشائعة"])
    items: list[dict] = []
    current_title = ""
    current_body: list[str] = []
    icons = ["briefcase", "document", "building", "globe", "list_check", "cog"]
    icon_i = 0

    def flush() -> None:
        nonlocal current_title, current_body, icon_i
        if current_title:
            items.append(
                {
                    "title": current_title,
                    "body": " ".join(current_body)[:500],
                    "icon": icons[icon_i % len(icons)],
                }
            )
            icon_i += 1
        current_title = ""
        current_body = []

    for p in chunk:
        if re.match(r"^\d+\.\s", p):
            flush()
            current_title = re.sub(r"^\d+\.\s*", "", p)
        elif current_title:
            current_body.append(p)
    flush()
    return items[:5]


def build_why_json(paras: list[str], lang: str) -> list[dict]:
    labels = [
        "Modon's Role in Saudi Vision 2030",
        "مدن ودورها في رؤية السعودية 2030",
        "Overview",
        "نظرة عامة",
    ]
    idx = find_index(paras, labels)
    if idx is None:
        return []
    chunk = paras[idx + 1 : idx + 8]
    icons = ["target", "chart", "globe", "users"]
    items = []
    for i, p in enumerate(chunk):
        if p.startswith(("Full name", "Abbreviation", "Establishment", "Owner", "الاسم", "الاختصار", "الجهة")):
            continue
        if len(p) > 30:
            items.append({"title": p.split(".")[0][:80], "body": p, "icon": icons[i % len(icons)]})
        if len(items) >= 3:
            break
    return items


def build_steps(paras: list[str], lang: str) -> list[dict]:
    labels = [
        "How to Register on the Shareek System",
        "كيفية التسجيل في نظام شريك",
        "How to Register on Senaei Platform",
        "كيفية التسجيل في منصة صناعي",
        "Registration Steps on Senaei",
        "خطوات التسجيل في منصة صناعي",
    ]
    idx = find_index(paras, labels)
    if idx is None:
        return []
    chunk = slice_until(
        paras,
        idx + 1,
        ["Frequently Asked Questions", "الأسئلة الشائعة", "Requirements", "المتطلبات"],
    )
    steps = []
    current = {"title": "", "body": ""}
    for p in chunk:
        if re.match(r"^\d+\.\s", p):
            if current["title"]:
                steps.append(current)
            current = {"title": re.sub(r"^\d+\.\s*", "", p), "body": ""}
        elif current["title"]:
            current["body"] += (" " if current["body"] else "") + p
    if current["title"]:
        steps.append(current)
    return steps[:5]


def build_requirements(paras: list[str], lang: str) -> list[dict]:
    labels = ["Requirements", "المتطلبات", "Prerequisites"]
    idx = find_index(paras, labels)
    if idx is None:
        return []
    chunk = slice_until(paras, idx + 1, ["Frequently Asked Questions", "الأسئلة الشائعة", "Partners", "الشركاء"])
    reqs = []
    for p in chunk:
        p = p.strip()
        if p.startswith("•"):
            p = p[1:].strip()
        if len(p) > 20:
            reqs.append({"content": p})
        if len(reqs) >= 6:
            break
    return reqs


def build_fees_table_html(paras: list[str], lang: str) -> str:
    labels = [
        "Complete Service Fees Schedule",
        "جدول الرسوم الكامل للخدمات",
    ]
    idx = find_index(paras, labels)
    if idx is None:
        return ""
    rows = []
    i = idx + 1
    # Skip header row(s).
    while i < len(paras) and paras[i].lower() in (
        "service type",
        "fee value",
        "notes",
        "نوع الخدمة",
        "قيمة الرسوم",
        "ملاحظات",
    ):
        i += 1
    while i + 2 < len(paras):
        service = paras[i]
        fee = paras[i + 1]
        note = paras[i + 2]
        if any(
            service.startswith(x)
            for x in (
                "Frequently",
                "When Do",
                "Information",
                "Schema",
                "متى تحتاج",
                "مصادر التحقق",
            )
        ):
            break
        if service.lower() in ("service type", "نوع الخدمة"):
            i += 1
            continue
        rows.append((service, fee, note))
        i += 3
        if len(rows) >= 9:
            break
    if not rows:
        return ""
    th = ("Service", "Fee", "Notes") if lang == "en" else ("الخدمة", "الرسوم", "ملاحظات")
    html_rows = "".join(
        f"<tr><td>{html.escape(a)}</td><td>{html.escape(b)}</td><td>{html.escape(c)}</td></tr>"
        for a, b, c in rows
    )
    return (
        f'<h2>{"Official service fees" if lang == "en" else "جدول الرسوم الرسمية"}</h2>'
        f"<table><thead><tr>"
        f"<th>{th[0]}</th><th>{th[1]}</th><th>{th[2]}</th>"
        f"</tr></thead><tbody>{html_rows}</tbody></table>"
    )


def build_financing_section_html(paras: list[str], lang: str) -> str:
    labels = ["Joint Financing Programs with SIDF", "برامج التمويل المشتركة مع SIDF"]
    idx = find_index(paras, labels)
    if idx is None:
        return ""
    chunk = slice_until(
        paras,
        idx,
        ["Industrial Investment Costs", "تكاليف الاستثمار", "SADAD", "Complete Service Fees"],
    )
    return paras_to_html(chunk)


def build_body(paras: list[str], lang: str, slug: str) -> tuple[str, str]:
    seo = extract_seo(paras)
    overview_idx = find_index(paras, ["Overview", "نظرة عامة"])
    intro = ""
    h1 = seo.get("h1", "")
    if h1 and overview_idx is not None:
        skip_labels = {
            "hreflang", "developer note", "link to", "ملاحظة للمبرمج", "url (do not change)",
            "url", "seo data for the page (developer instructions for drupal update)",
            "بيانات seo للصفحة (تعليمات للمبرمج لتحديثها في drupal)",
        }
        for i, p in enumerate(paras):
            if p != h1:
                continue
            if i + 1 >= len(paras) or i + 1 >= overview_idx:
                continue
            candidate = paras[i + 1].strip()
            if candidate.lower() in skip_labels or candidate.startswith("Link to /"):
                continue
            if len(candidate) > 80:
                intro = candidate
                break

    overview_chunk = []
    if overview_idx is not None:
        overview_chunk = slice_until(
            paras,
            overview_idx + 1,
            [
                "Modon's Role in Saudi Vision 2030",
                "مدن ودورها في رؤية السعودية 2030",
                "Key Services",
                "الخدمات الرئيسية",
            ],
        )

    parts = []
    if intro:
        parts.append(f"<p>{html.escape(intro)}</p>")
    if overview_chunk:
        parts.append(f"<h2>{'Overview' if lang == 'en' else 'نظرة عامة'}</h2>")
        parts.append(paras_to_html(overview_chunk[:12]))

    if slug == "modon":
        fin = build_financing_section_html(paras, lang)
        if fin:
            parts.append(f"<h2>{'Land and Loan / Factory and Loan programs' if lang == 'en' else 'برامج أرض وقرض / مصنع وقرض'}</h2>")
            parts.append(fin)
    if slug == "senaei":
        fees = build_fees_table_html(paras, lang)
        if fees:
            parts.append(fees)

    body = "".join(parts)
    summary = intro or (overview_chunk[0] if overview_chunk else "")
    return body, summary[:300]


def platform_row(slug: str, lang: str, paras: list[str]) -> dict:
    seo = extract_seo(paras)
    body, summary = build_body(paras, lang, slug)
    faq = extract_faq(paras, lang)
    help_json = build_help_from_services(paras, lang)
    why_json = build_why_json(paras, lang)
    steps_json = build_steps(paras, lang)
    req_json = build_requirements(paras, lang)

    if slug == "modon":
        en_title = "Modon"
        ar_title = "مدن (Modon)"
        subtitle_en = "Saudi Authority for Industrial Cities and Technology Zones"
        subtitle_ar = "الهيئة السعودية للمدن الصناعية ومناطق التقنية"
        short_en = "Official guide to Modon (modon.gov.sa): industrial land allocation, Shareek e-services, SIDF Land and Loan programs across 35 industrial cities."
        short_ar = "دليل هيئة مدن modon.gov.sa: تخصيص الأراضي الصناعية، نظام شريك، وبرامج أرض وقرض ومصنع وقرض في 35 مدينة صناعية."
        external = "https://modon.gov.sa/en/Pages/default.aspx"
        external_title = "modon.gov.sa"
        source_text_en = "Saudi Authority for Industrial Cities and Technology Zones"
        source_text_ar = "الهيئة السعودية للمدن الصناعية ومناطق التقنية"
        related = "MISA (Ministry of Investment Saudi Arabia)|Senaei|Saudi Business Center (Meras)"
        cta_uri = "https://modon.gov.sa/ar/Eservices/Pages/Shareek.aspx"
        cta_title = "Shareek system"
        cta_text_en = "Access Shareek e-services"
        cta_text_ar = "الدخول إلى نظام شريك"
        keywords_en = "Modon, MODON, industrial land Saudi Arabia, Shareek, Land and Loan, Factory and Loan, industrial cities KSA"
        keywords_ar = "مدن, Modon, أرض صناعية, نظام شريك, أرض وقرض, مصنع وقرض, مدن صناعية"
    else:
        en_title = "Senaei"
        ar_title = "صناعي (Senaei)"
        subtitle_en = "Ministry of Industry and Mineral Resources — Industrial Gateway"
        subtitle_ar = "وزارة الصناعة والثروة المعدنية — البوابة الصناعية"
        short_en = "Official guide to Senaei (industry.sa): industrial licensing, SADAD biller 0029002000, customs exemption, certificates of origin, and Qiwa integrations."
        short_ar = "دليل منصة صناعي industry.sa: الترخيص الصناعي، رمز سداد 0029002000، الإعفاء الجمركي، شهادات المنشأ، وتكامل قوى."
        external = "https://industry.sa/"
        external_title = "industry.sa"
        source_text_en = "Ministry of Industry and Mineral Resources"
        source_text_ar = "وزارة الصناعة والثروة المعدنية"
        related = "Modon|MISA (Ministry of Investment Saudi Arabia)|Qiwa"
        cta_uri = "https://app.industry.sa/"
        cta_title = "Senaei login"
        cta_text_en = "Open Senaei platform"
        cta_text_ar = "فتح منصة صناعي"
        keywords_en = "Senaei, industry.sa, industrial license Saudi Arabia, SADAD 0029002000, customs exemption, certificate of origin"
        keywords_ar = "صناعي, Senaei, ترخيص صناعي, سداد 0029002000, إعفاء جمركي, شهادة منشأ"

    title = en_title if lang == "en" else ar_title
    if lang == "en":
        meta_title = seo.get("meta_title") or f"{title}: Saudi platform guide 2026 | Motaded"
        meta_desc = seo.get("meta_description") or short_en
        meta_kw = seo.get("keywords") or keywords_en
    else:
        meta_title = seo.get("meta_title") or f"{title}: دليل المنصة 2026 | متعدد"
        meta_desc = seo.get("meta_description") or short_ar
        meta_kw = seo.get("keywords") or keywords_ar

    resources = [
        {"title": external_title, "uri": external, "link_title": external_title, "icon": "globe"},
    ]
    if slug == "modon":
        resources.append(
            {
                "title": "Shareek",
                "uri": "https://modon.gov.sa/ar/Eservices/Pages/Shareek.aspx",
                "link_title": "Shareek",
                "icon": "document",
            }
        )
    else:
        resources.append(
            {
                "title": "MIM",
                "uri": "https://mim.gov.sa/",
                "link_title": "mim.gov.sa",
                "icon": "info",
            }
        )

    row = {
        "title": title,
        "field_subtitle": subtitle_en if lang == "en" else subtitle_ar,
        "field_short_description": short_en if lang == "en" else short_ar,
        "field_sector_name": "Manufacturing",
        "field_category_name": "Licensing",
        "field_region_name": "Saudi Arabia",
        "field_related_platforms_titles": related,
        "field_external_link_uri": external,
        "field_external_link_title": external_title,
        "field_cta_link_uri": cta_uri,
        "field_cta_link_title": cta_title,
        "field_cta_text": cta_text_en if lang == "en" else cta_text_ar,
        "field_source_link_uri": external,
        "field_source_link_title": external_title,
        "field_source_text": source_text_en if lang == "en" else source_text_ar,
        "meta_title": meta_title,
        "meta_description": meta_desc,
        "meta_keywords": meta_kw,
        "body_summary": summary,
        "body": body,
        "why_json": why_json,
        "help_json": help_json,
        "steps_json": steps_json,
        "req_json": req_json,
        "resources_json": resources,
        "faq_json": faq,
        "path_alias": seo.get("path_alias") or f"/platforms/{slug}",
        "langcode": lang,
    }
    if lang == "ar":
        row["translation_source_title"] = en_title
    return row


def php_export_dataset(rows: list[dict]) -> str:
    lines = [
        "<?php",
        "",
        "/**",
        " * @file",
        " * Modon + Senaei platform import rows (generated from client Word files).",
        " *",
        " * Regenerate:",
        " *   python3 scripts/build_platform_modon_senaei_dataset.py",
        " * Import:",
        " *   php scripts/rebuild_platform_modon_senaei_csv.php",
        " *   ddev drush mpic platform_import_modon_senaei_ready.csv",
        " *   ddev drush mpic platform_import_modon_senaei_ar_ready.csv",
        " */",
        "",
        "declare(strict_types=1);",
        "",
        "require_once __DIR__ . '/platform_import.i18n.php';",
        "",
        "return [",
    ]
    for row in rows:
        if row["langcode"] != "en":
            continue
        lines.append("  _motaded_pi_row(" + php_array(row) + "),")
    lines.append("];")
    lines.append("")
    return "\n".join(lines)


def php_export_ar_overrides(en_rows: list[dict], ar_rows: list[dict]) -> str:
    ar_by_slug = {r["path_alias"].split("/")[-1]: r for r in ar_rows}
    lines = [
        "<?php",
        "",
        "/**",
        " * @file",
        " * Arabic overrides for platform_import_modon_senaei.dataset.php.",
        " */",
        "",
        "declare(strict_types=1);",
        "",
        "$j = static function (array $a): string {",
        "  return json_encode(array_values($a), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';",
        "};",
        "",
        "return [",
    ]
    for en in en_rows:
        if en["langcode"] != "en":
            continue
        slug = en["path_alias"].split("/")[-1]
        ar = ar_by_slug.get(slug)
        if not ar:
            continue
        lines.append(f"  '{en['title']}' => [")
        for key in [
            "title",
            "field_subtitle",
            "field_short_description",
            "field_cta_text",
            "field_source_text",
            "body_summary",
            "body",
        ]:
            val = ar.get(key, "")
            lines.append(f"    '{key}' => " + php_str(val) + ",")
        meta = {
            "title": ar["meta_title"],
            "description": ar["meta_description"],
            "keywords": ar["meta_keywords"],
        }
        lines.append("    'field_meta_tags_json' => " + php_str(json.dumps(meta, ensure_ascii=False)) + ",")
        for json_key, src_key in [
            ("field_why_matters_json", "why_json"),
            ("field_how_we_help_json", "help_json"),
            ("field_process_steps_json", "steps_json"),
            ("field_requirements_json", "req_json"),
            ("field_resources_json", "resources_json"),
            ("field_faq_json", "faq_json"),
        ]:
            lines.append(f"    '{json_key}' => $j(" + php_inline_array(ar[src_key]) + "),")
        lines.append("  ],")
    lines.append("];")
    lines.append("")
    return "\n".join(lines)


def php_str(s: str) -> str:
    return "'" + s.replace("\\", "\\\\").replace("'", "\\'") + "'"


def php_inline_array(items: list) -> str:
    if not items:
        return "[]"
    parts = ["["]
    for item in items:
        if isinstance(item, dict):
            inner = ", ".join(f"'{k}' => " + php_str(str(v)) for k, v in item.items())
            parts.append(f"  [{inner}],")
        else:
            parts.append("  " + php_str(str(item)) + ",")
    parts.append("]")
    return "\n".join(parts)


def php_array(row: dict) -> str:
    simple_keys = [
        "title",
        "field_subtitle",
        "field_short_description",
        "field_sector_name",
        "field_category_name",
        "field_region_name",
        "field_related_platforms_titles",
        "field_external_link_uri",
        "field_external_link_title",
        "field_cta_link_uri",
        "field_cta_link_title",
        "field_cta_text",
        "field_source_link_uri",
        "field_source_link_title",
        "field_source_text",
        "meta_title",
        "meta_description",
        "meta_keywords",
        "body_summary",
        "body",
        "path_alias",
    ]
    parts = ["["]
    for key in simple_keys:
        parts.append(f"    '{key}' => " + php_str(str(row.get(key, ""))) + ",")
    for key in ["why_json", "help_json", "steps_json", "req_json", "resources_json", "faq_json"]:
        parts.append(f"    '{key}' => " + php_inline_array(row.get(key, [])) + ",")
    parts.append("  ]")
    return "\n".join(parts)


def main() -> None:
    configs = [
        ("modon", "Modon platform/26_Modon_Platform_EN.docx", "Modon platform/25_Modon_Platform_AR.docx"),
        ("senaei", "Senaei platform/28_Senaei_Platform_EN.docx", "Senaei platform/27_Senaei_Platform_AR.docx"),
    ]
    en_rows: list[dict] = []
    ar_rows: list[dict] = []
    for slug, en_path, ar_path in configs:
        en_paras = docx_paragraphs(ROOT / en_path)
        ar_paras = docx_paragraphs(ROOT / ar_path)
        en_row = platform_row(slug, "en", en_paras)
        ar_row = platform_row(slug, "ar", ar_paras)
        en_rows.append(en_row)
        ar_rows.append(ar_row)
        print(f"{slug}: EN FAQ={len(en_row['faq_json'])}, help={len(en_row['help_json'])}, body={len(en_row['body'])} chars")
        print(f"{slug}: AR FAQ={len(ar_row['faq_json'])}, help={len(ar_row['help_json'])}, body={len(ar_row['body'])} chars")

    dataset_path = DATA_DIR / "platform_import_modon_senaei.dataset.php"
    overrides_path = DATA_DIR / "platform_import_modon_senaei_ar.overrides.php"
    dataset_path.write_text(php_export_dataset(en_rows), encoding="utf-8")
    overrides_path.write_text(php_export_ar_overrides(en_rows, ar_rows), encoding="utf-8")
    print(f"Wrote {dataset_path}")
    print(f"Wrote {overrides_path}")


if __name__ == "__main__":
    main()
