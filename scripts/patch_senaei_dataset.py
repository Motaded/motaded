#!/usr/bin/env python3
"""Patch Senaei EN/AR platform import datasets from client Word files (Modon-style layout)."""

from __future__ import annotations

import html
import json
import re
import textwrap
import zipfile
import xml.etree.ElementTree as ET
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
EN_DOCX = ROOT / "Senaei platform/28_Senaei_Platform_EN.docx"
AR_DOCX = ROOT / "Senaei platform/27_Senaei_Platform_AR.docx"
DATASET = ROOT / "web/modules/custom/motaded_custom/data/platform_import_modon_senaei.dataset.php"
AR_OVERRIDES = ROOT / "web/modules/custom/motaded_custom/data/platform_import_modon_senaei_ar.overrides.php"
MODON_EN_TITLE = (
    "Modon Authority: The Practical Guide to Industrial Investment in Saudi Arabia 2026"
)
MODON_AR_TITLE = (
    "هيئة مدن Modon: الدليل العملي للاستثمار الصناعي في السعودية 2026"
)
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


def php_quote(value: str) -> str:
    return "'" + value.replace("\\", "\\\\").replace("'", "\\'") + "'"


def php_array(items: list[dict], indent: str = "  ") -> str:
    if not items:
        return "[]"
    lines = ["["]
    for item in items:
        parts = [f"'{k}' => {php_quote(str(v))}" for k, v in item.items()]
        lines.append(indent + "[" + ", ".join(parts) + "],")
    lines.append("]")
    return "\n".join(lines)


def php_row(row: dict) -> str:
    keys_order = [
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
        "why_json",
        "help_json",
        "steps_json",
        "req_json",
        "resources_json",
        "faq_json",
    ]
    json_fields = {
        "why_json",
        "help_json",
        "steps_json",
        "req_json",
        "resources_json",
        "faq_json",
    }
    lines = ["  _motaded_pi_row(["]
    for key in keys_order:
        val = row.get(key, "" if key not in json_fields else [])
        if key in json_fields:
            lines.append(f"    '{key}' => {php_array(val, '  ')},")
        else:
            lines.append(f"    '{key}' => {php_quote(str(val))},")
    lines.append("  ]),")
    return "\n".join(lines)


def esc_p(text: str) -> str:
    return html.escape(text, quote=False)


def paras_to_ul(paras: list[str]) -> str:
    return "<ul>" + "".join(f"<li>{esc_p(p)}</li>" for p in paras) + "</ul>"


def paras_to_ol(paras: list[str]) -> str:
    return "<ol>" + "".join(f"<li>{esc_p(p)}</li>" for p in paras) + "</ol>"


def fee_table_html(p: list[str], start: int, rows: list[tuple[int, int, int]], lang: str) -> str:
    th = ("Service", "Fee", "Notes") if lang == "en" else ("الخدمة", "الرسوم", "ملاحظات")
    body_rows = "".join(
        f"<tr><td>{esc_p(p[a])}</td><td>{esc_p(p[b])}</td><td>{esc_p(p[c])}</td></tr>"
        for a, b, c in rows
    )
    return (
        f"<h3><strong>{esc_p(p[start])}</strong></h3>"
        f'<table class="table" cellspacing="0" cellpadding="0"><tbody>'
        f"<tr><td><strong>{th[0]}</strong></td><td><strong>{th[1]}</strong></td>"
        f"<td><strong>{th[2]}</strong></td></tr>{body_rows}</tbody></table>"
    )


def integration_table_html(
    p: list[str], heading_idx: int, intro_idx: int, pairs: list[tuple[int, int]], lang: str
) -> str:
    h_entity = "Entity / Platform" if lang == "en" else "الجهة / المنصة"
    h_type = "Integration Type" if lang == "en" else "نوع التكامل"
    rows = "".join(
        f"<tr><td>{esc_p(p[a])}</td><td>{esc_p(p[b])}</td></tr>" for a, b in pairs
    )
    return (
        f"<h3><strong>{esc_p(p[heading_idx])}</strong></h3>"
        f"<p>{esc_p(p[intro_idx])}</p>"
        f'<table class="table" cellspacing="0" cellpadding="0"><tbody>'
        f"<tr><td><strong>{h_entity}</strong></td><td><strong>{h_type}</strong></td></tr>"
        f"{rows}</tbody></table>"
    )


def build_en_body(p: list[str]) -> str:
    parts: list[str] = []
    parts.append(f"<p>{esc_p(p[18])}</p>")
    parts.append(f"<p>{esc_p(p[20])}&nbsp;</p>")
    parts.append(paras_to_ul(p[21:28]))
    parts.append(f"<h3><strong>{esc_p(p[56])}</strong></h3>")
    parts.append(f"<p>{esc_p(p[57])}</p>")
    parts.append(f"<p><strong>{esc_p(p[58])}</strong></p>")
    for note in p[59:62]:
        bullet = note[2:].strip() if note.startswith("•") else note
        parts.append(f"<p>• {esc_p(bullet)}</p>")
    fee_rows = [(i, i + 1, i + 2) for i in range(67, 94, 3)]
    parts.append(fee_table_html(p, 63, fee_rows, "en"))
    parts.append(f"<p><strong>{esc_p(p[94])}</strong></p>")
    for note in p[95:100]:
        bullet = note[2:].strip() if note.startswith("•") else note
        parts.append(f"<p>• {esc_p(bullet)}</p>")
    parts.append(f"<h3><strong>{esc_p(p[100])}</strong></h3>")
    parts.append(f"<p><strong>{esc_p(p[101])}</strong></p>")
    parts.append(paras_to_ol(p[102:109]))
    parts.append(f"<p><strong>{esc_p(p[109])}</strong></p>")
    parts.append(paras_to_ol(p[110:116]))
    int_pairs = [(i, i + 1) for i in range(141, 160, 2)]
    parts.append(integration_table_html(p, 137, 138, int_pairs, "en"))
    parts.append(f"<p><strong>{esc_p(p[161])}</strong></p>")
    parts.append(f"<p>{esc_p(p[162])}</p>")
    parts.append(f"<h3><strong>{esc_p(p[163])}</strong></h3>")
    parts.append(f"<p>{esc_p(p[164])}</p>")
    parts.append(paras_to_ul(p[165:176]))
    parts.append(f"<h3><strong>{esc_p(p[201])}</strong></h3>")
    parts.append(f"<p>{esc_p(p[202])}</p>")
    parts.append(f"<p>{esc_p(p[203])}</p>")
    parts.append(paras_to_ul(p[204:213]))
    parts.append(f"<h3><strong>{esc_p(p[213])}</strong></h3>")
    parts.append(f"<p>{esc_p(p[214])}</p>")
    return "".join(parts)


def build_ar_body(p: list[str]) -> str:
    parts: list[str] = []
    parts.append(f"<p>{esc_p(p[16])}</p>")
    parts.append(f"<p>{esc_p(p[18])}&nbsp;</p>")
    parts.append(paras_to_ul(p[19:26]))
    parts.append(f"<h3><strong>{esc_p(p[54])}</strong></h3>")
    parts.append(f"<p>{esc_p(p[55])}</p>")
    parts.append(f"<p><strong>{esc_p(p[56])}</strong></p>")
    for note in p[57:61]:
        bullet = note[2:].strip() if note.startswith("•") else note
        parts.append(f"<p>• {esc_p(bullet)}</p>")
    fee_rows = [(i, i + 1, i + 2) for i in range(65, 92, 3)]
    parts.append(fee_table_html(p, 61, fee_rows, "ar"))
    parts.append(f"<p><strong>{esc_p(p[92])}</strong></p>")
    for note in p[93:98]:
        bullet = note[2:].strip() if note.startswith("•") else note
        parts.append(f"<p>• {esc_p(bullet)}</p>")
    parts.append(f"<h3><strong>{esc_p(p[98])}</strong></h3>")
    parts.append(f"<p><strong>{esc_p(p[99])}</strong></p>")
    parts.append(paras_to_ol(p[100:107]))
    parts.append(f"<p><strong>{esc_p(p[107])}</strong></p>")
    parts.append(paras_to_ol(p[108:115]))
    int_pairs = [(i, i + 1) for i in range(139, 158, 2)]
    parts.append(integration_table_html(p, 135, 136, int_pairs, "ar"))
    parts.append(f"<p><strong>{esc_p(p[159])}</strong></p>")
    parts.append(f"<p>{esc_p(p[160])}</p>")
    parts.append(f"<h3><strong>{esc_p(p[161])}</strong></h3>")
    parts.append(f"<p>{esc_p(p[162])}</p>")
    parts.append(paras_to_ul(p[163:174]))
    parts.append(f"<h3><strong>{esc_p(p[199])}</strong></h3>")
    parts.append(f"<p>{esc_p(p[200])}</p>")
    parts.append(f"<p>{esc_p(p[201])}</p>")
    parts.append(paras_to_ul(p[202:211]))
    parts.append(f"<h3><strong>{esc_p(p[211])}</strong></h3>")
    parts.append(f"<p>{esc_p(p[212])}</p>")
    return "".join(parts)


def extract_services(p: list[str], specs: list[tuple[str, int, int]]) -> list[dict]:
    items: list[dict] = []
    for title, start, end in specs:
        clean = re.sub(r"^\d+\.\s*", "", title)
        intro = p[start]
        bullets = p[start + 1 : end + 1] if end > start else []
        if re.match(r"^\d+\.\s", intro):
            bullets = p[start : end + 1]
            intro = ""
        if bullets and intro:
            body = f"<p>{esc_p(intro)}</p>{paras_to_ul(bullets)}"
        elif bullets:
            body = paras_to_ul(bullets)
        else:
            body = esc_p(intro)
        items.append({"title": clean, "body": body, "icon": ""})
    return items


EN_SERVICES = [
    ("1. Industrial Licensing Services", 29, 36),
    ("2. Exemptions and Benefits Services", 37, 41),
    ("3. Labor and Establishment Services", 42, 46),
    ("4. Certificates of Origin", 47, 50),
    ("5. Integrations with Other Entities from Within the Platform", 51, 55),
]

AR_SERVICES = [
    ("1. خدمات الترخيص الصناعي", 28, 34),
    ("2. خدمات الإعفاءات والمزايا", 35, 39),
    ("3. خدمات العمالة والمنشأة", 40, 44),
    ("4. شهادات المنشأ", 45, 48),
    ("5. التكاملات مع جهات أخرى من داخل المنصة", 49, 53),
]


def extract_requirements(p: list[str], blocks: list[tuple[str, int, int]]) -> list[dict]:
    return [
        {
            "content": heading
            + "\n".join(f"- {p[i]}" for i in range(start, end + 1))
            + "\n"
        }
        for heading, start, end in blocks
    ]


EN_REQ = [
    ("For the Industrial Investor:\n", 119, 124),
    ("For the Factory:\n", 126, 130),
    ("For Foreign Companies:\n", 132, 136),
]

AR_REQ = [
    ("للمستثمر الصناعي:\n", 117, 122),
    ("للمصنع:\n", 124, 128),
    ("للشركات الأجنبية:\n", 130, 134),
]


def extract_faq(p: list[str], start_q: int, end_q: int) -> list[dict]:
    faq: list[dict] = []
    i = start_q
    while i + 1 <= end_q:
        faq.append({"question": p[i], "answer": f"<p>{esc_p(p[i + 1])}</p>"})
        i += 2
    return faq


RESOURCES = [
    {
        "title": "Official Senaei platform",
        "uri": "https://industry.sa",
        "link_title": "",
        "icon": "globe",
    },
    {
        "title": "Senaei login",
        "uri": "https://app.industry.sa",
        "link_title": "",
        "icon": "globe",
    },
    {
        "title": "Ministry of Industry and Mineral Resources",
        "uri": "https://mim.gov.sa",
        "link_title": "",
        "icon": "globe",
    },
    {
        "title": "Ministry page on the National Unified Platform",
        "uri": "https://my.gov.sa/ar/agencies/17651",
        "link_title": "",
        "icon": "globe",
    },
]

RESOURCES_AR = [
    {"title": "منصة صناعي الرسمية", "uri": "https://industry.sa", "link_title": "", "icon": "globe"},
    {"title": "رابط الدخول لمنصة صناعي", "uri": "https://app.industry.sa", "link_title": "", "icon": "globe"},
    {"title": "وزارة الصناعة والثروة المعدنية", "uri": "https://mim.gov.sa", "link_title": "", "icon": "globe"},
    {
        "title": "صفحة الوزارة في المنصة الوطنية الموحدة",
        "uri": "https://my.gov.sa/ar/agencies/17651",
        "link_title": "",
        "icon": "globe",
    },
]


def build_en_row(p: list[str]) -> dict:
    return {
        "title": p[12],
        "field_subtitle": "",
        "field_short_description": p[20],
        "field_sector_name": "Manufacturing",
        "field_category_name": "Licensing",
        "field_region_name": "Saudi Arabia",
        "field_related_platforms_titles": (
            f"{MODON_EN_TITLE}|MISA (Ministry of Investment Saudi Arabia)|Qiwa Platform"
        ),
        "field_external_link_uri": "https://industry.sa/",
        "field_external_link_title": "industry.sa",
        "field_cta_link_uri": "https://app.industry.sa/",
        "field_cta_link_title": "Senaei login",
        "field_cta_text": "Open Senaei platform",
        "field_source_link_uri": "",
        "field_source_link_title": "",
        "field_source_text": "",
        "meta_title": p[8],
        "meta_description": p[10],
        "meta_keywords": p[6],
        "body_summary": p[18][:600],
        "body": build_en_body(p),
        "path_alias": "/platforms/senaei",
        "why_json": extract_services(p, EN_SERVICES),
        "help_json": [],
        "steps_json": [],
        "req_json": extract_requirements(p, EN_REQ),
        "resources_json": RESOURCES,
        "faq_json": extract_faq(p, 177, 200),
    }


def build_ar_overrides(p: list[str], en_title: str) -> dict:
    return {
        "_en_title_key": en_title,
        "title": p[12],
        "field_subtitle": "",
        "field_short_description": p[18],
        "field_cta_text": "فتح منصة صناعي",
        "field_source_text": "",
        "body_summary": p[16][:300],
        "body": build_ar_body(p),
        "field_meta_tags_json": json.dumps(
            {"title": p[8], "description": p[10], "keywords": p[6]},
            ensure_ascii=False,
            separators=(", ", ": "),
        ),
        "field_why_matters_json": json.dumps(
            extract_services(p, AR_SERVICES), ensure_ascii=False, separators=(", ", ": ")
        ),
        "field_how_we_help_json": "[]",
        "field_process_steps_json": "[]",
        "field_requirements_json": json.dumps(
            extract_requirements(p, AR_REQ), ensure_ascii=False, separators=(", ", ": ")
        ),
        "field_resources_json": json.dumps(
            RESOURCES_AR, ensure_ascii=False, separators=(", ", ": ")
        ),
        "field_faq_json": json.dumps(
            extract_faq(p, 175, 198), ensure_ascii=False, separators=(", ", ": ")
        ),
        "field_related_platforms_titles": (
            f"{MODON_AR_TITLE}|وزارة الاستثمار السعودية (ميسا)|منصة قوى"
        ),
    }


def extract_modon_block(path: Path) -> str:
    text = path.read_text(encoding="utf-8")
    start = text.find("  _motaded_pi_row([")
    end = text.find("_motaded_pi_row([", start + 5)
    if end == -1:
        end = text.rfind("]);")
        return text[start:end].rstrip().removesuffix(",") + ","
    return text[start:end].rstrip().removesuffix(",") + ","


def extract_modon_ar_block(path: Path) -> str:
    text = path.read_text(encoding="utf-8")
    key = php_quote(MODON_EN_TITLE)
    start = text.find(f"  {key} => [")
    if start == -1:
        raise RuntimeError("Could not find Modon AR override block")
    senaei_start = text.find("  'Senaei Platform:", start)
    if senaei_start == -1:
        senaei_start = text.find("\n];", start)
    return text[start:senaei_start].rstrip().removesuffix(",") + ","


def patch_dataset(en_row: dict, modon_block: str) -> None:
    header = textwrap.dedent(
        """\
        <?php

        /**
         * @file
         * Modon + Senaei platform import rows.
         *
         * Regenerate Modon: python3 scripts/patch_modon_dataset_from_949.py
         * Regenerate Senaei: python3 scripts/patch_senaei_dataset.py
         * Regenerate CSV: php scripts/rebuild_platform_modon_senaei_csv.php
         */

        declare(strict_types=1);

        require_once __DIR__ . '/platform_import.i18n.php';

        return [
        """
    )
    DATASET.write_text(header + modon_block + "\n" + php_row(en_row) + "\n];\n", encoding="utf-8")


def patch_ar_overrides(ar: dict, modon_block: str) -> None:
    en_key = ar.pop("_en_title_key")
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
        modon_block,
        f"  {php_quote(en_key)} => [",
        f"    'title' => {php_quote(ar['title'])},",
        f"    'field_subtitle' => {php_quote(ar['field_subtitle'])},",
        f"    'field_short_description' => {php_quote(ar['field_short_description'])},",
        f"    'field_cta_text' => {php_quote(ar['field_cta_text'])},",
        f"    'field_source_text' => {php_quote(ar['field_source_text'])},",
        f"    'body_summary' => {php_quote(ar['body_summary'])},",
        f"    'body' => {php_quote(ar['body'])},",
        f"    'field_meta_tags_json' => {php_quote(ar['field_meta_tags_json'])},",
        f"    'field_why_matters_json' => {php_quote(ar['field_why_matters_json'])},",
        "    'field_how_we_help_json' => '[]',",
        "    'field_process_steps_json' => '[]',",
        f"    'field_requirements_json' => {php_quote(ar['field_requirements_json'])},",
        f"    'field_resources_json' => {php_quote(ar['field_resources_json'])},",
        f"    'field_faq_json' => {php_quote(ar['field_faq_json'])},",
        f"    'field_related_platforms_titles' => {php_quote(ar['field_related_platforms_titles'])},",
        "  ],",
        "];",
        "",
    ]
    AR_OVERRIDES.write_text("\n".join(lines), encoding="utf-8")


def main() -> None:
    en_p = docx_paragraphs(EN_DOCX)
    ar_p = docx_paragraphs(AR_DOCX)
    en_row = build_en_row(en_p)
    ar = build_ar_overrides(ar_p, en_row["title"])
    modon_dataset = extract_modon_block(DATASET)
    modon_ar = extract_modon_ar_block(AR_OVERRIDES)
    patch_dataset(en_row, modon_dataset)
    patch_ar_overrides(ar, modon_ar)
    print(f"Patched Senaei EN in {DATASET}")
    print(f"Patched Senaei AR in {AR_OVERRIDES}")
    print(f"EN title: {en_row['title']}")
    print(
        f"EN why={len(en_row['why_json'])} help={len(en_row['help_json'])} "
        f"req={len(en_row['req_json'])} res={len(en_row['resources_json'])} "
        f"faq={len(en_row['faq_json'])} body={len(en_row['body'])}"
    )


if __name__ == "__main__":
    main()
