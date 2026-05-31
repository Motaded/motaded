#!/usr/bin/env python3
"""Patch Modon EN/AR platform import datasets to match editorial node 949 structure."""

from __future__ import annotations

import html
import json
import re
import textwrap
import zipfile
import xml.etree.ElementTree as ET
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
EXPORT = ROOT / "tmp/modon_949_export.json"
AR_DOCX = ROOT / "Modon platform/25_Modon_Platform_AR.docx"
DATASET = ROOT / "web/modules/custom/motaded_custom/data/platform_import_modon_senaei.dataset.php"
AR_OVERRIDES = ROOT / "web/modules/custom/motaded_custom/data/platform_import_modon_senaei_ar.overrides.php"
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
        parts = []
        for k, v in item.items():
            parts.append(f"'{k}' => {php_quote(str(v))}")
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


def build_ar_body(p: list[str]) -> str:
    parts: list[str] = []
    parts.append(f"<p>{esc_p(p[16])}</p>")
    parts.append(f"<p>{esc_p(p[18])}&nbsp;</p>")
    parts.append(
        paras_to_ul(
            [
                p[19],
                p[20],
                p[21],
                p[22],
                p[23],
                p[24],
            ]
        )
    )
    parts.append(f"<h3><strong>{esc_p(p[26])}</strong></h3>")
    parts.append(f"<p>{esc_p(p[27])}</p>")
    parts.append(paras_to_ul(p[28:34]))
    parts.append(f"<h3><strong>{esc_p(p[60])}</strong></h3>")
    parts.append(f"<p>{esc_p(p[61])}</p>")
    parts.append(f"<p><strong>1. {esc_p(p[62].split('. ', 1)[-1])}</strong></p>")
    parts.append(paras_to_ul(p[63:68]))
    parts.append(f"<p><strong>2. {esc_p(p[68].split('. ', 1)[-1])}</strong></p>")
    parts.append(paras_to_ul(p[69:74]))
    parts.append(f"<p><strong>{esc_p(p[74])}</strong></p>")
    for note in p[75:79]:
        bullet = note[2:].strip() if note.startswith("•") else note
        parts.append(f"<p>• {esc_p(bullet)}</p>")
    parts.append(f"<h3><strong>{esc_p(p[79])}</strong></h3>")
    parts.append(f"<p>{esc_p(p[80])}</p>")
    table_rows = [
        (p[83], p[84]),
        (p[85], p[86]),
        (p[87], p[88]),
        (p[89], p[90]),
        (p[91], p[92]),
        (p[93], p[94]),
    ]
    rows_html = "".join(
        f"<tr><td><strong>{esc_p(a)}</strong></td><td>{esc_p(b)}</td></tr>"
        for a, b in table_rows
    )
    parts.append(
        '<table class="table" cellspacing="0" cellpadding="0"><tbody>'
        f"<tr><td><strong>البند</strong></td><td><strong>التفاصيل</strong></td></tr>"
        f"{rows_html}</tbody></table>"
    )
    parts.append(f"<p><strong>{esc_p(p[95])}</strong></p>")
    for note in p[96:100]:
        bullet = note[2:].strip() if note.startswith("•") else note
        parts.append(f"<p><strong>• {esc_p(bullet)}</strong></p>")
    parts.append(f"<h3><strong>{esc_p(p[100])}</strong></h3>")
    parts.append(paras_to_ol(p[101:112]))
    parts.append(f"<p><strong>{esc_p(p[112])}</strong></p>")
    parts.append(f"<h3><strong>{esc_p(p[133])}</strong></h3>")
    parts.append(f"<p>{esc_p(p[134])}</p>")
    integration_rows = [
        (p[137], p[138]),
        (p[139], p[140]),
        (p[141], p[142]),
        (p[143], p[144]),
        (p[145], p[146]),
        (p[147], p[148]),
        (p[149], p[150]),
        (p[151], p[152]),
    ]
    int_html = "".join(
        f"<tr><td>{esc_p(a)}</td><td>{esc_p(b)}</td></tr>" for a, b in integration_rows
    )
    parts.append(
        '<table class="table" cellspacing="0" cellpadding="0"><tbody>'
        f"<tr><td><strong>الجهة / المنصة</strong></td><td><strong>نوع التكامل</strong></td></tr>"
        f"{int_html}</tbody></table>"
    )
    parts.append(f"<p><strong>{esc_p(p[153])}</strong></p>")
    parts.append(f"<p>{esc_p(p[154])}</p>")
    parts.append(f"<h3><strong>{esc_p(p[155])}</strong></h3>")
    parts.append(f"<p>{esc_p(p[156])}</p>")
    parts.append(paras_to_ul(p[157:167]))
    parts.append(f"<h3><strong>{esc_p(p[192])}</strong></h3>")
    parts.append(f"<p>{esc_p(p[193])}</p>")
    parts.append(f"<p>{esc_p(p[194])}</p>")
    parts.append(paras_to_ul(p[195:203]))
    parts.append(f"<h3><strong>{esc_p(p[203])}</strong></h3>")
    parts.append(f"<p>{esc_p(p[204])}</p>")
    return "".join(parts)


def extract_ar_services(p: list[str]) -> list[dict]:
    services = [
        ("1. تخصيص الأراضي الصناعية", 36, 40),
        ("2. نظام شريك (Shareek) الإلكتروني", 42, 47),
        ("3. تأهيل المقاولين والمطورين", 49, 52),
        ("4. بوابة الموردين", 54, 54),
        ("5. خدمات إدارية متنوعة", 56, 59),
    ]
    items: list[dict] = []
    for title, start, end in services:
        clean_title = re.sub(r"^\d+\.\s*", "", title)
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
        items.append({"title": clean_title, "body": body, "icon": ""})
    return items


def extract_ar_requirements(p: list[str]) -> list[dict]:
    blocks = [
        ("للمستثمر الصناعي:\n", 115, 120),
        ("للمقاولين والمطورين:\n", 122, 126),
        ("للشركات الأجنبية:\n", 128, 132),
    ]
    items: list[dict] = []
    for heading, start, end in blocks:
        lines = [f"- {p[i]}" for i in range(start, end + 1)]
        items.append({"content": heading + "\n".join(lines) + "\n"})
    return items


def extract_ar_faq(p: list[str]) -> list[dict]:
    faq: list[dict] = []
    i = 168
    while i + 1 < 192:
        q, a = p[i], p[i + 1]
        faq.append({"question": q, "answer": f"<p>{esc_p(a)}</p>"})
        i += 2
    return faq


def build_en_row(export: dict) -> dict:
    meta = export.get("meta", {})
    ext = export.get("field_external_link") or {}
    cta = export.get("field_cta_link") or {}
    return {
        "title": export["title"],
        "field_subtitle": export.get("field_subtitle") or "",
        "field_short_description": export["field_short_description"],
        "field_sector_name": export["field_sector"],
        "field_category_name": export["field_category"],
        "field_region_name": export["field_region"],
        "field_related_platforms_titles": "|".join(export.get("related", [])),
        "field_external_link_uri": ext.get("uri", "https://modon.gov.sa/"),
        "field_external_link_title": ext.get("title", "modon.gov.sa"),
        "field_cta_link_uri": cta.get("uri", ""),
        "field_cta_link_title": cta.get("title", ""),
        "field_cta_text": export.get("field_cta_text", ""),
        "field_source_link_uri": "",
        "field_source_link_title": "",
        "field_source_text": export.get("field_source_text") or "",
        "meta_title": meta.get("title", ""),
        "meta_description": meta.get("description", ""),
        "meta_keywords": meta.get("abstract") or meta.get("keywords", ""),
        "body_summary": export["body_summary"],
        "body": export["body"],
        "path_alias": "/platforms/modon",
        "why_json": export.get("why", []),
        "help_json": export.get("help", []),
        "steps_json": export.get("steps", []),
        "req_json": export.get("req", []),
        "resources_json": export.get("resources", []),
        "faq_json": export.get("faq", []),
    }


def build_ar_overrides(p: list[str], en_title: str) -> dict:
    return {
        "title": p[12],
        "field_subtitle": "",
        "field_short_description": p[18],
        "field_cta_text": "الدخول إلى نظام شريك",
        "field_source_text": "",
        "body_summary": p[16][:300],
        "body": build_ar_body(p),
        "field_meta_tags_json": json.dumps(
            {
                "title": p[8],
                "description": p[10],
                "keywords": p[6],
            },
            ensure_ascii=False,
            separators=(", ", ": "),
        ),
        "field_why_matters_json": json.dumps(
            extract_ar_services(p),
            ensure_ascii=False,
            separators=(", ", ": "),
        ),
        "field_how_we_help_json": "[]",
        "field_process_steps_json": "[]",
        "field_requirements_json": json.dumps(
            extract_ar_requirements(p),
            ensure_ascii=False,
            separators=(", ", ": "),
        ),
        "field_resources_json": json.dumps(
            [
                {
                    "title": "الموقع الرسمي لهيئة مدن",
                    "uri": "https://modon.gov.sa",
                    "link_title": "",
                    "icon": "globe",
                },
                {
                    "title": "نظام شريك الإلكتروني",
                    "uri": "https://modon.gov.sa/ar/Eservices/Pages/Shareek.aspx",
                    "link_title": "",
                    "icon": "globe",
                },
                {
                    "title": "صفحة تكلفة الصناعة الرسمية",
                    "uri": "https://modon.gov.sa/ar/Systems/Pages/IndustryCost.aspx",
                    "link_title": "",
                    "icon": "globe",
                },
                {
                    "title": "صفحة اتصل بنا الرسمية",
                    "uri": "https://modon.gov.sa/ar/ContactUs/Pages/ContactUs.aspx",
                    "link_title": "",
                    "icon": "globe",
                },
                {
                    "title": "صندوق التنمية الصناعية السعودي",
                    "uri": "https://www.sidf.gov.sa",
                    "link_title": "",
                    "icon": "globe",
                },
                {
                    "title": "صفحة مدن في المنصة الوطنية الموحدة",
                    "uri": "https://my.gov.sa/ar/agencies/17582",
                    "link_title": "",
                    "icon": "globe",
                },
            ],
            ensure_ascii=False,
            separators=(", ", ": "),
        ),
        "field_faq_json": json.dumps(
            extract_ar_faq(p),
            ensure_ascii=False,
            separators=(", ", ": "),
        ),
        "field_related_platforms_titles": "وزارة الاستثمار السعودية (ميسا)|المركز السعودي للأعمال|بلدي",
        "_en_title_key": en_title,
    }


def patch_dataset(en_row: dict, senaei_block: str) -> None:
    header = textwrap.dedent(
        """\
        <?php

        /**
         * @file
         * Modon + Senaei platform import rows (generated from client Word files).
         *
         * Regenerate Modon from node 949:
         *   python3 scripts/patch_modon_dataset_from_949.py
         * Regenerate CSV:
         *   php scripts/rebuild_platform_modon_senaei_csv.php
         * Import:
         *   ddev drush mpic platform_import_modon_senaei_ready.csv --upsert=1
         *   ddev drush mpic platform_import_modon_senaei_ar_ready.csv --upsert=1
         */

        declare(strict_types=1);

        require_once __DIR__ . '/platform_import.i18n.php';

        return [
        """
    )
    content = header + php_row(en_row) + "\n" + senaei_block + "\n];\n"
    DATASET.write_text(content, encoding="utf-8")


def patch_ar_overrides(ar: dict, senaei_block: str) -> None:
    en_key = ar.pop("_en_title_key")
    j = "$j"
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
        f"  {senaei_block.strip()}",
        "];",
        "",
    ]
    AR_OVERRIDES.write_text("\n".join(lines), encoding="utf-8")


def extract_senaei_block(path: Path, marker: str, *, row_prefix: str = "_motaded_pi_row(") -> str:
    text = path.read_text(encoding="utf-8")
    idx = text.find(marker)
    if idx == -1:
        raise RuntimeError(f"Could not find {marker!r} in {path}")
    if row_prefix == "_motaded_pi_row(":
        start = text.rfind(row_prefix, 0, idx)
        end = text.find("]),", idx)
        if start == -1 or end == -1:
            raise RuntimeError(f"Could not extract Senaei block from {path}")
        return text[start : end + 3]
    start = idx
    file_end = text.find("\n];", start)
    if file_end == -1:
        raise RuntimeError(f"Could not extract Senaei block from {path}")
    block = text[start:file_end].rstrip()
    if block.endswith("  ]"):
        block = block[:-3].rstrip() + "\n  ],"
    elif not block.endswith("  ],"):
        chunk_end = text.rfind("  ],", start, file_end)
        if chunk_end != -1:
            block = text[start : chunk_end + 4]
        else:
            raise RuntimeError(f"Could not extract Senaei block from {path}")
    return block


def main() -> None:
    export = json.loads(EXPORT.read_text(encoding="utf-8"))
    paras = docx_paragraphs(AR_DOCX)
    en_row = build_en_row(export)
    ar = build_ar_overrides(paras, en_row["title"])

    senaei_dataset = extract_senaei_block(DATASET, "'title' => 'Senaei'")
    senaei_overrides = extract_senaei_block(
        AR_OVERRIDES, "'Senaei' => [", row_prefix="'Senaei' => ["
    )

    patch_dataset(en_row, senaei_dataset)
    patch_ar_overrides(ar, senaei_overrides)

    print(f"Patched EN Modon in {DATASET}")
    print(f"Patched AR Modon in {AR_OVERRIDES}")
    print(f"EN title: {en_row['title']}")
    print(f"AR title: {ar['title']}")
    print(f"EN why/services: {len(en_row['why_json'])}, help: {len(en_row['help_json'])}, req: {len(en_row['req_json'])}, faq: {len(en_row['faq_json'])}")
    print(f"AR body length: {len(ar['body'])}")


if __name__ == "__main__":
    main()
