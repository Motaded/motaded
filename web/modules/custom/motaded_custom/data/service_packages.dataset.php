<?php

/**
 * @file
 * Service package landing pages (RHQ, Office Solutions, Entrepreneur License).
 */

declare(strict_types=1);

/** UUID of the top-level "Pricing" item in the main menu. */
const MOTADED_PRICING_MENU_PARENT_UUID = '03533a6d-9a01-4ad9-9507-32234aa4e4a9';

return [
  'entrepreneur_license_package' => [
    'existing_nid' => 991,
    'alias_en' => '/entrepreneur-license-package',
    'alias_ar' => '/entrepreneur-license-package',
    'layout' => 'pricing',
    'pricing_card_count' => 1,
    'menu' => [
      'title_en' => 'Entrepreneur License Package',
      'title_ar' => 'باقة ترخيص ريادة الأعمال',
      'weight' => -53,
    ],
    'en' => [
      'title' => 'Entrepreneur License Package',
      'banner_title' => 'Entrepreneur License Package',
      'banner_subtitle' => 'MISA Entrepreneur License for innovative startups in Saudi Arabia',
      'banner_sm' => [
        'title' => 'Get Started',
        'body' => 'To start your Entrepreneur License application, schedule a consultation with our team. We will review your business idea, confirm activity eligibility, prepare the Support Letter, and submit your application to MISA — all under one roof.',
        'cta_label' => 'Contact us',
        'cta_uri' => 'internal:/contact-us',
      ],
      'pricing_cards' => [
        [
          'title' => 'Entrepreneur License Package',
          'cost' => 'SAR 5,000',
          'subtitle' => 'One-time setup + SAR 3,000/month virtual office (12 months)',
          'cta_label' => 'Start Entrepreneur License',
          'cta_uri' => 'internal:/contact-us',
        ],
      ],
      'faq' => [
        [
          'question' => 'Why does Motaded issue the Support Letter directly?',
          'answer' => 'Motaded holds a Business Incubator license from the relevant Saudi authority. This positions us among the approved supporting entities recognized by MISA to issue Support Letters for the Entrepreneur License. Founders working with Motaded skip the step of seeking an external incubator endorsement.',
        ],
        [
          'question' => 'Why is the Virtual Office mandatory for 12 months?',
          'answer' => 'The Virtual Office is not merely a registered address — it is the functional operational base required for the entrepreneur license framework. It ensures your startup has a stable operational anchor during the critical first year of license activation. The 12-month commitment aligns with the license validity period.',
        ],
        [
          'question' => 'Are MISA government fees included?',
          'answer' => 'Yes. The SAR 5,000 setup fee includes all MISA government fees associated with the Entrepreneur License application. Currently, MISA fees from the 9th edition Service Manual (2022) are suspended under ministry review. When MISA fees resume, they remain included in this package.',
        ],
        [
          'question' => 'Is VAT included in the price?',
          'answer' => 'No. The SAR 5,000 setup fee and SAR 3,000 monthly virtual office fee exclude 15% VAT. VAT will be added to each invoice at the time of billing.',
        ],
        [
          'question' => 'How long is the Entrepreneur License valid?',
          'answer' => 'The license is valid for 1 year and is renewable annually. The annual renewal requires updated data, continued compliance, and payment of the applicable renewal fees as determined by MISA.',
        ],
        [
          'question' => 'Can I convert to a regular MISA license later?',
          'answer' => 'Yes. As your startup matures, you can transition from the Entrepreneur License to a standard MISA Investment Registration. Motaded supports this transition through the regular Business Setup Packages.',
        ],
        [
          'question' => 'Do I need an existing company in my home country?',
          'answer' => 'No. Unlike standard foreign investment licenses, the Entrepreneur License does not require a parent company. Individual founders can apply directly. If the applicant is an existing company, a board resolution is required.',
        ],
      ],
    ],
    'ar' => [
      'title' => 'باقة ترخيص ريادة الأعمال',
      'banner_title' => 'باقة ترخيص ريادة الأعمال',
      'banner_subtitle' => 'ترخيص ريادة الأعمال من وزارة الاستثمار للشركات الناشئة المبتكرة في السعوديّة',
      'banner_sm' => [
        'title' => 'ابدأ معنا',
        'body' => 'للبدء طلب ترخيص ريادة الأعمال، احجز جلسة استشاريّة مع فريقنا. سنراجع فكرة العمل، نؤكّد أهليّة النشاط، نُعدّ خطاب الدعم، ونقدّم الطلب إلى وزارة الاستثمار — كلّ ذلك تحت سقف واحد.',
        'cta_label' => 'تواصل معنا',
        'cta_uri' => 'internal:/contact-us',
      ],
      'pricing_cards' => [
        [
          'title' => 'باقة ترخيص ريادة الأعمال',
          'cost' => '٥,٠٠٠ ريال',
          'subtitle' => 'رسوم تأسيس لمرّة واحدة + ٣,٠٠٠ ريال/شهر للمكتب الافتراضيّ (١٢ شهراً)',
          'cta_label' => 'ابدأ ترخيص ريادة الأعمال',
          'cta_uri' => 'internal:/contact-us',
        ],
      ],
      'faq' => [
        [
          'question' => 'لماذا تُصدر متعدد خطاب الدعم مباشرةً؟',
          'answer' => 'متعدد تمتلك ترخيص حاضنة أعمال من الجهة السعوديّة المختصّة. هذا يضعنا ضمن الجهات الداعمة المعتمدة المعترف بها من وزارة الاستثمار لإصدار خطابات الدعم لترخيص ريادة الأعمال. المؤسّسون الذين يعملون مع متعدد يتجاوزون خطوة البحث عن تأييد من حاضنة خارجيّة.',
        ],
        [
          'question' => 'لماذا المكتب الافتراضيّ إلزاميّ لمدّة ١٢ شهراً؟',
          'answer' => 'المكتب الافتراضيّ ليس مجرّد عنوان مسجَّل — إنّه القاعدة التشغيليّة الوظيفيّة المطلوبة لإطار ترخيص ريادة الأعمال. يضمن للشركة الناشئة وجود مرتكز تشغيليّ ثابت خلال السنة الأولى الحرجة من تفعيل الترخيص. التزام الـ ١٢ شهراً يتوافق مع فترة صلاحيّة الترخيص.',
        ],
        [
          'question' => 'هل الرسوم الحكوميّة لوزارة الاستثمار مشمولة؟',
          'answer' => 'نعم. رسوم التأسيس البالغة ٥,٠٠٠ ريال تشمل جميع الرسوم الحكوميّة لوزارة الاستثمار المرتبطة بطلب ترخيص ريادة الأعمال. حاليّاً، رسوم وزارة الاستثمار في دليل الخدمات الإصدار التاسع (٢٠٢٢) معلَّقة تحت مراجعة الوزارة. عند استئناف الرسوم، تبقى مشمولة في هذه الباقة.',
        ],
        [
          'question' => 'هل ضريبة القيمة المضافة مشمولة في السعر؟',
          'answer' => 'لا. رسوم التأسيس ٥,٠٠٠ ريال ورسوم المكتب الافتراضيّ الشهريّة ٣,٠٠٠ ريال لا تشمل ضريبة القيمة المضافة ١٥٪. تُضاف الضريبة على كلّ فاتورة وقت الفوترة.',
        ],
        [
          'question' => 'ما مدّة صلاحيّة ترخيص ريادة الأعمال؟',
          'answer' => 'الترخيص صالح لمدّة سنة واحدة وقابل للتجديد سنويّاً. التجديد السنويّ يتطلَّب تحديث البيانات، الامتثال المستمرّ، ودفع رسوم التجديد المطبَّقة كما تحدّدها وزارة الاستثمار.',
        ],
        [
          'question' => 'هل يمكنني التحويل لترخيص استثمار قياسيّ لاحقاً؟',
          'answer' => 'نعم. مع نضوج الشركة الناشئة، يمكن التحويل من ترخيص ريادة الأعمال إلى تسجيل استثمار قياسيّ من وزارة الاستثمار. متعدد تدعم هذا التحويل عبر باقات تأسيس الشركات العاديّة.',
        ],
        [
          'question' => 'هل أحتاج شركة قائمة في بلدي؟',
          'answer' => 'لا. بخلاف تراخيص الاستثمار الأجنبيّ القياسيّة، لا يتطلَّب ترخيص ريادة الأعمال شركة أمّ. المؤسّسون الأفراد يمكنهم التقديم مباشرةً. إذا كان مقدّم الطلب شركة قائمة، يُطلَب قرار من مجلس الإدارة.',
        ],
      ],
    ],
  ],
  'rhq_package' => [
    'alias_en' => '/rhq-package',
    'alias_ar' => '/rhq-package',
    'layout' => 'pricing',
    'pricing_card_count' => 1,
    'menu' => [
      'title_en' => 'RHQ Package',
      'title_ar' => 'باقة المقرّ الإقليميّ (RHQ)',
      'weight' => -52,
    ],
    'en' => [
      'title' => 'Regional Headquarters (RHQ) Package',
      'banner_title' => 'Regional Headquarters (RHQ) Package',
      'banner_subtitle' => 'Complete RHQ license and company formation for multinational corporations in Saudi Arabia',
      'banner_sm' => [
        'title' => 'Get Started',
        'body' => 'To proceed with RHQ setup or request a strategic consultation, schedule a meeting with our team. We will review your parent group structure, target activities, and timeline to confirm eligibility and define the activation roadmap.',
        'cta_label' => 'Contact us',
        'cta_uri' => 'internal:/contact-us',
      ],
      'pricing_cards' => [
        [
          'title' => 'RHQ Package — All-Inclusive',
          'cost' => 'SAR 125,000',
          'subtitle' => 'One-time fee',
          'cta_label' => 'Request RHQ proposal',
          'cta_uri' => 'internal:/contact-us',
        ],
      ],
      'faq' => [
        [
          'question' => 'Are all government fees included in the SAR 125,000 price?',
          'answer' => 'Yes. The package is end-to-end and covers all government fees for the services listed, including MISA fees. Currently, MISA fees published in the 9th edition of the MISA Service Manual (2022) are suspended under ministry review with no official announcement of new fees. When MISA fees resume, they remain included in this package price.',
        ],
        [
          'question' => 'Is VAT included in the price?',
          'answer' => 'No. The price excludes 15% VAT. VAT will be added to the invoice at the time of billing.',
        ],
        [
          'question' => 'Can the RHQ engage in commercial activities (buying and selling)?',
          'answer' => 'No. The RHQ license is restricted to RHQ activities — strategic direction, management, and the optional activities approved by MISA. For commercial operations, a separate legal entity with its own license is required. Motaded can support setting up the operating entity in parallel.',
        ],
        [
          'question' => 'What is the parent company eligibility requirement?',
          'answer' => 'The parent multinational group must have active operations in at least two countries other than Saudi Arabia and the country of its main headquarters. This is documented through copies of commercial registrations or licenses from those two qualifying countries.',
        ],
        [
          'question' => 'What documents are required from the investor?',
          'answer' => 'Required documents: parent company commercial registration, commercial registrations from two qualifying countries, latest audited consolidated financial statements, business and activation plan, and a power of attorney for Motaded — all attested by the Saudi embassy in the country of origin or via apostille.',
        ],
        [
          'question' => 'Does the RHQ require a physical office in Saudi Arabia?',
          'answer' => 'Yes. The RHQ must have a physical office in Saudi Arabia for the actual practice of management activities. Motaded provides a virtual address for the first year as part of the package, but the client must secure a physical office before the 12-month activation deadline. Motaded supports physical office search and leasing through separate agreements.',
        ],
        [
          'question' => 'What happens if the RHQ does not activate mandatory activities on time?',
          'answer' => 'MISA conducts an annual evaluation. If mandatory activities are not activated within 6 months or 3 optional activities within 12 months, MISA may issue warnings, suspend incentives, or revoke the license. Motaded provides activation plan support during the first 90 days to ensure compliance.',
        ],
      ],
    ],
    'ar' => [
      'title' => 'باقة المقرّ الإقليميّ (RHQ)',
      'banner_title' => 'باقة المقرّ الإقليميّ (RHQ)',
      'banner_subtitle' => 'ترخيص المقرّ الإقليميّ وتأسيس الشركة الكامل للشركات متعدّدة الجنسيّات في السعوديّة',
      'banner_sm' => [
        'title' => 'ابدأ معنا',
        'body' => 'للمضيّ قدماً في تأسيس RHQ أو طلب استشارة استراتيجيّة، احجز جلسة مع فريقنا. سنراجع هيكل مجموعتك الأمّ، الأنشطة المستهدفة، والجدول الزمنيّ لتأكيد الأهليّة وتحديد خارطة طريق التفعيل.',
        'cta_label' => 'تواصل معنا',
        'cta_uri' => 'internal:/contact-us',
      ],
      'pricing_cards' => [
        [
          'title' => 'باقة المقرّ الإقليميّ — شاملة',
          'cost' => '١٢٥,٠٠٠ ريال',
          'subtitle' => 'رسوم لمرّة واحدة',
          'cta_label' => 'اطلب عرض RHQ',
          'cta_uri' => 'internal:/contact-us',
        ],
      ],
      'faq' => [
        [
          'question' => 'هل جميع الرسوم الحكوميّة مشمولة في سعر ١٢٥,٠٠٠ ريال؟',
          'answer' => 'نعم. الباقة شاملة من البداية للنهاية وتغطّي جميع الرسوم الحكوميّة للخدمات المدرجة، بما في ذلك رسوم MISA. حاليّاً، رسوم MISA المنشورة في دليل خدمات MISA الإصدار التاسع (٢٠٢٢) معلَّقة تحت مراجعة الوزارة بدون إعلان رسميّ للرسوم الجديدة. عند استئناف الرسوم، تبقى مشمولة في سعر هذه الباقة.',
        ],
        [
          'question' => 'هل ضريبة القيمة المضافة مشمولة في السعر؟',
          'answer' => 'لا. السعر لا يشمل ضريبة القيمة المضافة ١٥٪. تُضاف الضريبة على الفاتورة وقت الفوترة.',
        ],
        [
          'question' => 'هل يمكن للمقرّ الإقليميّ ممارسة أنشطة تجاريّة (بيع وشراء)؟',
          'answer' => 'لا. رخصة RHQ مقتصرة على أنشطة RHQ — التوجيه الاستراتيجيّ والإدارة والأنشطة الاختياريّة المعتمدة من MISA. للعمليّات التجاريّة، يلزم كيان قانونيّ مستقلّ برخصته الخاصّة. متعدد يمكنها دعم تأسيس الكيان التشغيليّ بالتوازي.',
        ],
        [
          'question' => 'ما شرط أهليّة الشركة الأمّ؟',
          'answer' => 'على الشركة الأمّ متعدّدة الجنسيّات أن تكون لديها عمليّات نشطة في دولتين على الأقلّ غير السعوديّة ودولة مقرّها الرئيسيّ. يُوثَّق ذلك بنسخ السجلّات التجاريّة أو التراخيص من تلك الدولتين المؤهَّلتين.',
        ],
        [
          'question' => 'ما المستندات المطلوبة من المستثمر؟',
          'answer' => 'المستندات المطلوبة: السجلّ التجاريّ للشركة الأمّ، السجلّات التجاريّة من دولتين مؤهَّلتين، أحدث القوائم الماليّة الموحَّدة المدقَّقة، خطّة العمل وخطّة التفعيل، ووكالة لشركة متعدد — جميعها مصدَّقة من السفارة السعوديّة في بلد المنشأ أو عبر أبوستيل.',
        ],
        [
          'question' => 'هل يتطلَّب RHQ مكتباً فعليّاً في السعوديّة؟',
          'answer' => 'نعم. يجب أن يكون لـ RHQ مكتب فعليّ في السعوديّة لممارسة أنشطة الإدارة الحقيقيّة. توفّر متعدد العنوان الافتراضيّ للسنة الأولى ضمن الباقة، لكن على العميل تأمين مكتب فعليّ قبل انتهاء مهلة التفعيل البالغة ١٢ شهراً. متعدد تدعم البحث عن المكتب الفعليّ والإيجار بعقود منفصلة.',
        ],
        [
          'question' => 'ماذا لو لم تُفعَّل الأنشطة الإلزاميّة في وقتها؟',
          'answer' => 'تجري MISA تقييماً سنويّاً. إذا لم تُفعَّل الأنشطة الإلزاميّة خلال ٦ أشهر أو ٣ أنشطة اختياريّة خلال ١٢ شهراً، يجوز لـ MISA إصدار إنذارات، تعليق الحوافز، أو إلغاء الرخصة. تقدّم متعدد دعم خطّة التفعيل خلال أوّل ٩٠ يوماً لضمان الامتثال.',
        ],
      ],
    ],
  ],
  'office_solutions_packages' => [
    'alias_en' => '/office-solutions-packages',
    'alias_ar' => '/office-solutions-packages',
    'layout' => 'pricing',
    'pricing_card_count' => 6,
    'menu' => [
      'title_en' => 'Office Solutions Packages',
      'title_ar' => 'باقات حلول المكاتب',
      'weight' => -51,
    ],
    'en' => [
      'title' => 'Office Solutions Packages',
      'banner_title' => 'Office Solutions Packages',
      'banner_subtitle' => 'Premium workspaces in Riyadh — flexible terms, no hidden fees',
      'banner_sm' => [
        'title' => 'Get Started',
        'body' => 'To reserve your workspace or request a tour, contact our team. We will help you choose the right plan and complete your setup.',
        'cta_label' => 'Contact us',
        'cta_uri' => 'internal:/contact-us',
      ],
      'pricing_cards' => [
        [
          'title' => 'Shared Office (Coworking)',
          'cost' => 'From SAR 1,800 / month',
          'subtitle' => 'Flexible terms from 10 days',
          'cta_label' => 'Book coworking',
          'cta_uri' => 'internal:/contact-us',
        ],
        [
          'title' => 'Private Office',
          'cost' => 'From SAR 9,500 / month',
          'subtitle' => 'Dedicated team space',
          'cta_label' => 'Book private office',
          'cta_uri' => 'internal:/contact-us',
        ],
        [
          'title' => 'Virtual Office',
          'cost' => 'From SAR 1,500 / month',
          'subtitle' => 'Registered business presence',
          'cta_label' => 'Get virtual office',
          'cta_uri' => 'internal:/contact-us',
        ],
        [
          'title' => 'Virtual Address',
          'cost' => 'From SAR 750 / month',
          'subtitle' => 'Address only',
          'cta_label' => 'Get virtual address',
          'cta_uri' => 'internal:/contact-us',
        ],
        [
          'title' => 'Meeting Room',
          'cost' => 'From SAR 150 / hour',
          'subtitle' => 'Up to 15 people',
          'cta_label' => 'Book meeting room',
          'cta_uri' => 'internal:/contact-us',
        ],
        [
          'title' => 'Conference Hall',
          'cost' => 'From SAR 45,000 / day',
          'subtitle' => 'Large-scale events',
          'cta_label' => 'Book conference hall',
          'cta_uri' => 'internal:/contact-us',
        ],
      ],
      'faq' => [
        [
          'question' => 'Where are the offices located?',
          'answer' => 'All Motaded office solutions are located in Al Sahafah, Riyadh — a premier business district with easy access to major roads and government entities.',
        ],
        [
          'question' => 'Is VAT included in the prices?',
          'answer' => 'No. All listed prices exclude 15% VAT. VAT will be added to each invoice at the time of billing.',
        ],
        [
          'question' => 'Can I use the virtual address for company registration?',
          'answer' => 'Yes. The virtual address can be used as your official business address for company registration with the Ministry of Commerce, MISA, and other government authorities.',
        ],
        [
          'question' => 'Can I switch between plans?',
          'answer' => 'Yes. You can upgrade or downgrade at any time. Upgrading from a Virtual Office or Virtual Address to a Private Office unlocks a 10% discount on your first contract.',
        ],
        [
          'question' => 'What are the access hours?',
          'answer' => 'Standard access is during regular working hours. Extended access can be arranged on request for private office tenants.',
        ],
        [
          'question' => 'Do you offer discounts for longer commitments?',
          'answer' => 'Yes. 2-month and 3-month contracts unlock a 10% discount. Annual contracts offer the best value with the largest savings.',
        ],
        [
          'question' => 'Is parking available?',
          'answer' => 'Yes. Parking is available for all tenants and visitors.',
        ],
        [
          'question' => 'Can I receive mail and packages?',
          'answer' => 'Yes. Mail and parcel receiving is included in all plans. You will be notified by email and SMS when items arrive. International shipping is available through our DHL Express partnership.',
        ],
      ],
    ],
    'ar' => [
      'title' => 'باقات حلول المكاتب',
      'banner_title' => 'باقات حلول المكاتب',
      'banner_subtitle' => 'مساحات عمل متميّزة في الرياض — شروط مرنة، بلا رسوم خفيّة',
      'banner_sm' => [
        'title' => 'ابدأ معنا',
        'body' => 'لحجز مساحة العمل أو طلب جولة، تواصل مع فريقنا. سنساعدك في اختيار الباقة المناسبة وإكمال الإعداد.',
        'cta_label' => 'تواصل معنا',
        'cta_uri' => 'internal:/contact-us',
      ],
      'pricing_cards' => [
        [
          'title' => 'المكتب المشترك (Coworking)',
          'cost' => 'من ١,٨٠٠ ريال / شهر',
          'subtitle' => 'شروط مرنة من ١٠ أيّام',
          'cta_label' => 'احجز مساحة مشتركة',
          'cta_uri' => 'internal:/contact-us',
        ],
        [
          'title' => 'المكتب الخاصّ',
          'cost' => 'من ٩,٥٠٠ ريال / شهر',
          'subtitle' => 'مساحة مخصّصة للفريق',
          'cta_label' => 'احجز مكتباً خاصّاً',
          'cta_uri' => 'internal:/contact-us',
        ],
        [
          'title' => 'المكتب الافتراضيّ',
          'cost' => 'من ١,٥٠٠ ريال / شهر',
          'subtitle' => 'حضور تجاريّ مسجَّل',
          'cta_label' => 'احصل على مكتب افتراضيّ',
          'cta_uri' => 'internal:/contact-us',
        ],
        [
          'title' => 'العنوان الافتراضيّ',
          'cost' => 'من ٧٥٠ ريال / شهر',
          'subtitle' => 'عنوان فقط',
          'cta_label' => 'احصل على عنوان افتراضيّ',
          'cta_uri' => 'internal:/contact-us',
        ],
        [
          'title' => 'قاعة الاجتماعات',
          'cost' => 'من ١٥٠ ريال / ساعة',
          'subtitle' => 'حتّى ١٥ شخصاً',
          'cta_label' => 'احجز قاعة اجتماعات',
          'cta_uri' => 'internal:/contact-us',
        ],
        [
          'title' => 'قاعة المؤتمرات',
          'cost' => 'من ٤٥,٠٠٠ ريال / يوم',
          'subtitle' => 'فعاليّات كبيرة',
          'cta_label' => 'احجز قاعة مؤتمرات',
          'cta_uri' => 'internal:/contact-us',
        ],
      ],
      'faq' => [
        [
          'question' => 'أين تقع المكاتب؟',
          'answer' => 'جميع حلول مكاتب متعدد تقع في حيّ الصحافة بالرياض — منطقة أعمال متميّزة مع وصول سهل إلى الطرق الرئيسيّة والجهات الحكوميّة.',
        ],
        [
          'question' => 'هل ضريبة القيمة المضافة مشمولة في الأسعار؟',
          'answer' => 'لا. جميع الأسعار المدرجة لا تشمل ضريبة القيمة المضافة ١٥٪. تُضاف الضريبة على كلّ فاتورة وقت الفوترة.',
        ],
        [
          'question' => 'هل يمكنني استخدام العنوان الافتراضيّ لتسجيل الشركة؟',
          'answer' => 'نعم. يمكن استخدام العنوان الافتراضيّ كعنوان تجاريّ رسميّ لتسجيل الشركة في وزارة التجارة، وزارة الاستثمار، والجهات الحكوميّة الأخرى.',
        ],
        [
          'question' => 'هل يمكنني التبديل بين الباقات؟',
          'answer' => 'نعم. يمكنك الترقية أو التخفيض في أيّ وقت. الترقية من مكتب افتراضيّ أو عنوان افتراضيّ إلى مكتب خاصّ تفتح خصم ١٠٪ على عقدك الأوّل.',
        ],
        [
          'question' => 'ما هي ساعات الوصول؟',
          'answer' => 'الوصول القياسيّ خلال ساعات العمل المعتادة. يمكن ترتيب وصول ممتدّ عند الطلب لمستأجري المكاتب الخاصّة.',
        ],
        [
          'question' => 'هل تقدّمون خصومات للالتزامات الأطول؟',
          'answer' => 'نعم. عقود الشهرين والثلاثة أشهر تفتح خصم ١٠٪. العقود السنويّة تقدّم أفضل قيمة بأكبر التوفيرات.',
        ],
        [
          'question' => 'هل تتوفّر مواقف سيّارات؟',
          'answer' => 'نعم. تتوفّر مواقف سيّارات لجميع المستأجرين والزوّار.',
        ],
        [
          'question' => 'هل يمكنني استلام البريد والطرود؟',
          'answer' => 'نعم. استلام البريد والطرود مشمول في جميع الباقات. سيتمّ إخطارك بالبريد الإلكترونيّ والرسائل النصّيّة عند وصول العناصر. الشحن الدوليّ متاح عبر شراكتنا مع DHL Express.',
        ],
      ],
    ],
  ],
];
