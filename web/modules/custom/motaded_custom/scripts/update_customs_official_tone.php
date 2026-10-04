<?php

/**
 * @file
 * Rewrites customs landing copy into official, SEO-oriented institutional tone.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_customs_official_tone.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

const MOTADED_CUSTOMS_TONE_ALIAS = '/services/customs-clearance-saudi-arabia';

$copy = [
  'meta' => [
    'en' => [
      'title' => 'Customs Clearance in Saudi Arabia | Motaded',
      'description' => 'Customs clearance in Saudi Arabia for import and export: document review, FASAH declaration, SABER and SFDA requirements where applicable, and follow-up to the release decision. Scope and service fee are agreed in writing before work begins.',
    ],
    'ar' => [
      'title' => 'التخليص الجمركي في المملكة العربية السعودية | Motaded',
      'description' => 'التخليص الجمركي في المملكة العربية السعودية للاستيراد والتصدير: مراجعة المستندات، البيان عبر فسح، متطلبات سابر والهيئة العامة للغذاء والدواء عند انطباقها، والمتابعة حتى قرار الإفراج. يُتفق على نطاق العمل وأتعاب الخدمة كتابةً قبل البدء.',
    ],
  ],
  'hero' => [
    'en' => [
      'eyebrow' => 'Customs services · Saudi Arabia',
      'prefix' => 'Customs Clearance',
      'accent' => 'in Saudi Arabia',
      'body' => '<p>Customs clearance support for import and export consignments in the Kingdom of Saudi Arabia, covering document review, FASAH declaration procedures and follow-up through to the authority’s release decision.</p>',
      'cta' => 'Request a Consultation',
    ],
    'ar' => [
      'eyebrow' => 'خدمات جمركية · المملكة العربية السعودية',
      'prefix' => 'التخليص الجمركي',
      'accent' => 'في المملكة العربية السعودية',
      'body' => '<p>دعم التخليص الجمركي لشحنات الاستيراد والتصدير في المملكة العربية السعودية، ويشمل مراجعة المستندات وإجراءات البيان عبر فسح والمتابعة حتى قرار الإفراج.</p>',
      'cta' => 'طلب استشارة',
    ],
  ],
  'overview' => [
    'match' => ['Customs clearance support for your shipment', 'Customs clearance for import and export consignments', 'Service Overview'],
    'en' => [
      'title' => 'Service Overview',
      'type' => 'overview',
      'replace' => TRUE,
      'lede' => '<p>Motaded provides customs clearance coordination for companies importing goods into Saudi Arabia or exporting goods from the Kingdom. Support covers shipment document review, coordination of customs declaration preparation, applicable product requirements and release follow-up.</p><p>The engagement is defined according to the goods, shipment route and current consignment status. The written quotation identifies the agreed work, responsibilities and service fees before work begins.</p>',
      'items' => [
        [
          'title' => 'Shipment Status',
          'body' => '<ul><li><strong>Before dispatch:</strong> review available documents and identify outstanding requirements.</li><li><strong>In transit:</strong> coordinate remaining documentation and clearance preparation.</li><li><strong>After arrival:</strong> review the current status and outstanding actions required for clearance.</li></ul>',
        ],
      ],
    ],
    'ar' => [
      'title' => 'نظرة عامة على الخدمة',
      'type' => 'overview',
      'lede' => '<p>يقدّم متعدد تنسيق التخليص الجمركي للمنشآت التي تستورد بضائع إلى المملكة العربية السعودية أو تصدّر بضائع منها. يشمل الدعم مراجعة مستندات الشحنة، وتنسيق إعداد البيان الجمركي، والمتطلبات المنطبقة على المنتج، ومتابعة الإفراج.</p><p>يُحدَّد نطاق الارتباط وفق نوع البضاعة ومسار الشحنة وحالتها الحالية. يبيّن عرض السعر المكتوب العمل المتفق عليه والمسؤوليات وأتعاب الخدمة قبل بدء العمل.</p>',
      'items' => [
        [
          'title' => 'حالة الشحنة',
          'body' => '<ul><li><strong>قبل الإرسال:</strong> مراجعة المستندات المتوفرة وتحديد المتطلبات المعلقة.</li><li><strong>في الطريق:</strong> تنسيق المستندات المتبقية وإعداد التخليص.</li><li><strong>بعد الوصول:</strong> مراجعة الحالة الحالية والإجراءات المتبقية اللازمة للتخليص.</li></ul>',
        ],
      ],
    ],
  ],
  'cargo' => [
    'match' => ['Does this cover your cargo?', 'Cargo categories for customs clearance', 'Cargo Type and Product Requirements'],
    'en' => [
      'title' => 'Cargo Type and Product Requirements',
      'type' => 'matrix',
      'replace' => TRUE,
      'headers' => ['Cargo category', 'What needs to be reviewed', 'Additional product support'],
      'lede' => '<p>Clearance preparation depends on the goods and the proposed customs procedure. Product descriptions, intended use and tariff classification help identify whether additional conformity documents, registrations or permits need to be considered.</p><p>The table below outlines the initial review for imports into Saudi Arabia. Export consignments are assessed separately against applicable Saudi export controls and destination-country requirements.</p>',
      'items' => [
        [
          'title' => 'General commercial goods',
          'subtitle' => 'Accurate descriptions, quantities, values, origin and consistency between shipment documents; applicable conformity requirements',
          'body' => '<p>Coordination of <a href="/platforms/saber">SABER</a> documentation where applicable. General goods should not be assumed to be exempt from product requirements.</p>',
        ],
        [
          'title' => 'Food products',
          'subtitle' => 'Product category, importer information, labelling and available certificates relevant to the consignment',
          'body' => '<p>Coordination of applicable <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> food documentation and clearance requirements.</p>',
        ],
        [
          'title' => 'Medicines and pharmaceutical products',
          'subtitle' => 'Product and establishment records, available authorisations and consignment documentation',
          'body' => '<p><a href="/services/medicine-services-saudi-arabia">Pharmaceutical regulatory and import support</a> where required.</p>',
        ],
        [
          'title' => 'Medical devices and supplies',
          'subtitle' => 'Device identification, intended use, available authorisation records and importer documentation',
          'body' => '<p><a href="/services/medical-device-services-saudi-arabia">Medical-device regulatory support</a> and coordination of applicable <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> shipment requirements.</p>',
        ],
        [
          'title' => 'Cosmetics and personal-care products',
          'subtitle' => 'Product identity, available listing records, manufacturer information and shipment documentation',
          'body' => '<p><a href="/services/cosmetics-services-saudi-arabia">Cosmetics listing and documentation support</a> where required.</p>',
        ],
        [
          'title' => 'Goods subject to specific controls',
          'subtitle' => 'Tariff classification, product characteristics, intended use and any restrictions or permit requirements',
          'body' => '<p>Coordination with the relevant authority or specialist provider within the agreed scope.</p>',
        ],
        [
          'title' => '',
          'body' => '<p>The consignment-specific review establishes which documents and procedures apply. Product registration, conformity assessment and permit applications are separate work items unless included in the quotation.</p>',
        ],
      ],
    ],
    'ar' => [
      'title' => 'نوع الشحنة ومتطلبات المنتج',
      'type' => 'matrix',
      'headers' => ['فئة الشحنة', 'ما يجب مراجعته', 'الدعم الإضافي للمنتج'],
      'lede' => '<p>يعتمد إعداد التخليص على البضاعة والإجراء الجمركي المقترح. تساعد أوصاف المنتج والاستخدام المقصود والتصنيف الجمركي على تحديد ما إذا كانت مستندات مطابقة أو تسجيلات أو تصاريح إضافية يلزم أخذها في الاعتبار.</p><p>يوضح الجدول أدناه المراجعة الأولية للاستيراد إلى المملكة العربية السعودية. تُقيَّم شحنات التصدير على حدة وفق ضوابط التصدير السعودية المنطبقة ومتطلبات بلد المقصد.</p>',
      'items' => [
        [
          'title' => 'بضائع تجارية عامة',
          'subtitle' => 'أوصاف دقيقة وكميات وقيم ومنشأ واتساق مستندات الشحنة؛ ومتطلبات المطابقة المنطبقة',
          'body' => '<p>تنسيق مستندات <a href="/platforms/saber">سابر</a> عند انطباقها. لا يُفترض أن البضائع العامة معفاة من متطلبات المنتج.</p>',
        ],
        [
          'title' => 'المنتجات الغذائية',
          'subtitle' => 'فئة المنتج وبيانات المستورد والبطاقات وأي شهادات متوفرة ذات صلة بالشحنة',
          'body' => '<p>تنسيق مستندات الغذاء ومتطلبات التخليص المنطبقة لدى <a href="/platforms/saudi-food-and-drug-authority-sfda">الهيئة العامة للغذاء والدواء</a>.</p>',
        ],
        [
          'title' => 'الأدوية والمستحضرات الصيدلانية',
          'subtitle' => 'سجلات المنتج والمنشأة والتراخيص المتوفرة ومستندات الشحنة',
          'body' => '<p><a href="/services/medicine-services-saudi-arabia">الدعم التنظيمي والاستيرادي للأدوية</a> عند الحاجة.</p>',
        ],
        [
          'title' => 'الأجهزة والمستلزمات الطبية',
          'subtitle' => 'تحديد الجهاز والاستخدام المقصود وسجلات الترخيص المتوفرة ومستندات المستورد',
          'body' => '<p><a href="/services/medical-device-services-saudi-arabia">الدعم التنظيمي للأجهزة الطبية</a> وتنسيق متطلبات شحن <a href="/platforms/saudi-food-and-drug-authority-sfda">الهيئة العامة للغذاء والدواء</a> المنطبقة.</p>',
        ],
        [
          'title' => 'مستحضرات التجميل والعناية الشخصية',
          'subtitle' => 'هوية المنتج وسجلات الإدراج المتوفرة وبيانات المصنّع ومستندات الشحنة',
          'body' => '<p><a href="/ar/services/cosmetics-services-saudi-arabia">دعم إدراج مستحضرات التجميل والمستندات</a> عند الحاجة.</p>',
        ],
        [
          'title' => 'بضائع تخضع لضوابط محددة',
          'subtitle' => 'التصنيف الجمركي وخصائص المنتج والاستخدام المقصود وأي قيود أو متطلبات تصريح',
          'body' => '<p>التنسيق مع الجهة المختصة أو مقدّم متخصص ضمن النطاق المتفق عليه.</p>',
        ],
        [
          'title' => '',
          'body' => '<p>تحدد المراجعة الخاصة بالشحنة المستندات والإجراءات المنطبقة. تسجيل المنتج وتقييم المطابقة وطلبات التصاريح بنود عمل منفصلة ما لم تُدرج في عرض السعر.</p>',
        ],
      ],
    ],
  ],
  'scope' => [
    'match' => ['What Motaded handles', 'Scope of Services', 'Customs Clearance Scope of Services'],
    'en' => [
      'title' => 'Customs Clearance Scope of Services',
      'type' => 'tabs',
      'replace' => TRUE,
      'lede' => '<p>Motaded coordinates the agreed customs clearance work between the client, supplier, appointed customs broker and relevant service providers. The scope covers document preparation, declaration coordination, product-related requirements and release follow-up.</p>',
      'items' => [
        [
          'title' => 'Shipment Document Preparation',
          'body' => '<p>Review the available invoice, packing list and transport documents for completeness and consistency. Identify missing information and coordinate corrections with the client or supplier before the documents are passed for declaration preparation.</p>',
        ],
        [
          'title' => 'Customs Declaration Coordination',
          'body' => '<p>Organise the shipment information and supporting documents required by the party preparing the customs declaration. Coordinate queries concerning product descriptions, quantities, values, origin and proposed tariff classification.</p>'
            . '<p>The declaration is submitted by the importer or its duly authorised customs broker, as applicable. The quotation identifies the submitting party and distinguishes Motaded’s coordination work from the broker’s declaration services.</p>',
        ],
        [
          'title' => 'Product Requirements Coordination',
          'body' => '<p>Identify product-related matters requiring further review and coordinate the agreed supporting documentation. Where relevant, this may involve conformity records, product registrations or permits.</p>'
            . '<p>Applications for product registration, conformity assessment or permits are included only where specified in the scope of work.</p>',
        ],
        [
          'title' => 'Clearance and Release Follow-up',
          'body' => '<p>Track available clearance updates and communicate requests for documents, clarification or client action. Coordinate the agreed responses with the broker and relevant providers, and report the documented release status.</p>'
            . '<p>Transport, storage and delivery arrangements are defined separately where required.</p>',
        ],
      ],
    ],
    'ar' => [
      'title' => 'نطاق خدمات التخليص الجمركي',
      'type' => 'tabs',
      'lede' => '<p>ينسّق متعدد أعمال التخليص الجمركي المتفق عليها بين العميل والمورّد والوسيط الجمركي المعيّن ومقدّمي الخدمات المعنيين. يشمل النطاق إعداد المستندات وتنسيق البيان والمتطلبات المتعلقة بالمنتج ومتابعة الإفراج.</p>',
      'items' => [
        [
          'title' => 'إعداد مستندات الشحنة',
          'body' => '<p>مراجعة الفاتورة وقائمة التعبئة ومستندات النقل المتوفرة من حيث الاكتمال والاتساق. تحديد المعلومات الناقصة وتنسيق التصحيحات مع العميل أو المورّد قبل إحالة المستندات لإعداد البيان.</p>',
        ],
        [
          'title' => 'تنسيق البيان الجمركي',
          'body' => '<p>تنظيم معلومات الشحنة والمستندات الداعمة التي يحتاجها الطرف المعدّ للبيان الجمركي. تنسيق الاستفسارات المتعلقة بأوصاف المنتج والكميات والقيم والمنشأ والتصنيف الجمركي المقترح.</p>'
            . '<p>يُقدَّم البيان من المستورد أو وسيطه الجمركي المفوض حسب الحال. يحدّد عرض السعر الجهة المقدِّمة ويميّز عمل التنسيق الذي يقوم به متعدد عن خدمات البيان لدى الوسيط.</p>',
        ],
        [
          'title' => 'تنسيق متطلبات المنتج',
          'body' => '<p>تحديد المسائل المتعلقة بالمنتج التي تستدعي مراجعة إضافية وتنسيق المستندات الداعمة المتفق عليها. قد يشمل ذلك عند الاقتضاء سجلات المطابقة أو تسجيلات المنتج أو التصاريح.</p>'
            . '<p>تُدرج طلبات تسجيل المنتج أو تقييم المطابقة أو التصاريح فقط إذا نُصّ عليها في نطاق العمل.</p>',
        ],
        [
          'title' => 'متابعة التخليص والإفراج',
          'body' => '<p>تتبع تحديثات التخليص المتوفرة وإبلاغ طلبات المستندات أو التوضيح أو إجراء العميل. تنسيق الردود المتفق عليها مع الوسيط ومقدّمي الخدمات المعنيين، والإبلاغ عن حالة الإفراج الموثّقة.</p>'
            . '<p>تُحدَّد ترتيبات النقل والتخزين والتسليم على حدة عند الحاجة.</p>',
        ],
      ],
    ],
  ],
  'working' => [
    'match' => ['Working with Motaded'],
    'en' => [
      'title' => 'Working with Motaded',
      'lede' => '<p>Riyadh-based consultancy, established 2017. Further information is available on <a href="/about-us">About Motaded</a>.</p>',
      'items' => [
        ['title' => 'One point of contact', 'body' => '<p>A dedicated coordinator is assigned for the documents, the customs declaration and consignment status.</p>'],
        ['title' => 'Clear scope and fees', 'body' => '<p>The work and the service fee are agreed in writing before the clearance engagement begins.</p>'],
        ['title' => 'Updates at key stages', 'body' => '<p>Status is reported at the document, inspection, payment and release stages.</p>'],
        ['title' => 'Arabic and English', 'body' => '<p>Documents and status updates are provided in Arabic and English.</p>'],
        ['title' => 'Request a Consultation', 'body' => '', 'link_title' => 'Request a Consultation'],
        ['title' => 'WhatsApp', 'body' => '', 'link_title' => 'WhatsApp'],
      ],
    ],
    'ar' => [
      'title' => 'العمل مع متعدد',
      'lede' => '<p>استشارات مقرها الرياض، تأسست عام 2017. المزيد في <a href="/about-us">عن متعدد</a>.</p>',
      'items' => [
        ['title' => 'جهة اتصال واحدة', 'body' => '<p>يُعيَّن منسّق مخصص للمستندات والبيان الجمركي وحالة الشحنة.</p>'],
        ['title' => 'نطاق ورسوم واضحة', 'body' => '<p>يُتفق على العمل وأتعاب الخدمة كتابةً قبل بدء ارتباط التخليص.</p>'],
        ['title' => 'تحديثات في المراحل الرئيسية', 'body' => '<p>يُبلَّغ عن الحالة في مراحل المستندات والمعاينة والسداد والإفراج.</p>'],
        ['title' => 'العربية والإنجليزية', 'body' => '<p>تُقدَّم المستندات وتحديثات الحالة بالعربية والإنجليزية.</p>'],
        ['title' => 'طلب استشارة', 'body' => '', 'link_title' => 'طلب استشارة'],
        ['title' => 'WhatsApp', 'body' => '', 'link_title' => 'WhatsApp'],
      ],
    ],
  ],
  'process' => [
    'match' => ['How your customs clearance works', 'Customs clearance procedure'],
    'en' => [
      'title' => 'Customs clearance procedure',
      'lede' => '<p>Customs clearance in Saudi Arabia proceeds in the stages below. Duration depends on document completeness, cargo category and any inspection appointments. A written indication of timing is provided with the quotation after the consignment details have been reviewed.</p>',
      'items' => [
        [
          'title' => 'Submission of consignment details',
          'format' => 'full_html',
          'body' => _motaded_customs_tone_stage(
            'Company details, trade direction, cargo category and available commercial documents. A complete file is not required at this stage.',
            'The submitted information is reviewed. Applicable documents, approvals and actions are confirmed in writing, and a scope of work is proposed.',
            'Written service quotation for acceptance.'
          ),
        ],
        [
          'title' => 'Confirmation of scope and preparation of documents',
          'format' => 'full_html',
          'body' => _motaded_customs_tone_stage(
            'Acceptance of the written quotation, together with the commercial documents already held (invoice, packing list, bill of lading or air waybill, Commercial Register data, and any SABER or SFDA evidence already available).',
            'The agreed scope is confirmed. Remaining documents or certificates required before filing are listed in writing.',
            'Documents ready for the declaration, or a written list of outstanding items.'
          ),
        ],
        [
          'title' => 'Declaration and required checks',
          'format' => 'full_html',
          'body' => _motaded_customs_tone_stage(
            'Confirmation to file, and any clarifications requested by the authority.',
            'The declaration is submitted on <a href="/platforms/fasah">FASAH</a>, followed with <a href="/platforms/zatca">ZATCA</a>, and <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> or <a href="/platforms/saber">SABER</a> inspection is coordinated where required.',
            'Any official charges issued, and the release decision.'
          ),
        ],
        [
          'title' => 'Payment, release and handover',
          'format' => 'full_html',
          'body' => _motaded_customs_tone_stage(
            'Payment of official duty, tax and any inspection fees through the authority’s channels; nomination of the transporter.',
            'Payment of official charges is confirmed; the authority’s release decision is followed; the consignment is then handed to the nominated transporter.',
            'Completion confirmed to the named contact.'
          ),
        ],
      ],
    ],
    'ar' => [
      'title' => 'إجراءات التخليص الجمركي',
      'lede' => '<p>يمر التخليص الجمركي في المملكة العربية السعودية بالمراحل أدناه. تعتمد المدة على اكتمال المستندات وفئة الشحنة وأي مواعيد معاينة. يُرفق توجيه مكتوب بالمدة مع عرض السعر بعد مراجعة بيانات الشحنة.</p>',
      'items' => [
        [
          'title' => 'تقديم بيانات الشحنة',
          'format' => 'full_html',
          'body' => _motaded_customs_tone_stage_ar(
            'بيانات المنشأة، اتجاه التجارة، فئة الشحنة، والمستندات التجارية المتوفرة. لا يُشترط ملف مكتمل في هذه المرحلة.',
            'تُراجع المعلومات المقدَّمة. تُؤكد كتابةً المستندات والموافقات والإجراءات المنطبقة، ويُقترح نطاق العمل.',
            'عرض سعر مكتوب للقبول.'
          ),
        ],
        [
          'title' => 'تأكيد النطاق وإعداد المستندات',
          'format' => 'full_html',
          'body' => _motaded_customs_tone_stage_ar(
            'قبول عرض السعر المكتوب، والمستندات التجارية المتوفرة (الفاتورة، قائمة التعبئة، بوليصة الشحن أو بوليصة الشحن الجوي، بيانات السجل التجاري، وأي إثباتات سابر أو الهيئة العامة للغذاء والدواء المتوفرة).',
            'يُؤكد النطاق المتفق عليه. تُذكر كتابةً المستندات أو الشهادات المتبقية المطلوبة قبل التقديم.',
            'مستندات جاهزة للبيان، أو قائمة مكتوبة بالمعلقات.'
          ),
        ],
        [
          'title' => 'البيان والفحوصات المطلوبة',
          'format' => 'full_html',
          'body' => _motaded_customs_tone_stage_ar(
            'تأكيد التقديم، وأي إيضاحات تطلبها الجهة.',
            'يُقدَّم البيان عبر <a href="/platforms/fasah">فسح</a>، ويُتابع مع <a href="/platforms/zatca">ZATCA</a>، وتُنسَّق معاينة <a href="/platforms/saudi-food-and-drug-authority-sfda">الهيئة العامة للغذاء والدواء</a> أو <a href="/platforms/saber">سابر</a> عند الحاجة.',
            'أي رسوم رسمية إن صدرت، وقرار الإفراج.'
          ),
        ],
        [
          'title' => 'السداد والإفراج والتسليم',
          'format' => 'full_html',
          'body' => _motaded_customs_tone_stage_ar(
            'سداد الرسوم الجمركية والضريبة وأي رسوم معاينة عبر قنوات الجهة؛ وترشيح الناقل.',
            'يُؤكد سداد الرسوم الرسمية؛ ويُتابع قرار الإفراج من الجهة؛ ثم تُسلَّم الشحنة إلى الناقل المعيّن.',
            'تأكيد الإنجاز لجهة الاتصال المحددة.'
          ),
        ],
      ],
    ],
  ],
  'invite' => [
    'match' => ['Start with a consultation', 'Talk to the clearance team', 'Start with a written assessment', 'Request a written consultation'],
    'en' => [
      'title' => 'Request a written consultation',
      'lede' => '<p>Company and consignment details are reviewed in consultation. Applicable customs clearance requirements and the service fee are confirmed in writing.</p>',
      'items' => [
        ['title' => 'Request a Consultation', 'body' => '', 'link_title' => 'Request a Consultation'],
      ],
    ],
    'ar' => [
      'title' => 'طلب استشارة مكتوبة',
      'lede' => '<p>تُراجع بيانات المنشأة والشحنة أثناء الاستشارة. تُؤكد متطلبات التخليص الجمركي المنطبقة وأتعاب الخدمة كتابةً.</p>',
      'items' => [
        ['title' => 'طلب استشارة', 'body' => '', 'link_title' => 'طلب استشارة'],
      ],
    ],
  ],
  'documents' => [
    'match' => ['What documents do you need?', 'Documents required for customs clearance'],
    'en' => [
      'title' => 'Documents required for customs clearance',
      'lede' => '<p>The documents listed below are typically required for customs clearance in Saudi Arabia. Available documents are reviewed and any outstanding items are confirmed in writing.</p>',
      'items' => [
        ['title' => 'Documents for customs clearance', 'body' => '<ul><li>Commercial invoice</li><li>Packing list</li><li>Transport document (bill of lading or air waybill)</li><li>Company registration details</li><li>Product certificates and permits, where required — <a href="/platforms/saber">SABER</a> and <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a>. These depend on the cargo.</li></ul>'],
      ],
    ],
    'ar' => [
      'title' => 'المستندات المطلوبة للتخليص الجمركي',
      'lede' => '<p>تُطلب عادةً المستندات المدرجة أدناه للتخليص الجمركي في المملكة العربية السعودية. تُراجع المستندات المتوفرة ويُؤكد كتابةً أي بنود ناقصة.</p>',
      'items' => [
        ['title' => 'مستندات التخليص الجمركي', 'body' => '<ul><li>فاتورة تجارية</li><li>قائمة تعبئة</li><li>مستند النقل (بوليصة شحن أو بوليصة شحن جوي)</li><li>بيانات تسجيل المنشأة</li><li>شهادات المنتج والتصاريح عند الحاجة — <a href="/platforms/saber">سابر</a> و<a href="/platforms/saudi-food-and-drug-authority-sfda">الهيئة العامة للغذاء والدواء</a>. تعتمد على البضاعة.</li></ul>'],
      ],
    ],
  ],
  'fees' => [
    'match' => ['Fees and quotation', 'Fees and Quotation', 'How much does customs clearance cost?'],
    'en' => [
      'title' => 'Fees and quotation',
      'lede' => '<p>A written quotation is issued for each consignment. The service fee is agreed before work begins and reflects the cargo, the shipment route and the clearance work required.</p>',
      'items' => [
        ['title' => 'Motaded service fee', 'body' => '<ul><li>Agreed before work begins</li><li>The quotation states what is included and what is paid separately</li></ul>'],
        ['title' => 'Additional charges', 'body' => '<ul><li>Duty and taxes, if they apply — <a href="/platforms/zatca">ZATCA</a></li><li>Inspections and permits, if required</li><li>Terminal and warehouse charges, if they arise</li></ul>'],
      ],
    ],
    'ar' => [
      'title' => 'الرسوم وعرض السعر',
      'lede' => '<p>يُصدر عرض سعر مكتوب لكل شحنة. يُتفق على أتعاب الخدمة قبل بدء العمل، وتراعي البضاعة ومسار الشحنة وعمل التخليص المطلوب.</p>',
      'items' => [
        ['title' => 'أتعاب خدمة متعدد', 'body' => '<ul><li>يُتفق عليها قبل بدء العمل</li><li>يبيّن عرض السعر ما هو مشمول وما يُدفع بشكل منفصل</li></ul>'],
        ['title' => 'رسوم إضافية', 'body' => '<ul><li>الرسوم الجمركية والضرائب، إذا انطبقت — <a href="/platforms/zatca">ZATCA</a></li><li>المعاينات والتصاريح، إذا لزم الأمر</li><li>رسوم الميناء والمستودع، إذا نشأت</li></ul>'],
      ],
    ],
  ],
  'form' => [
    'en' => '<h2>Request a Consultation</h2>',
    'ar' => '<h2>طلب استشارة</h2>',
  ],
  'contact' => [
    'match' => ['Contact Our Customs Clearance Team'],
    'en' => [
      'title' => 'Contact Our Customs Clearance Team',
      'lede' => '<p>Further enquiries may be directed to the customs clearance team by WhatsApp, email or telephone <a href="tel:+966539797197">+966 53 979 7197</a>.</p>',
      'items' => [
        ['title' => 'Request a Consultation', 'body' => '', 'link_title' => 'Request a Consultation'],
      ],
    ],
    'ar' => [
      'title' => 'تواصل مع فريق التخليص الجمركي',
      'lede' => '<p>يمكن توجيه الاستفسارات الإضافية إلى فريق التخليص الجمركي عبر واتساب أو البريد الإلكتروني أو الهاتف <a href="tel:+966539797197">+966 53 979 7197</a>.</p>',
      'items' => [
        ['title' => 'طلب استشارة', 'body' => '', 'link_title' => 'طلب استشارة'],
      ],
    ],
  ],
  'related' => [
    'match' => ['Related Motaded pages'],
    'en' => [
      'title' => 'Related Motaded pages',
      'lede' => '<p>Official platforms and related Motaded pages for customs clearance in Saudi Arabia.</p>',
    ],
    'ar' => [
      'title' => 'صفحات متعدد ذات الصلة',
      'lede' => '<p>المنصات الرسمية وصفحات متعدد ذات الصلة بالتخليص الجمركي في المملكة العربية السعودية.</p>',
    ],
  ],
  'faq' => [
    'en' => [
      [
        'title' => 'How long does customs clearance take in Saudi Arabia?',
        'body' => '<p>Duration is typically measured in days. It depends on document completeness, cargo category and inspection appointments.</p>',
      ],
      [
        'title' => 'What happens if clearance documents are incomplete?',
        'body' => '<p>Outstanding documents and the next steps are identified in writing. A consultation may still be issued on the documents already supplied.</p>',
      ],
      [
        'title' => 'Can customs clearance proceed after the goods have arrived?',
        'body' => '<p>Assistance remains available after arrival. The entry point, available documents and arrival status should be stated in the consignment details on the form. Official inspection fees are paid to the examining authority. Storage, terminal or warehouse charges are billed by those providers.</p>',
      ],
      [
        'title' => 'When are additional inspections required?',
        'body' => '<p>When the cargo category falls under <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> or another examining body, or when <a href="/platforms/zatca">ZATCA</a> or <a href="/platforms/saber">SABER</a> procedures require physical or documentary examination. The appointment is coordinated by Motaded.</p>',
      ],
      [
        'title' => 'What charges are paid, and to whom?',
        'body' => '<p>The Motaded service fee is paid to Motaded against the accepted quotation. Customs duty, import VAT and official inspection fees are paid through the official channels. Storage or terminal charges are paid to the relevant provider. See also <a href="/platforms/zatca">ZATCA</a>.</p>',
      ],
      [
        'title' => 'Does the service cover both import and export?',
        'body' => '<p>Import and export are both supported. The trade direction should be selected on the form. The documents and <a href="/platforms/fasah">FASAH</a> messages differ; clearance requirements are confirmed during consultation. Importers who are not yet on FASAH may require <a href="/services/electronic-registration-importers-and-exporters">electronic registration</a>.</p>',
      ],
      [
        'title' => 'Is a Commercial Register required for FASAH filing?',
        'body' => '<p>A <a href="/platforms/fasah">FASAH</a> declaration must match a registered importer. If the company is not yet registered, the consignment may be reviewed and, separately, entity procedures may be advised through the <a href="/platforms/saudi-business-center-meras">Saudi Business Center</a>.</p>',
      ],
      [
        'title' => 'How does SABER relate to the customs declaration?',
        'body' => '<p><a href="/platforms/saber">SABER</a> addresses product conformity. <a href="/platforms/fasah">FASAH</a> and <a href="/platforms/zatca">ZATCA</a> address the customs declaration. Both may be required. A conformity certificate does not replace the declaration. The English <a href="/documents/customs-law-english">Customs Law</a> is available in the Motaded library.</p>',
      ],
    ],
    'ar' => [
      [
        'title' => 'كم يستغرق التخليص الجمركي في المملكة العربية السعودية؟',
        'body' => '<p>تُقاس المدة عادةً بالأيام. تعتمد على اكتمال المستندات وفئة الشحنة ومواعيد المعاينة.</p>',
      ],
      [
        'title' => 'ماذا يحدث إذا كانت مستندات التخليص ناقصة؟',
        'body' => '<p>تُحدد المستندات الناقصة والخطوات التالية كتابةً. يمكن إصدار استشارة بناءً على المستندات المقدَّمة.</p>',
      ],
      [
        'title' => 'هل يمكن المضي في التخليص الجمركي بعد وصول البضاعة؟',
        'body' => '<p>تبقى المساعدة متاحة بعد الوصول. يُذكر منفذ الدخول والمستندات المتوفرة وحالة الوصول في تفاصيل الشحنة في النموذج. رسوم المعاينة الرسمية تُدفع للجهة الفاحصة. رسوم التخزين أو الميناء أو المستودع يصدرها مقدّمو تلك الخدمات.</p>',
      ],
      [
        'title' => 'متى تُطلب معاينات إضافية؟',
        'body' => '<p>عندما تقع فئة الشحنة تحت <a href="/platforms/saudi-food-and-drug-authority-sfda">الهيئة العامة للغذاء والدواء</a> أو جهة فحص أخرى، أو عندما تتطلب إجراءات <a href="/platforms/zatca">ZATCA</a> أو <a href="/platforms/saber">سابر</a> فحصاً مادياً أو مستندياً. ينسّق متعدد الموعد.</p>',
      ],
      [
        'title' => 'ما الرسوم المستحقة، ولمن تُدفع؟',
        'body' => '<p>تُدفع أتعاب خدمة متعدد إلى متعدد مقابل عرض السعر المقبول. تُسدَّد الرسوم الجمركية وضريبة القيمة المضافة على الاستيراد ورسوم المعاينة الرسمية عبر القنوات الرسمية. رسوم التخزين أو الميناء تُدفع لمقدّم الخدمة المعني. انظر أيضاً <a href="/platforms/zatca">ZATCA</a>.</p>',
      ],
      [
        'title' => 'هل تشمل الخدمة الاستيراد والتصدير؟',
        'body' => '<p>يُدعم الاستيراد والتصدير. يُحدد اتجاه التجارة في النموذج. تختلف المستندات ورسائل <a href="/platforms/fasah">فسح</a>؛ وتُؤكد متطلبات التخليص أثناء الاستشارة. قد يحتاج المستوردون غير المسجّلين في فسح إلى <a href="/services/electronic-registration-importers-and-exporters">التسجيل الإلكتروني</a>.</p>',
      ],
      [
        'title' => 'هل يلزم سجل تجاري للتقديم عبر فسح؟',
        'body' => '<p>يجب أن يطابق بيان <a href="/platforms/fasah">فسح</a> مستورداً مسجّلاً. إذا لم تكن المنشأة مسجّلة بعد، يمكن مراجعة الشحنة، وتقديم المشورة بشكل منفصل حول إجراءات الكيان عبر <a href="/platforms/saudi-business-center-meras">المركز السعودي للأعمال</a>.</p>',
      ],
      [
        'title' => 'ما علاقة سابر بالبيان الجمركي؟',
        'body' => '<p>يعالج <a href="/platforms/saber">سابر</a> مطابقة المنتج. يعالج <a href="/platforms/fasah">فسح</a> و<a href="/platforms/zatca">ZATCA</a> البيان الجمركي. قد يُطلب كلاهما. شهادة المطابقة لا تغني عن البيان. <a href="/documents/customs-law-english">نظام الجمارك</a> بالإنجليزية متوفر في مكتبة متعدد.</p>',
      ],
    ],
  ],
];

$aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
  'alias' => MOTADED_CUSTOMS_TONE_ALIAS,
  'langcode' => 'en',
]);
$alias = $aliases ? reset($aliases) : NULL;
if (!$alias instanceof PathAlias) {
  throw new \RuntimeException('Customs landing alias not found.');
}
$nid = (int) str_replace('/node/', '', $alias->getPath());
$node = Node::load($nid);
if (!$node instanceof Node) {
  throw new \RuntimeException('Customs landing node not found.');
}

foreach (['en', 'ar'] as $langcode) {
  if (!$node->hasTranslation($langcode) && $langcode === 'ar') {
    continue;
  }
  $tr = $node->hasTranslation($langcode) ? $node->getTranslation($langcode) : $node;
  $tr->set('field_meta', json_encode($copy['meta'][$langcode], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
  $tr->save();
}

_motaded_customs_tone_allow_overview_type();

$updated = [];
$source = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $en_p = $paragraph->hasTranslation('en') ? $paragraph->getTranslation('en') : $paragraph;
  $title = $en_p->hasField('field_title') ? trim((string) $en_p->get('field_title')->value) : '';
  $bundle = $paragraph->bundle();

  if ($bundle === 'hero_split_banner') {
    _motaded_customs_tone_apply_hero($paragraph, $copy['hero']);
    $updated[] = 'hero';
    continue;
  }
  if ($bundle === 'webform') {
    _motaded_customs_tone_set_body($paragraph, 'en', $copy['form']['en'], 'full_html');
    _motaded_customs_tone_set_body($paragraph, 'ar', $copy['form']['ar'], 'full_html');
    $updated[] = 'form';
    continue;
  }
  if ($bundle === 'accordions') {
    _motaded_customs_tone_apply_faq($paragraph, $copy['faq']);
    $updated[] = 'faq';
    continue;
  }
  if ($bundle !== 'cards') {
    continue;
  }

  foreach (['overview', 'cargo', 'scope', 'working', 'process', 'invite', 'documents', 'fees', 'contact', 'related'] as $key) {
    if (!in_array($title, $copy[$key]['match'], TRUE)) {
      continue;
    }
    _motaded_customs_tone_apply_cards($paragraph, $copy[$key]['en'], $copy[$key]['ar']);
    $updated[] = $key;
    break;
  }
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $nid]);
echo 'Updated official tone on /node/' . $nid . ': ' . implode(', ', $updated) . "\n";

/**
 * @param array<string, array<string, string>> $copy
 */
function _motaded_customs_tone_allow_overview_type(): void {
  $storage = \Drupal::entityTypeManager()->getStorage('field_storage_config')->load('paragraph.field_card_type');
  if (!$storage) {
    return;
  }
  $values = $storage->getSetting('allowed_values') ?: [];
  if (isset($values['overview'])) {
    return;
  }
  $values['overview'] = 'Service: split overview';
  $storage->setSetting('allowed_values', $values);
  $storage->save();
}

function _motaded_customs_tone_apply_hero(ParagraphInterface $paragraph, array $copy): void {
  foreach ($copy as $langcode => $row) {
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
    $tr->save();
  }
}

/**
 * @param array<string, mixed> $en
 * @param array<string, mixed> $ar
 */
function _motaded_customs_tone_apply_cards(ParagraphInterface $paragraph, array $en, array $ar): void {
  if (empty($en['replace'])) {
    _motaded_customs_tone_update_cards($paragraph, $en, $ar);
    return;
  }

  $old = $paragraph->get('field_paragraphs')->referencedEntities();
  $children = [];
  foreach ($en['items'] ?? [] as $i => $item) {
    $values = [
      'type' => 'card',
      'langcode' => 'en',
      'field_title' => $item['title'] ?? '',
      'field_body' => [
        'value' => $item['body'] ?? '',
        'format' => $item['format'] ?? 'basic_html',
      ],
    ];
    if (($item['subtitle'] ?? '') !== '') {
      $values['field_sub_title'] = $item['subtitle'];
    }
    if (($item['uri'] ?? '') !== '') {
      $values['field_link'] = [
        'uri' => $item['uri'],
        'title' => ($item['link_title'] ?? '') !== '' ? $item['link_title'] : $item['title'],
      ];
    }
    $card = Paragraph::create($values);
    $card->save();
    $ar_item = $ar['items'][$i] ?? $item;
    $card_ar = $card->hasTranslation('ar')
      ? $card->getTranslation('ar')
      : $card->addTranslation('ar', ['status' => $card->isPublished()]);
    $card_ar->set('field_title', $ar_item['title'] ?? '');
    $card_ar->set('field_body', [
      'value' => $ar_item['body'] ?? '',
      'format' => $ar_item['format'] ?? 'basic_html',
    ]);
    if (($ar_item['subtitle'] ?? '') !== '') {
      $card_ar->set('field_sub_title', $ar_item['subtitle']);
    }
    if ($card_ar->hasField('field_link') && !$card->get('field_link')->isEmpty()) {
      $link = $card->get('field_link')->getValue();
      $link[0]['title'] = $ar_item['link_title'] ?? ($link[0]['title'] ?? '');
      $card_ar->set('field_link', $link);
    }
    $card_ar->save();
    $children[] = [
      'target_id' => $card->id(),
      'target_revision_id' => $card->getRevisionId(),
    ];
  }

  foreach (['en' => $en, 'ar' => $ar] as $langcode => $row) {
    $tr = $paragraph->hasTranslation($langcode)
      ? $paragraph->getTranslation($langcode)
      : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
    $tr->set('field_title', $row['title']);
    if (isset($row['lede'])) {
      $tr->set('field_body', [
        'value' => $row['lede'],
        'format' => 'basic_html',
      ]);
    }
    if (!empty($row['type']) && $tr->hasField('field_card_type')) {
      $tr->set('field_card_type', $row['type']);
    }
    if (!empty($row['headers']) && $tr->hasField('field_sub_title')) {
      $tr->set('field_sub_title', implode('|', $row['headers']));
    }
    $tr->set('field_paragraphs', $children);
    $tr->save();
  }

  foreach ($old as $child) {
    if ($child instanceof ParagraphInterface) {
      $child->delete();
    }
  }
}

function _motaded_customs_tone_update_cards(ParagraphInterface $paragraph, array $en, array $ar): void {
  foreach (['en' => $en, 'ar' => $ar] as $langcode => $row) {
    $tr = $paragraph->hasTranslation($langcode)
      ? $paragraph->getTranslation($langcode)
      : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
    $tr->set('field_title', $row['title']);
    if (isset($row['lede'])) {
      $tr->set('field_body', [
        'value' => $row['lede'],
        'format' => 'basic_html',
      ]);
    }
    if (!empty($row['type']) && $tr->hasField('field_card_type')) {
      $tr->set('field_card_type', $row['type']);
    }
    if (!empty($row['headers']) && $tr->hasField('field_sub_title')) {
      $tr->set('field_sub_title', implode('|', $row['headers']));
    }
    $tr->save();
  }

  if (empty($en['items'])) {
    return;
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
        'format' => $row['format'] ?? 'basic_html',
      ]);
      if (isset($row['subtitle'])) {
        $card_tr->set('field_sub_title', $row['subtitle']);
      }
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
 * @param array<string, list<array<string, string>>> $faq
 */
function _motaded_customs_tone_apply_faq(ParagraphInterface $paragraph, array $faq): void {
  $i = 0;
  foreach ($paragraph->get('field_paragraphs') as $item) {
    $card = $item->entity;
    if (!$card instanceof ParagraphInterface || !isset($faq['en'][$i])) {
      continue;
    }
    foreach (['en', 'ar'] as $langcode) {
      $row = $faq[$langcode][$i] ?? $faq['en'][$i];
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
}

function _motaded_customs_tone_set_body(ParagraphInterface $paragraph, string $langcode, string $html, string $format): void {
  $tr = $paragraph->hasTranslation($langcode)
    ? $paragraph->getTranslation($langcode)
    : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
  $tr->set('field_body', [
    'value' => $html,
    'format' => $format,
  ]);
  $tr->save();
}

function _motaded_customs_tone_stage(string $client, string $motaded, string $next): string {
  return '<div class="customs-stage__grid">'
    . '<div class="customs-stage__col"><span class="customs-stage__label">Client provides</span><p>' . $client . '</p></div>'
    . '<div class="customs-stage__col"><span class="customs-stage__label">Motaded does</span><p>' . $motaded . '</p></div>'
    . '<div class="customs-stage__col"><span class="customs-stage__label">Next step</span><p>' . $next . '</p></div>'
    . '</div>';
}

function _motaded_customs_tone_stage_ar(string $client, string $motaded, string $next): string {
  return '<div class="customs-stage__grid">'
    . '<div class="customs-stage__col"><span class="customs-stage__label">يقدّم العميل</span><p>' . $client . '</p></div>'
    . '<div class="customs-stage__col"><span class="customs-stage__label">ينفّذ متعدد</span><p>' . $motaded . '</p></div>'
    . '<div class="customs-stage__col"><span class="customs-stage__label">الخطوة التالية</span><p>' . $next . '</p></div>'
    . '</div>';
}
