<?php

/**
 * @file
 * Applies official institutional copy to the live accounting landing.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_accounting_official_tone.php
 */

declare(strict_types=1);

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\webform\Entity\Webform;

$copy = [
  'meta' => [
    'en' => [
      'title' => 'Accounting Services in Saudi Arabia | Motaded',
      'description' => 'Accounting services in Saudi Arabia: bookkeeping, financial reporting, tax compliance and audit support for ongoing requirements and individual assignments. Scope and fees are agreed before the engagement begins.',
    ],
    'ar' => [
      'title' => 'الخدمات المحاسبية في المملكة العربية السعودية | Motaded',
      'description' => 'الخدمات المحاسبية في المملكة العربية السعودية: مسك الدفاتر والتقارير المالية وتقديم الإقرارات ودعم التدقيق. يُتفق على نطاق العمل وأتعاب الخدمة كتابةً قبل البدء.',
    ],
  ],
  'hero' => [
    'en' => [
      'eyebrow' => 'Accounting · Tax · Audit Support',
      'prefix' => 'Accounting Services',
      'accent' => 'in Saudi Arabia',
      'body' => '<p>Bookkeeping, financial reporting, tax compliance and audit support for companies operating in Saudi Arabia. <a href="/about-us">Motaded</a> assists with ongoing accounting requirements and individual assignments, with the scope of work and fees agreed before the engagement begins.</p>',
      'cta' => 'Request a Consultation',
      'secondary' => 'Explore Accounting Services',
    ],
    'ar' => [
      'eyebrow' => 'محاسبة · ضرائب · دعم التدقيق',
      'prefix' => 'الخدمات المحاسبية',
      'accent' => 'في المملكة العربية السعودية',
      'body' => '<p>مسك الدفاتر والتقارير المالية والامتثال الضريبي ودعم التدقيق للشركات العاملة في المملكة العربية السعودية. يساعد <a href="/about-us">متعدد</a> في المتطلبات المحاسبية المستمرة والمهام الفردية، مع الاتفاق على نطاق العمل والأتعاب قبل بدء الارتباط.</p>',
      'cta' => 'طلب استشارة',
      'secondary' => 'استكشف الخدمات المحاسبية',
    ],
  ],
  'context' => [
    'match' => [
      'Accounting and Tax Compliance in Saudi Arabia',
    ],
    'replace' => TRUE,
    'type' => 'context',
    'en' => [
      'title' => 'Accounting and Tax Compliance in Saudi Arabia',
      'lede' => '<p>Accounting and tax compliance involve distinct responsibilities: maintaining reliable financial records, preparing financial statements and meeting applicable registration, filing and invoicing requirements. The requirements relevant to a business depend on its legal structure, ownership, activities and transactions.</p>',
      'items' => [
        ['title' => 'Financial Records and Reporting', 'body' => '<p>Organised accounting records provide the basis for financial reporting, reconciliation and tax preparation. Financial statements are prepared under the applicable reporting framework, including IFRS Accounting Standards endorsed in Saudi Arabia or the endorsed IFRS for SMEs Accounting Standard, as appropriate to the entity.</p>'],
        ['title' => 'Zakat and Tax Obligations', 'body' => '<p><a href="/platforms/zatca">ZATCA</a> administers <a href="/services/zakat">Zakat</a>, <a href="/services/corporate-tax">corporate income tax</a>, <a href="/services/vat-advisory-services-saudi-arabia">VAT</a> and <a href="/services/withholding-tax-wht-services">withholding tax</a>. These obligations do not apply uniformly to every company. Each business should identify the requirements relevant to its circumstances and maintain the records needed for the applicable returns and calculations.</p>'],
        ['title' => 'VAT and Electronic Invoicing', 'body' => '<p>Businesses within the scope of <a href="/documents/vat-law-english">VAT</a> requirements must address applicable <a href="/services/vat-registration-services-saudi-arabia">registration</a>, invoicing and <a href="/services/vat-filing-services-saudi-arabia">return</a> obligations. Electronic invoicing is governed by <a href="/platforms/zatca">ZATCA</a>’s <a href="/blog/zatca-e-invoicing-phase-2-saudi-arabia-motaded">FATOORA</a> requirements, with generation and integration requirements applied according to the relevant rules and implementation phases.</p>'],
        ['title' => 'Reporting and Filing Coordination', 'body' => '<p>Financial reporting and tax filing are related but separate processes. A clear schedule for collecting documents, reconciling balances, reviewing reports and preparing returns helps ensure that the information required for each obligation is available.</p>'],
      ],
    ],
    'ar' => [
      'title' => 'المحاسبة والامتثال الضريبي في المملكة العربية السعودية',
      'lede' => '<p>تنطوي المحاسبة والامتثال الضريبي على مسؤوليات متمايزة: المحافظة على سجلات مالية موثوقة، وإعداد القوائم المالية، واستيفاء متطلبات التسجيل والتقديم والفوترة المنطبقة. وتعتمد المتطلبات ذات الصلة بالمنشأة على شكلها النظامي وملكيتها وأنشطتها ومعاملاتها.</p>',
      'items' => [
        ['title' => 'السجلات المالية والتقارير', 'body' => '<p>توفّر السجلات المحاسبية المنظّمة الأساس للتقارير المالية والتسويات وإعداد الضرائب. تُعد القوائم المالية وفق إطار التقارير المنطبق، بما في ذلك معايير المحاسبة الدولية IFRS المعتمدة في المملكة العربية السعودية أو المعيار الدولي للتقرير المالي للمنشآت الصغيرة والمتوسطة IFRS for SMEs المعتمد، بحسب طبيعة المنشأة.</p>'],
        ['title' => 'التزامات الزكاة والضرائب', 'body' => '<p>تدير <a href="/platforms/zatca">هيئة الزكاة والضريبة والجمارك ZATCA</a> <a href="/services/zakat">الزكاة</a> و<a href="/services/corporate-tax">ضريبة الدخل على الشركات</a> و<a href="/services/vat-advisory-services-saudi-arabia">ضريبة القيمة المضافة</a> و<a href="/services/withholding-tax-wht-services">ضريبة الاستقطاع</a>. ولا تنطبق هذه الالتزامات بشكل موحّد على كل منشأة. ينبغي لكل منشأة تحديد المتطلبات ذات الصلة بظروفها والمحافظة على السجلات اللازمة للإقرارات والحسابات المنطبقة.</p>'],
        ['title' => 'ضريبة القيمة المضافة والفوترة الإلكترونية', 'body' => '<p>يجب على المنشآت المشمولة بمتطلبات <a href="/documents/vat-law-english">ضريبة القيمة المضافة</a> معالجة التزامات <a href="/services/vat-registration-services-saudi-arabia">التسجيل</a> والفوترة و<a href="/services/vat-filing-services-saudi-arabia">الإقرارات</a> المنطبقة. وتخضع الفوترة الإلكترونية لمتطلبات <a href="/blog/zatca-e-invoicing-phase-2-saudi-arabia-motaded">فاتورة FATOORA</a> الصادرة عن <a href="/platforms/zatca">ZATCA</a>، مع تطبيق متطلبات الإصدار والربط وفق القواعد والمراحل التنفيذية ذات الصلة.</p>'],
        ['title' => 'تنسيق التقارير والتقديم', 'body' => '<p>التقارير المالية وتقديم الإقرارات عمليتان مرتبطتان لكن منفصلتان. يساعد جدول واضح لجمع المستندات وتسوية الأرصدة ومراجعة التقارير وإعداد الإقرارات على توفير المعلومات اللازمة لكل التزام.</p>'],
      ],
    ],
  ],
  'needs' => [
    'match' => [
      'Accounting support for every stage of your business',
      'Accounting support at each stage of the business',
      'Accounting Support at Every Stage of Your Business',
    ],
    'en' => [
      'title' => 'Accounting Support at Every Stage of Your Business',
      'lede' => '<p>Accounting requirements evolve as a company begins operations, establishes regular processes and expands its activities. <a href="/about-us">Motaded</a> provides support appropriate to the company’s stage, available records and reporting needs.</p>',
      'items' => [
        ['title' => 'Newly Established Companies', 'body' => '<p>Establish an organised accounting foundation from the start of operations. Support includes setting up records and reporting processes so that transactions are documented consistently and financial information is available for subsequent accounting and <a href="/platforms/zatca">tax</a> work.</p>'],
        ['title' => 'Operating Companies', 'body' => '<p>Maintain current accounting records and a consistent reporting cycle. Regular support helps management understand the company’s financial position, monitor outstanding balances and prepare the information required for applicable <a href="/services/vat-filing-services-saudi-arabia">filings</a>.</p>'],
        ['title' => 'Growing Companies', 'body' => '<p>Adapt accounting processes to increased transaction volumes and more complex operations. Support focuses on reliable reporting, reconciliation and financial information for management review and <a href="/services/external-audit">audit preparation</a>.</p>'],
      ],
    ],
    'ar' => [
      'title' => 'الدعم المحاسبي في كل مرحلة من مراحل أعمالكم',
      'lede' => '<p>تتطور المتطلبات المحاسبية مع بدء المنشأة عملياتها ووضع إجراءات منتظمة وتوسيع أنشطتها. يقدّم <a href="/about-us">متعدد</a> الدعم المناسب لمرحلة المنشأة والسجلات المتوفرة واحتياجات التقارير.</p>',
      'items' => [
        ['title' => 'المنشآت حديثة التأسيس', 'body' => '<p>إرساء أساس محاسبي منظّم منذ بدء العمليات. يشمل الدعم إعداد السجلات وإجراءات التقارير بحيث تُوثَّق المعاملات بشكل متسق وتكون المعلومات المالية متاحة للعمل المحاسبي و<a href="/platforms/zatca">الضريبي</a> اللاحق.</p>'],
        ['title' => 'المنشآت القائمة', 'body' => '<p>المحافظة على سجلات محاسبية محدّثة ودورة تقارير منتظمة. يساعد الدعم المنتظم الإدارة على فهم المركز المالي للمنشأة ومتابعة الأرصدة المعلقة وإعداد المعلومات اللازمة <a href="/services/vat-filing-services-saudi-arabia">للتقديمات</a> المنطبقة.</p>'],
        ['title' => 'المنشآت النامية', 'body' => '<p>تكييف الإجراءات المحاسبية مع زيادة حجم المعاملات وتعقّد العمليات. يركّز الدعم على تقارير موثوقة والتسويات والمعلومات المالية لمراجعة الإدارة و<a href="/services/external-audit">الإعداد للتدقيق</a>.</p>'],
      ],
    ],
  ],
  'situations' => [
    'match' => [
      'When you need accounting support',
      'When the service applies',
      'Accounting Challenges We Address',
    ],
    'replace' => TRUE,
    'en' => [
      'title' => 'Accounting Challenges We Address',
      'lede' => '<p>Support is available for companies requiring corrections to existing records, continuity during an accounting handover or preparation for reporting and filing obligations.</p>',
      'items' => [
        ['title' => 'Accounting Records Are Behind', 'body' => '<p>Review outstanding periods, identify missing documents and <a href="/services/accounting-catch-services-saudi-arabia">bring the agreed accounting records up to date</a>.</p>'],
        ['title' => 'A Change of Accountant or Service Provider', 'body' => '<p>Organise the transfer of available records, opening balances and outstanding matters to support continuity of accounting work.</p>'],
        ['title' => 'Financial Reports Are Incomplete or Unclear', 'body' => '<p>Review the underlying records and reconciliations to prepare <a href="/services/accounts-review-services-saudi-arabia">financial reports</a> that provide a clearer view of business performance and financial position.</p>'],
        ['title' => 'A Tax Filing Deadline Is Approaching', 'body' => '<p>Assess the available information, identify outstanding requirements and prepare the agreed <a href="/services/vat-filing-services-saudi-arabia">filing</a>. Submission is included where authorised and specified in the engagement.</p>'],
        ['title' => 'An External Audit Is Being Prepared', 'body' => '<p>Organise accounting schedules and supporting documents, identify gaps in the records and coordinate responses to the appointed <a href="/services/external-audit">auditor</a>’s information requests.</p>'],
        ['title' => 'Request a Consultation', 'body' => '', 'uri' => 'internal:#assessment', 'link_title' => 'Request a Consultation'],
      ],
    ],
    'ar' => [
      'title' => 'التحديات المحاسبية التي نعالجها',
      'lede' => '<p>يتوفر الدعم للمنشآت التي تحتاج إلى تصحيح السجلات القائمة، أو استمرارية أثناء تسليم المحاسبة، أو إعداد التزامات التقارير والتقديم.</p>',
      'items' => [
        ['title' => 'السجلات المحاسبية متأخرة', 'body' => '<p>مراجعة الفترات المعلقة، وتحديد المستندات الناقصة، و<a href="/services/accounting-catch-services-saudi-arabia">تحديث السجلات المحاسبية المتفق عليها</a>.</p>'],
        ['title' => 'تغيير المحاسب أو مقدّم الخدمة', 'body' => '<p>تنظيم نقل السجلات المتوفرة والأرصدة الافتتاحية والمسائل المعلقة لدعم استمرارية العمل المحاسبي.</p>'],
        ['title' => 'التقارير المالية غير مكتملة أو غير واضحة', 'body' => '<p>مراجعة السجلات الأساسية والتسويات لإعداد <a href="/services/accounts-review-services-saudi-arabia">تقارير مالية</a> تعطي صورة أوضح عن أداء المنشأة ومركزها المالي.</p>'],
        ['title' => 'يقترب موعد تقديم ضريبي', 'body' => '<p>تقييم المعلومات المتوفرة، وتحديد المتطلبات المعلقة، وإعداد <a href="/services/vat-filing-services-saudi-arabia">الإقرار</a> المتفق عليه. يُدرج التقديم عند التفويض وتحديده في الارتباط.</p>'],
        ['title' => 'يُعد تدقيق خارجي', 'body' => '<p>تنظيم الجداول المحاسبية والمستندات الداعمة، وتحديد الفجوات في السجلات، وتنسيق الردود على طلبات معلومات <a href="/services/external-audit">المدقق</a> المعيّن.</p>'],
        ['title' => 'طلب استشارة', 'body' => '', 'uri' => 'internal:#assessment', 'link_title' => 'طلب استشارة'],
      ],
    ],
  ],
  'catalogue' => [
    'en' => '<h2>Accounting, tax and audit services</h2><p>Support may be requested for a specific requirement or for an ongoing arrangement covering several services.</p>',
    'ar' => '<h2>الخدمات المحاسبية والضريبية وخدمات التدقيق</h2><p>يمكن طلب الدعم لمتطلب محدد أو لترتيب مستمر يشمل عدة خدمات.</p>',
  ],
  'working' => [
    'match' => ['Working with Motaded', 'Accounting Coordination and Progress Updates'],
    'replace' => TRUE,
    'en' => [
      'title' => 'Accounting Coordination and Progress Updates',
      'lede' => '<p>A designated <a href="/about-us">Motaded</a> coordinator manages document requests, transaction queries and the reporting schedule. Outstanding information and client actions are tracked throughout the engagement. Draft reports are shared for review and clarification before final delivery, while tasks requiring client approval are identified separately.</p>',
      'items' => [
        ['title' => 'Request a Consultation', 'body' => '', 'uri' => 'internal:#assessment', 'link_title' => 'Request a Consultation'],
        ['title' => 'WhatsApp', 'body' => '', 'uri' => 'https://wa.me/966539797197', 'link_title' => 'WhatsApp'],
      ],
    ],
    'ar' => [
      'title' => 'التنسيق المحاسبي وتحديثات التقدم',
      'lede' => '<p>يتولى منسق معيّن من <a href="/about-us">متعدد</a> إدارة طلبات المستندات واستفسارات المعاملات وجدول التقارير. تُتابع المعلومات المعلقة وإجراءات العميل طوال فترة الارتباط. تُشارك مسودات التقارير للمراجعة والإيضاح قبل التسليم النهائي، وتُحدد المهام التي تتطلب موافقة العميل بشكل منفصل.</p>',
      'items' => [
        ['title' => 'طلب استشارة', 'body' => '', 'uri' => 'internal:#assessment', 'link_title' => 'طلب استشارة'],
        ['title' => 'WhatsApp', 'body' => '', 'uri' => 'https://wa.me/966539797197', 'link_title' => 'WhatsApp'],
      ],
    ],
  ],
  'formats' => [
    'match' => [
      'Ongoing Accounting and One-Off Assignments',
    ],
    'replace' => TRUE,
    'type' => 'formats',
    'en' => [
      'title' => 'Ongoing Accounting and One-Off Assignments',
      'lede' => '<p>Accounting support can be arranged as a recurring engagement or a defined assignment. The appropriate format depends on whether the company requires regular accounting operations or completion of a specific task.</p>',
      'items' => [
        [
          'title' => 'Ongoing Accounting Support',
          'body' => '<p>For companies requiring consistent maintenance of their accounting records and periodic reporting.</p><p>The engagement may include:</p><ul><li>Recording and classification of business transactions.</li><li>Bank and account reconciliations.</li><li>Periodic financial and management reports.</li><li><a href="/services/payroll-management-services-saudi-arabia">Payroll</a> calculations and related accounting entries.</li><li>Preparation of applicable <a href="/services/vat-filing-services-saudi-arabia">tax returns</a>, where included.</li></ul><p>The agreed schedule defines reporting periods, document submission deadlines and responsibilities. Each cycle concludes with the agreed reports and a record of outstanding matters.</p>',
        ],
        [
          'title' => 'One-Off Assignments',
          'body' => '<p>For companies requiring work on a defined accounting period, application or reporting requirement.</p><p>Assignments may include:</p><ul><li>Accounting setup for a new business.</li><li><a href="/services/accounting-catch-services-saudi-arabia">Catch-up accounting</a> for outstanding periods.</li><li><a href="/services/accounts-review-services-saudi-arabia">Review and reconciliation</a> of existing accounts.</li><li>Preparation of a specific <a href="/services/vat-filing-services-saudi-arabia">tax return</a>.</li><li>Accounting handover from a previous provider.</li><li>Preparation of schedules and documents for an <a href="/services/external-audit">external audit</a>.</li></ul><p>Each assignment has a defined scope, required inputs, deliverables and completion schedule. The client receives the completed materials and a record of any unresolved items requiring further action.</p>',
        ],
      ],
    ],
    'ar' => [
      'title' => 'المحاسبة المستمرة والمهام لمرة واحدة',
      'lede' => '<p>يمكن ترتيب الدعم المحاسبي كارتباط متكرر أو كمهمة محددة. ويعتمد الشكل المناسب على ما إذا كانت المنشأة تحتاج إلى عمليات محاسبية منتظمة أو إنجاز مهمة محددة.</p>',
      'items' => [
        [
          'title' => 'الدعم المحاسبي المستمر',
          'body' => '<p>للمنشآت التي تحتاج إلى محافظة مستمرة على سجلاتها المحاسبية وتقارير دورية.</p><p>قد يشمل الارتباط:</p><ul><li>تسجيل وتصنيف معاملات المنشأة.</li><li>تسويات البنوك والحسابات.</li><li>التقارير المالية والإدارية الدورية.</li><li>احتساب <a href="/services/payroll-management-services-saudi-arabia">الرواتب</a> والقيود المحاسبية ذات الصلة.</li><li>إعداد <a href="/services/vat-filing-services-saudi-arabia">الإقرارات الضريبية</a> المنطبقة، عند شمولها.</li></ul><p>يحدد الجدول المتفق عليه فترات التقارير ومواعيد تقديم المستندات والمسؤوليات. وتُختتم كل دورة بالتقارير المتفق عليها وسجل بالمسائل المعلقة.</p>',
        ],
        [
          'title' => 'المهام لمرة واحدة',
          'body' => '<p>للمنشآت التي تحتاج إلى عمل على فترة محاسبية محددة أو طلب أو متطلب تقارير.</p><p>قد تشمل المهام:</p><ul><li>إعداد النظام المحاسبي لمنشأة جديدة.</li><li><a href="/services/accounting-catch-services-saudi-arabia">استدراك المحاسبة</a> للفترات المعلقة.</li><li><a href="/services/accounts-review-services-saudi-arabia">مراجعة وتسوية</a> الحسابات القائمة.</li><li>إعداد <a href="/services/vat-filing-services-saudi-arabia">إقرار ضريبي</a> محدد.</li><li>تسليم محاسبي من مقدّم خدمة سابق.</li><li>إعداد الجداول والمستندات ل<a href="/services/external-audit">تدقيق خارجي</a>.</li></ul><p>لكل مهمة نطاق محدد، ومدخلات مطلوبة، ومخرجات، وجدول إنجاز. ويتلقى العميل المواد المنجزة وسجلاً بأي بنود غير محسومة تتطلب إجراءً لاحقاً.</p>',
        ],
      ],
    ],
  ],
  'process' => [
    'match' => ['How we work', 'Accounting Engagement Process'],
    'replace' => TRUE,
    'type' => 'stages',
    'en' => [
      'title' => 'Accounting Engagement Process',
      'lede' => '<p>Each engagement begins with a review of the company’s requirements and available records. The process below applies to ongoing accounting support and individual assignments, with deliverables and responsibilities defined for the selected services.</p>',
      'items' => [
        ['title' => 'Assessment of Existing Records', 'body' => '<p><a href="/about-us">Motaded</a> reviews the company’s activities, accounting period, available records and reporting requirements. The assessment identifies the condition of the records, outstanding information and priorities for the engagement.</p>'],
        ['title' => 'Agreement on Scope and Deliverables', 'body' => '<p>The written proposal defines the services, accounting periods, expected deliverables, responsibilities, schedule and fees. For ongoing support, it also establishes the reporting frequency and deadlines for providing documents.</p>'],
        ['title' => 'Transfer of Documents and Data', 'body' => '<p>The client provides the relevant accounting records and supporting documents through the agreed transfer method. Where access to existing accounting software is required, permissions are arranged separately. Missing information and opening balances requiring confirmation are recorded for follow-up.</p>'],
        ['title' => 'Accounting Work and Review', 'body' => '<p>The team completes the agreed accounting, reconciliation, reporting or <a href="/services/vat-filing-services-saudi-arabia">tax preparation</a> work. Transaction queries are referred to the client for clarification, and prepared records and reports undergo review before delivery. Filings requiring client approval are submitted where authorised and included in the engagement.</p>'],
        ['title' => 'Delivery of Results and Outstanding Actions', 'body' => '<p>The client receives the agreed records, reports or filing documentation, together with any matters requiring further action. For ongoing engagements, the next reporting period and outstanding tasks are confirmed. For individual assignments, the completed work and handover materials are documented.</p>'],
      ],
    ],
    'ar' => [
      'title' => 'عملية الارتباط المحاسبي',
      'lede' => '<p>يبدأ كل ارتباط بمراجعة متطلبات المنشأة والسجلات المتوفرة. تنطبق العملية أدناه على الدعم المحاسبي المستمر والمهام الفردية، مع تحديد المخرجات والمسؤوليات للخدمات المختارة.</p>',
      'items' => [
        ['title' => 'تقييم السجلات القائمة', 'body' => '<p>يراجع <a href="/about-us">متعدد</a> أنشطة المنشأة والفترة المحاسبية والسجلات المتوفرة ومتطلبات التقارير. يحدد التقييم حالة السجلات والمعلومات المعلقة وأولويات الارتباط.</p>'],
        ['title' => 'الاتفاق على النطاق والمخرجات', 'body' => '<p>يحدد العرض المكتوب الخدمات والفترات المحاسبية والمخرجات المتوقعة والمسؤوليات والجدول والأتعاب. وبالنسبة للدعم المستمر، يحدّد أيضاً وتيرة التقارير ومواعيد تقديم المستندات.</p>'],
        ['title' => 'نقل المستندات والبيانات', 'body' => '<p>يوفّر العميل السجلات المحاسبية والمستندات الداعمة ذات الصلة عبر طريقة النقل المتفق عليها. وإذا لزم الوصول إلى برنامج محاسبي قائم، تُرتَّب الصلاحيات بشكل منفصل. تُسجَّل المعلومات الناقصة والأرصدة الافتتاحية التي تتطلب تأكيداً للمتابعة.</p>'],
        ['title' => 'العمل المحاسبي والمراجعة', 'body' => '<p>ينجز الفريق العمل المتفق عليه في المحاسبة أو التسويات أو التقارير أو <a href="/services/vat-filing-services-saudi-arabia">إعداد الضرائب</a>. تُحال استفسارات المعاملات إلى العميل للإيضاح، وتخضع السجلات والتقارير المعدّة للمراجعة قبل التسليم. تُقدَّم الإقرارات التي تتطلب موافقة العميل عند التفويض وشمولها في الارتباط.</p>'],
        ['title' => 'تسليم النتائج والإجراءات المعلقة', 'body' => '<p>يتلقى العميل السجلات أو التقارير أو مستندات التقديم المتفق عليها، مع أي مسائل تتطلب إجراءً لاحقاً. وبالنسبة للارتباطات المستمرة، يُؤكد فترة التقارير التالية والمهام المعلقة. أما المهام الفردية فتُوثَّق الأعمال المنجزة ومواد التسليم.</p>'],
      ],
    ],
  ],
  'documents' => [
    'match' => ['Documents we may need'],
    'en' => [
      'title' => 'Documents we may need',
      'lede' => '',
      'items' => [
        ['title' => '', 'body' => '<ul><li>Company and <a href="/platforms/zatca">tax registration</a> details.</li><li>Existing accounting records and financial statements.</li><li>Invoices, bank statements and expense records.</li><li>Depending on the service: <a href="/services/payroll-management-services-saudi-arabia">payroll</a> records, <a href="/services/inventory-stock-audit">inventory</a> lists or previous <a href="/services/vat-filing-services-saudi-arabia">tax returns</a>.</li></ul>'],
        ['title' => '', 'body' => '<p>Incomplete records do not preclude a consultation. Outstanding items are confirmed in writing.</p>'],
      ],
    ],
    'ar' => [
      'title' => 'مستندات قد نحتاجها',
      'lede' => '',
      'items' => [
        ['title' => '', 'body' => '<ul><li>بيانات تسجيل المنشأة و<a href="/platforms/zatca">الضرائب</a>.</li><li>السجلات المحاسبية والقوائم المالية الحالية.</li><li>الفواتير وكشوف الحساب البنكية وسجلات المصروفات.</li><li>بحسب الخدمة: سجلات <a href="/services/payroll-management-services-saudi-arabia">الرواتب</a> أو قوائم <a href="/services/inventory-stock-audit">المخزون</a> أو <a href="/services/vat-filing-services-saudi-arabia">الإقرارات الضريبية</a> السابقة.</li></ul>'],
        ['title' => '', 'body' => '<p>السجلات غير المكتملة لا تحول دون طلب استشارة. تُؤكد البنود الناقصة كتابةً.</p>'],
      ],
    ],
  ],
  'fees' => [
    'match' => ['Fees and quotation'],
    'en' => [
      'title' => 'Fees and quotation',
      'lede' => '<p>A quotation may be requested for a one-off assignment or for ongoing bookkeeping support. The quotation states the work ordered and the fee, for agreement before work begins.</p>',
      'items' => [
        ['title' => 'The quotation takes into account', 'body' => '<ul><li>The volume and period of work.</li><li>The condition of existing records.</li><li>The services required.</li></ul>'],
        ['title' => 'The quotation includes', 'body' => '<ul><li>The agreed work.</li><li>The expected results.</li><li>The timeline.</li><li>The fee.</li><li>Any separate costs.</li></ul><p>Taxes, <a href="/services/zakat">zakat</a> and any third-party payments are separate from <a href="/about-us">Motaded</a>’s fee.</p>', 'link_title' => 'Request a Quotation'],
      ],
    ],
    'ar' => [
      'title' => 'الرسوم وعرض السعر',
      'lede' => '<p>يمكن طلب عرض سعر لمهمة لمرة واحدة أو لدعم مستمر في مسك الدفاتر. يبيّن عرض السعر العمل المطلوب وأتعاب الخدمة للاتفاق قبل بدء العمل.</p>',
      'items' => [
        ['title' => 'يراعي عرض السعر', 'body' => '<ul><li>حجم العمل والفترة.</li><li>حالة السجلات الحالية.</li><li>الخدمات المطلوبة.</li></ul>'],
        ['title' => 'يشمل عرض السعر', 'body' => '<ul><li>العمل المتفق عليه.</li><li>النتائج المتوقعة.</li><li>المدة.</li><li>أتعاب الخدمة.</li><li>أي تكاليف منفصلة.</li></ul><p>الضرائب و<a href="/services/zakat">الزكاة</a> وأي مدفوعات لأطراف ثالثة منفصلة عن أتعاب <a href="/about-us">متعدد</a>.</p>', 'link_title' => 'طلب عرض سعر'],
      ],
    ],
  ],
  'form' => [
    'en' => '<h2>Request a Consultation</h2>',
    'ar' => '<h2>طلب استشارة</h2>',
  ],
  'faq' => [
    'en' => [
      ['title' => 'Does the service cover a newly established company?', 'body' => '<p>Accounting setup, a chart of accounts and processes for invoices, expenses and reporting may be organised. <a href="/services/vat-registration-services-saudi-arabia">Tax registration</a> guidance may be included where required.</p>'],
      ['title' => 'Can an ongoing bookkeeping engagement be arranged?', 'body' => '<p>Ongoing bookkeeping, reconciliations, financial reporting and related <a href="/services/vat-filing-services-saudi-arabia">filing support</a> may be arranged. Frequency and deliverables are agreed in the proposal.</p>'],
      ['title' => 'How should incomplete or overdue records be addressed?', 'body' => '<p>The affected periods and the records already held should be stated. Gaps are assessed and <a href="/services/accounting-catch-services-saudi-arabia">catch-up work</a> is proposed, including any dependencies affecting reporting or filing.</p>'],
      ['title' => 'Can Motaded take over from the current accountant?', 'body' => '<p>Handover requirements and available records are reviewed. Opening balances, previous filings, outstanding work and subsequent responsibilities are established.</p>'],
      ['title' => 'Can a single service be requested?', 'body' => '<p>A specific service may be requested, such as an <a href="/services/accounts-review-services-saudi-arabia">accounts review</a>, <a href="/services/vat-filing-services-saudi-arabia">VAT return</a> preparation, <a href="/services/inventory-stock-audit">inventory audit</a> or <a href="/services/external-audit">audit preparation</a>.</p>'],
      ['title' => 'How are applicable tax services identified?', 'body' => '<p>Company structure, activities and transactions are reviewed to identify the relevant work. Applicable requirements should be checked against current <a href="/platforms/zatca">ZATCA</a> rules and guidance.</p>'],
      ['title' => 'What is the difference between audit preparation and an external audit?', 'body' => '<p>Audit preparation involves organising financial statements, records and supporting schedules. An independent <a href="/services/external-audit">external audit</a> is a separate engagement, and the audit opinion is issued by the appointed licensed auditor. The written proposal clarifies <a href="/about-us">Motaded</a>’s role.</p>'],
      ['title' => 'How is the fee determined?', 'body' => '<p>The fee depends on the volume and complexity of the work, the condition of the records and the required deliverables. Scope and fee are confirmed in writing before work begins.</p>'],
    ],
    'ar' => [
      ['title' => 'هل تشمل الخدمة المنشآت حديثة التأسيس؟', 'body' => '<p>يمكن تنظيم الإعداد المحاسبي ودليل الحسابات وإجراءات الفواتير والمصروفات والتقارير. يمكن تضمين إرشاد <a href="/services/vat-registration-services-saudi-arabia">تسجيل الضرائب</a> عند الحاجة.</p>'],
      ['title' => 'هل يمكن ترتيب ارتباط مستمر لمسك الدفاتر؟', 'body' => '<p>يمكن ترتيب مسك الدفاتر المستمر والتسويات والتقارير المالية و<a href="/services/vat-filing-services-saudi-arabia">دعم التقديم</a> المرتبط بذلك. يُتفق على التكرار والمخرجات في العرض.</p>'],
      ['title' => 'كيف تُعالج السجلات غير المكتملة أو المتأخرة؟', 'body' => '<p>تُذكر الفترات المتأثرة والسجلات المتوفرة. تُقيَّم الفجوات ويُقترح <a href="/services/accounting-catch-services-saudi-arabia">عمل استدراك</a>، بما في ذلك أي ارتباطات تؤثر على التقارير أو التقديم.</p>'],
      ['title' => 'هل يمكن لمتعدد تولي العمل من المحاسب الحالي؟', 'body' => '<p>تُراجع متطلبات التسليم والسجلات المتوفرة. تُثبت الأرصدة الافتتاحية والإقرارات السابقة والعمل المعلّق والمسؤوليات اللاحقة.</p>'],
      ['title' => 'هل يمكن طلب خدمة واحدة فقط؟', 'body' => '<p>يمكن طلب خدمة محددة، مثل <a href="/services/accounts-review-services-saudi-arabia">مراجعة الحسابات</a> أو إعداد <a href="/services/vat-filing-services-saudi-arabia">إقرار VAT</a> أو <a href="/services/inventory-stock-audit">تدقيق المخزون</a> أو <a href="/services/external-audit">الإعداد للتدقيق</a>.</p>'],
      ['title' => 'كيف تُحدد الخدمات الضريبية المنطبقة؟', 'body' => '<p>يُراجع هيكل المنشأة وأنشطتها ومعاملاتها لتحديد العمل ذي الصلة. ينبغي التحقق من المتطلبات المنطبقة وفق قواعد وإرشادات <a href="/platforms/zatca">ZATCA</a> الحالية.</p>'],
      ['title' => 'ما الفرق بين الإعداد للتدقيق والتدقيق الخارجي؟', 'body' => '<p>يشمل الإعداد للتدقيق تنظيم القوائم المالية والسجلات والجداول الداعمة. أما <a href="/services/external-audit">التدقيق الخارجي</a> المستقل فارتباط منفصل، ويصدر رأي التدقيق عن المدقق المرخّص المعيّن. يوضح العرض المكتوب دور <a href="/about-us">متعدد</a>.</p>'],
      ['title' => 'كيف تُحدد الأتعاب؟', 'body' => '<p>تعتمد الأتعاب على حجم العمل وتعقيده وحالة السجلات والمخرجات المطلوبة. يُؤكد النطاق وأتعاب الخدمة كتابةً قبل بدء العمل.</p>'],
    ],
  ],
];

$storage = FieldStorageConfig::loadByName('paragraph', 'field_card_type');
if ($storage) {
  $allowed = $storage->getSetting('allowed_values') ?: [];
  if ($allowed !== [] && array_is_list($allowed)) {
    $keyed = [];
    foreach ($allowed as $item) {
      if (is_array($item) && isset($item['value'])) {
        $keyed[(string) $item['value']] = (string) ($item['label'] ?? $item['value']);
      }
    }
    $allowed = $keyed;
  }
  if (!isset($allowed['formats'])) {
    $allowed['formats'] = 'Service: engagement formats';
    $storage->setSetting('allowed_values', $allowed);
    $storage->save();
  }
}

$aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
  'alias' => '/services/accounting-services-saudi-arabia',
  'langcode' => 'en',
]);
$alias = $aliases ? reset($aliases) : NULL;
if (!$alias instanceof PathAlias) {
  throw new \RuntimeException('Accounting landing alias not found.');
}
$nid = (int) str_replace('/node/', '', $alias->getPath());
$node = Node::load($nid);
if (!$node instanceof Node) {
  throw new \RuntimeException('Accounting landing node not found.');
}

foreach (['en', 'ar'] as $langcode) {
  if (!$node->hasTranslation($langcode) && $langcode === 'ar') {
    continue;
  }
  $tr = $node->hasTranslation($langcode) ? $node->getTranslation($langcode) : $node;
  $tr->set('field_meta', json_encode($copy['meta'][$langcode], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
  $tr->save();
}

$updated = ['meta'];
$source = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
$refs = [];
$context_p = NULL;
$formats_p = NULL;
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $bundle = $paragraph->bundle();
  $en_p = $paragraph->hasTranslation('en') ? $paragraph->getTranslation('en') : $paragraph;
  $title = $en_p->hasField('field_title') ? trim((string) $en_p->get('field_title')->value) : '';
  $type = $en_p->hasField('field_card_type') && !$en_p->get('field_card_type')->isEmpty()
    ? (string) $en_p->get('field_card_type')->value
    : '';
  if ($bundle === 'cards' && ($type === 'context' || in_array($title, $copy['context']['match'], TRUE))) {
    $context_p = $paragraph;
  }
  if ($bundle === 'cards' && ($type === 'formats' || in_array($title, $copy['formats']['match'], TRUE))) {
    $formats_p = $paragraph;
  }
  $refs[] = [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];

  if ($bundle === 'hero_split_banner') {
    foreach ($copy['hero'] as $langcode => $row) {
      $tr = $paragraph->hasTranslation($langcode)
        ? $paragraph->getTranslation($langcode)
        : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
      $tr->set('field_hero_split_eyebrow', $row['eyebrow']);
      $tr->set('field_hero_split_headline_prefix', $row['prefix']);
      $tr->set('field_hero_split_headline_accent', $row['accent']);
      $tr->set('field_body', [
        'value' => $row['body'],
        'format' => 'basic_html',
      ]);
      $link = $tr->get('field_link')->getValue();
      if ($link) {
        $link[0]['title'] = $row['cta'];
        $tr->set('field_link', $link);
      }
      if ($tr->hasField('field_link_secondary')) {
        $secondary = $tr->get('field_link_secondary')->getValue();
        if ($secondary) {
          $secondary[0]['title'] = $row['secondary'];
          $tr->set('field_link_secondary', $secondary);
        }
        else {
          $tr->set('field_link_secondary', [
            'uri' => 'internal:#catalogue',
            'title' => $row['secondary'],
          ]);
        }
      }
      $tr->save();
    }
    $updated[] = 'hero';
    continue;
  }
  if ($bundle === 'view_block') {
    foreach ($copy['catalogue'] as $langcode => $html) {
      $tr = $paragraph->hasTranslation($langcode)
        ? $paragraph->getTranslation($langcode)
        : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
      $tr->set('field_body', [
        'value' => $html,
        'format' => 'full_html',
      ]);
      $tr->save();
    }
    $updated[] = 'catalogue';
    continue;
  }
  if ($bundle === 'webform') {
    foreach ($copy['form'] as $langcode => $html) {
      $tr = $paragraph->hasTranslation($langcode)
        ? $paragraph->getTranslation($langcode)
        : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
      $tr->set('field_body', [
        'value' => $html,
        'format' => 'full_html',
      ]);
      $tr->save();
    }
    $updated[] = 'form';
    continue;
  }
  if ($bundle === 'accordions') {
    $i = 0;
    foreach ($paragraph->get('field_paragraphs') as $child_item) {
      $card = $child_item->entity;
      if (!$card instanceof ParagraphInterface || !isset($copy['faq']['en'][$i])) {
        continue;
      }
      foreach (['en', 'ar'] as $langcode) {
        $row = $copy['faq'][$langcode][$i] ?? $copy['faq']['en'][$i];
        $card_tr = $card->hasTranslation($langcode)
          ? $card->getTranslation($langcode)
          : $card->addTranslation($langcode, ['status' => $card->isPublished()]);
        $card_tr->set('field_title', $row['title']);
        $card_tr->set('field_body', [
          'value' => $row['body'],
          'format' => 'basic_html',
        ]);
        $card_tr->save();
      }
      $i++;
    }
    $updated[] = 'faq';
    continue;
  }
  if ($bundle !== 'cards') {
    continue;
  }
  foreach (['context', 'needs', 'situations', 'formats', 'working', 'process', 'documents', 'fees'] as $key) {
    if (!in_array($title, $copy[$key]['match'], TRUE)) {
      continue;
    }
    if (!empty($copy[$key]['replace'])) {
      _motaded_accounting_tone_replace_cards($paragraph, $copy[$key]['en'], $copy[$key]['ar']);
    }
    else {
      _motaded_accounting_tone_apply_cards($paragraph, $copy[$key]['en'], $copy[$key]['ar']);
    }
    if (!empty($copy[$key]['type'])) {
      $paragraph->set('field_card_type', $copy[$key]['type']);
      $paragraph->save();
      if ($paragraph->hasTranslation('ar')) {
        $ar_p = $paragraph->getTranslation('ar');
        $ar_p->set('field_card_type', $copy[$key]['type']);
        $ar_p->save();
      }
    }
    $updated[] = $key;
    break;
  }
}

if (!$context_p instanceof ParagraphInterface) {
  $context_p = Paragraph::create([
    'type' => 'cards',
    'langcode' => 'en',
    'field_card_type' => 'context',
  ]);
  $context_p->save();
  _motaded_accounting_tone_replace_cards($context_p, $copy['context']['en'], $copy['context']['ar']);
  $context_p->set('field_card_type', 'context');
  $context_p->save();
  if ($context_p->hasTranslation('ar')) {
    $ar_p = $context_p->getTranslation('ar');
    $ar_p->set('field_card_type', 'context');
    $ar_p->save();
  }
  $updated[] = 'context-created';
}

if (!$formats_p instanceof ParagraphInterface) {
  $formats_p = Paragraph::create([
    'type' => 'cards',
    'langcode' => 'en',
    'field_card_type' => 'formats',
  ]);
  $formats_p->save();
  _motaded_accounting_tone_replace_cards($formats_p, $copy['formats']['en'], $copy['formats']['ar']);
  $formats_p->set('field_card_type', 'formats');
  $formats_p->save();
  if ($formats_p->hasTranslation('ar')) {
    $ar_p = $formats_p->getTranslation('ar');
    $ar_p->set('field_card_type', 'formats');
    $ar_p->save();
  }
  $updated[] = 'formats-created';
}

$context_p = Paragraph::load((int) $context_p->id());
$formats_p = Paragraph::load((int) $formats_p->id());
$context_ref = [
  'target_id' => $context_p->id(),
  'target_revision_id' => $context_p->getRevisionId(),
];
$formats_ref = [
  'target_id' => $formats_p->id(),
  'target_revision_id' => $formats_p->getRevisionId(),
];
$skip_ids = [
  (int) $context_p->id(),
  (int) $formats_p->id(),
];
$ordered = [];
$inserted_context = FALSE;
$inserted_formats = FALSE;
foreach ($refs as $ref) {
  $paragraph = Paragraph::load((int) $ref['target_id']);
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  if (in_array((int) $paragraph->id(), $skip_ids, TRUE)) {
    continue;
  }
  $ordered[] = [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
  if ($paragraph->bundle() === 'hero_split_banner') {
    $ordered[] = $context_ref;
    $inserted_context = TRUE;
  }
  if ($paragraph->bundle() === 'view_block') {
    $ordered[] = $formats_ref;
    $inserted_formats = TRUE;
  }
}
if (!$inserted_context) {
  array_unshift($ordered, $context_ref);
}
if (!$inserted_formats) {
  $ordered[] = $formats_ref;
}
$source->set('field_paragraphs', $ordered);
$source->save();
$updated[] = 'section-order';

$webform = Webform::load('accounting_assessment');
if ($webform instanceof Webform) {
  $elements = $webform->getElementsDecoded();
  if (isset($elements['actions'])) {
    $elements['actions']['#submit__label'] = 'Submit';
    $webform->setElements($elements);
    $webform->save();
    $updated[] = 'submit';
  }
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $nid]);
echo 'Updated accounting official tone on /node/' . $nid . ': ' . implode(', ', $updated) . "\n";

/**
 * @param array<string, mixed> $en
 * @param array<string, mixed> $ar
 */
function _motaded_accounting_tone_apply_cards(ParagraphInterface $paragraph, array $en, array $ar): void {
  foreach (['en' => $en, 'ar' => $ar] as $langcode => $row) {
    $tr = $paragraph->hasTranslation($langcode)
      ? $paragraph->getTranslation($langcode)
      : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
    $tr->set('field_title', $row['title']);
    if (($row['lede'] ?? '') !== '') {
      $tr->set('field_body', [
        'value' => $row['lede'],
        'format' => 'basic_html',
      ]);
    }
    else {
      $tr->set('field_body', []);
    }
    $tr->save();
  }

  $i = 0;
  foreach ($paragraph->get('field_paragraphs') as $item) {
    $card = $item->entity;
    if (!$card instanceof ParagraphInterface || !isset($en['items'][$i])) {
      continue;
    }
    foreach (['en' => $en['items'][$i], 'ar' => $ar['items'][$i] ?? $en['items'][$i]] as $langcode => $row) {
      $card_tr = $card->hasTranslation($langcode)
        ? $card->getTranslation($langcode)
        : $card->addTranslation($langcode, ['status' => $card->isPublished()]);
      $card_tr->set('field_title', $row['title']);
      $card_tr->set('field_body', [
        'value' => $row['body'] ?? '',
        'format' => 'basic_html',
      ]);
      if (isset($row['link_title']) && $card_tr->hasField('field_link') && !$card_tr->get('field_link')->isEmpty()) {
        $link = $card_tr->get('field_link')->getValue();
        $link[0]['title'] = $row['link_title'];
        $card_tr->set('field_link', $link);
      }
      $card_tr->save();
    }
    $i++;
  }
}

/**
 * @param array<string, mixed> $en
 * @param array<string, mixed> $ar
 */
function _motaded_accounting_tone_replace_cards(ParagraphInterface $parent, array $en, array $ar): void {
  $old = $parent->get('field_paragraphs')->referencedEntities();
  $children = [];
  foreach ($en['items'] as $i => $item) {
    $values = [
      'type' => 'card',
      'langcode' => 'en',
      'field_title' => $item['title'],
      'field_body' => [
        'value' => $item['body'] ?? '',
        'format' => 'basic_html',
      ],
    ];
    if (($item['uri'] ?? '') !== '') {
      $values['field_link'] = [
        'uri' => $item['uri'],
        'title' => ($item['link_title'] ?? '') !== '' ? $item['link_title'] : $item['title'],
      ];
    }
    $card = Paragraph::create($values);
    $card->save();
    $ar_item = $ar['items'][$i] ?? $item;
    $card_ar = $card->addTranslation('ar', ['status' => $card->isPublished()]);
    $card_ar->set('field_title', $ar_item['title']);
    $card_ar->set('field_body', [
      'value' => $ar_item['body'] ?? '',
      'format' => 'basic_html',
    ]);
    if (($ar_item['uri'] ?? '') !== '') {
      $card_ar->set('field_link', [
        'uri' => $ar_item['uri'],
        'title' => ($ar_item['link_title'] ?? '') !== '' ? $ar_item['link_title'] : $ar_item['title'],
      ]);
    }
    $card_ar->save();
    $children[] = [
      'target_id' => $card->id(),
      'target_revision_id' => $card->getRevisionId(),
    ];
  }

  foreach (['en' => $en, 'ar' => $ar] as $langcode => $row) {
    $tr = $parent->hasTranslation($langcode)
      ? $parent->getTranslation($langcode)
      : $parent->addTranslation($langcode, ['status' => $parent->isPublished()]);
    $tr->set('field_title', $row['title']);
    if (($row['lede'] ?? '') !== '') {
      $tr->set('field_body', [
        'value' => $row['lede'],
        'format' => 'basic_html',
      ]);
    }
    else {
      $tr->set('field_body', []);
    }
    $tr->set('field_paragraphs', $children);
    $tr->save();
  }

  foreach ($old as $child) {
    $child->delete();
  }
}
