#!/usr/bin/env python3
"""
Split service package full HTML bodies into page body + pricing card fragments.

Preserves 100% of text: normalize(strip_tags(full)) == normalize(strip_tags(body + cards)).

Usage:
  python3 scripts/split_service_package_bodies.py
"""

from __future__ import annotations

import re
import shutil
from collections import Counter
from html import unescape
from pathlib import Path

DATA_DIR = Path(__file__).resolve().parents[1] / "web/modules/custom/motaded_custom/data/service_packages"

PACKAGES = (
    "entrepreneur_license_package",
    "rhq_package",
    "office_solutions_packages",
)

LANGS = ("en", "ar")


def strip_text(html: str) -> str:
    text = re.sub(r"<[^>]+>", " ", html)
    text = unescape(text)
    text = re.sub(r"\s+", " ", text).strip()
    return text


def assert_preserved(full: str, parts: list[str], label: str) -> None:
    """Ensure split parts contain exactly the same visible text as the source."""
    combined = "".join(parts)
    full_norm = strip_text(full)
    combined_norm = strip_text(combined)
    if Counter(full_norm) != Counter(combined_norm):
        raise ValueError(
            f"{label}: content mismatch after split "
            f"(full={len(full_norm)} chars, combined={len(combined_norm)} chars)"
        )


def find_or_fail(haystack: str, needle: str, label: str) -> int:
    pos = haystack.find(needle)
    if pos < 0:
        raise ValueError(f"{label}: marker not found: {needle[:80]!r}")
    return pos


def split_entrepreneur(html: str, lang: str) -> tuple[str, list[str]]:
    if lang == "en":
        pricing = find_or_fail(html, "<h3><strong>Pricing Structure", "entrepreneur")
        eligibility = find_or_fail(html, "<h3><strong>Eligibility Requirements", "entrepreneur")
        services = find_or_fail(html, "<h4><strong>Services Included", "entrepreneur")
        timeline = find_or_fail(html, "<p><strong>Timeline</strong>", "entrepreneur")
    else:
        pricing = find_or_fail(html, "<h3><strong>هيكل التسعير", "entrepreneur")
        eligibility = find_or_fail(html, "<h3><strong>شروط الأهليّة", "entrepreneur")
        services = find_or_fail(html, "<h4><strong>الخدمات المشمولة", "entrepreneur")
        timeline = find_or_fail(html, "<p><strong>الجدول الزمنيّ", "entrepreneur")

    card_html = html[pricing:eligibility] + html[services:timeline]
    body_html = html[:pricing] + html[eligibility:services] + html[timeline:]
    assert_preserved(html, [body_html, card_html], f"entrepreneur/{lang}")
    return body_html, [card_html]


def split_rhq(html: str, lang: str) -> tuple[str, list[str]]:
    if lang == "en":
        package = find_or_fail(html, "<h3><strong>RHQ Package All-Inclusive", "rhq")
        who = find_or_fail(html, "<h3><strong>Who It", "rhq")
        included = find_or_fail(html, "<h3><strong>Included Services", "rhq")
        benefits = find_or_fail(html, "<h3><strong>RHQ Program Benefits", "rhq")
    else:
        package = find_or_fail(html, "<h3><strong>باقة المقرّ الإقليميّ", "rhq")
        who = find_or_fail(html, "<h3><strong>الفئة المستهدفة", "rhq")
        included = find_or_fail(html, "<h3><strong>الخدمات المشمولة", "rhq")
        benefits = find_or_fail(html, "<h3><strong>مزايا برنامج", "rhq")

    card_html = html[package:who] + html[included:benefits]
    body_html = html[:package] + html[who:included] + html[benefits:]
    assert_preserved(html, [body_html, card_html], f"rhq/{lang}")
    return body_html, [card_html]


def split_office(html: str, lang: str) -> tuple[str, list[str]]:
    if lang == "en":
        intro_end = find_or_fail(html, "<h3><strong>1.", "office")
        comparison = find_or_fail(html, "<h3><strong>Quick Comparison", "office")
        pattern = re.compile(r"<h3><strong>\d+\..*?</h3>", re.DOTALL)
    else:
        intro_end = find_or_fail(html, "<h3><strong>١.", "office")
        comparison = find_or_fail(html, "<h3><strong>مقارنة سريعة", "office")
        pattern = re.compile(r"<h3><strong>[١٢٣٤٥٦]\..*?</h3>", re.DOTALL)

    intro = html[:intro_end]
    comparison_block = html[comparison:]
    services_block = html[intro_end:comparison]

    matches = list(pattern.finditer(services_block))
    if len(matches) != 6:
        raise ValueError(f"office/{lang}: expected 6 service sections, found {len(matches)}")

    cards: list[str] = []
    for index, match in enumerate(matches):
        start = match.start()
        end = matches[index + 1].start() if index + 1 < len(matches) else len(services_block)
        cards.append(services_block[start:end])

    body_html = intro + comparison_block
    assert_preserved(html, [body_html, *cards], f"office/{lang}")
    return body_html, cards


SPLITTERS = {
    "entrepreneur_license_package": split_entrepreneur,
    "rhq_package": split_rhq,
    "office_solutions_packages": split_office,
}


def main() -> None:
    for package in PACKAGES:
        for lang in LANGS:
            full_path = DATA_DIR / f"{package}.body.{lang}.html"
            archive_path = DATA_DIR / f"{package}.body.full.{lang}.html"
            if not full_path.is_file():
                raise FileNotFoundError(full_path)

            if archive_path.is_file():
                full_html = archive_path.read_text(encoding="utf-8")
            else:
                full_html = full_path.read_text(encoding="utf-8")
                shutil.copy2(full_path, archive_path)

            body_html, card_htmls = SPLITTERS[package](full_html, lang)

            (DATA_DIR / f"{package}.body.{lang}.html").write_text(body_html, encoding="utf-8")

            pricing_dir = DATA_DIR / "pricing" / package
            pricing_dir.mkdir(parents=True, exist_ok=True)
            for index, card_html in enumerate(card_htmls, start=1):
                card_path = pricing_dir / f"card_{index}.{lang}.html"
                card_path.write_text(card_html, encoding="utf-8")

            print(
                f"OK {package}/{lang}: body={len(body_html)} chars, "
                f"cards={len(card_htmls)} ({sum(len(c) for c in card_htmls)} chars)"
            )


if __name__ == "__main__":
    main()
