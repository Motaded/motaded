<?php

/**
 * @file
 * Arabic internal link snippets for official event translations.
 *
 * Paths omit /ar — language negotiation adds the prefix (same as platforms).
 */

declare(strict_types=1);

return static function (): array {
  return [
    'library' => '<a href="/library">مكتبة متعدد</a>',
    'events_dir' => '<a href="/events">دليل الفعاليات</a>',
    'inc_guide' => '<a href="/documents/company-incorporation-guide">دليل تأسيس الشركات</a>',
    'inv_law' => '<a href="/documents/investment-law">نظام الاستثمار</a>',
    'sector_tech' => '<a href="/sectors/technology">قطاع التكنولوجيا</a>',
    'sector_energy' => '<a href="/sectors/energy">قطاع الطاقة</a>',
    'sector_health' => '<a href="/sectors/healthcare">قطاع الرعاية الصحية</a>',
    'sector_construction' => '<a href="/sectors/construction-infrastructure">الإنشاءات والبنية التحتية</a>',
    'sector_tourism' => '<a href="/sectors/tourism">قطاع السياحة</a>',
    'sector_logistics' => '<a href="/sectors/logistics-transport">الخدمات اللوجستية والنقل</a>',
    'sector_finance' => '<a href="/sectors/finance-fintech">التمويل والتقنية المالية</a>',
    'platform_misa' => '<a href="/platforms/misa">منصة ميسا</a>',
    'platform_zatca' => '<a href="/platforms/zatca">هيئة الزكاة والضريبة والجمارك</a>',
  ];
};
