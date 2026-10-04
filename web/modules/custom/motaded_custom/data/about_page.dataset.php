<?php

/**
 * @file
 * About Motaded content patches (group companies + mid-page CTAs).
 */

declare(strict_types=1);

return [
  'alias' => '/about-us',
  'group' => [
    'match_titles' => [
      'Four companies, one group',
      'Three companies, one group',
      'أربع شركات، مجموعة واحدة',
      'ثلاث شركات، مجموعة واحدة',
    ],
    'en' => [
      'title' => 'Four companies, one group',
      'lede' => '<p>Each Motaded group entity is a separate Saudi legal person with its own Commercial Registration. Together they cover the full path of a foreign investor — from incorporation, through staffing, to import-export operations.</p>',
    ],
    'ar' => [
      'title' => 'أربع شركات، مجموعة واحدة',
      'lede' => '<p>كلّ كيان في مجموعة متعدد شخصيّة اعتباريّة سعوديّة مستقلّة بسجلّ تجاريّ خاصّ. مجتمعةً، تغطّي المسار الكامل للمستثمر الأجنبيّ — من التأسيس، عبر التوظيف، إلى عمليّات الاستيراد والتصدير.</p>',
    ],
    'cards' => [
      [
        'match' => ['Motaded Limited Company', 'شركة متعدد المحدودة'],
        'en' => [
          'title' => 'Motaded Limited Company',
          'body' => '<p>The group\'s flagship consultancy. Specialises in company formation, MISA licensing, government relations, SFDA regulatory affairs, accounting, audit, VAT and zakat compliance, intellectual property registration, and serviced offices.</p>',
        ],
        'ar' => [
          'title' => 'شركة متعدد المحدودة',
          'body' => '<p>الشركة الاستشاريّة الرئيسيّة للمجموعة. متخصّصة في تأسيس الشركات، ترخيص وزارة الاستثمار، العلاقات الحكوميّة، الشؤون التنظيميّة لدى هيئة الغذاء والدواء، المحاسبة، التدقيق، الامتثال الضريبيّ والزكويّ، تسجيل الملكيّة الفكريّة، والمكاتب المُجَهَّزة.</p>',
        ],
      ],
      [
        'match' => ['Talent Solutions', 'شركة الكوادر للحلول'],
        'en' => [
          'title' => 'Talent Solutions',
          'body' => '<p>Specialised HR outsourcing and manpower supply. Provides payroll administration, employee onboarding via Qiwa, residency processing, work permits, Saudization compliance, and end-to-end PRO/GRO services.</p>',
        ],
        'ar' => [
          'title' => 'شركة الكوادر للحلول',
          'body' => '<p>شركة متخصّصة في الخدمات الإداريّة للموارد البشريّة وتوريد القوى العاملة. تقدّم إدارة الرواتب، توثيق العقود في قوى، معالجة الإقامات، تراخيص العمل، الامتثال للسعودة، والخدمات الكاملة لـ PRO وGRO.</p>',
        ],
      ],
      [
        'match' => ['Motaded Customs Company', 'شركة متعدد للتخليص الجمركيّ'],
        'en' => [
          'title' => 'Motaded Customs Company',
          'body' => '<p>Dedicated customs broker and trade logistics entity. Handles import and export clearance, Fasah and Saber platform operations, ZATCA customs declarations, and operates across all 9 Saudi commercial and industrial ports.</p>',
        ],
        'ar' => [
          'title' => 'شركة متعدد للتخليص الجمركيّ',
          'body' => '<p>كيان مستقلّ متخصّص في التخليص الجمركيّ ولوجستيّات التجارة. يعالج تخليص الاستيراد والتصدير، عمليّات منصّتَي فسح وسابر، إقرارات هيئة الزكاة والضريبة والجمارك، ويعمل عبر جميع الموانئ السعوديّة التجاريّة والصناعيّة التسعة.</p>',
        ],
      ],
      [
        'match' => ['Mavzen Company', 'شركة مافزن'],
        'en' => [
          'title' => 'Mavzen Company',
          'body' => '<p>A complete finance function for your business, without the cost of building one in-house. Provides accounting and bookkeeping, VAT filing, zakat and income tax filing, audit, tax advisory, e-invoicing implementation and solutions, management reporting, and virtual CFO services.</p>',
        ],
        'ar' => [
          'title' => 'شركة مافزن',
          'body' => '<p>وظيفة ماليّة مكتملة لأعمالك، دون تكلفة بناء فريق داخليّ. تقدّم المحاسبة ومسك الدفاتر، تقديم ضريبة القيمة المضافة، تقديم الزكاة وضريبة الدخل، التدقيق، الاستشارات الضريبيّة، تطبيق الفوترة الإلكترونيّة وحلولها، التقارير الإداريّة، وخدمات المدير الماليّ الافتراضيّ.</p>',
        ],
      ],
    ],
  ],
  'cta' => [
    'en' => [
      'title' => 'Ready to establish in Saudi Arabia?',
      'body' => '<p>Every Motaded engagement begins with a private consultation.</p>',
      'link_title' => 'Book a consultation',
    ],
    'ar' => [
      'title' => 'جاهز للتأسيس في السعوديّة؟',
      'body' => '<p>كلّ مهمّة لمتعدد تبدأ باستشارة خاصّة.</p>',
      'link_title' => 'احجز استشارة',
    ],
    'uri' => 'internal:#consultation',
    'after' => [
      [
        'The team behind every Motaded engagement',
        'الفريق وراء كلّ مهمّة لمتعدد',
      ],
      [
        'Foreign investors who trust Motaded',
        'مستثمرون أجانب يثقون بمتعدد',
      ],
    ],
  ],
];
