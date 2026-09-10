# Лендінг: Customs Broker Services (KSA)

Чернетка для клієнта й команди. Основа — поточна сторінка  
`/services/customs-broker-services-saudi-arabia` (node 960, тип `page`).  
Факти про процедури — лише з офіційних порталів. Тексти Motaded (хто ми, як працюємо) — окремо від державних фактів.

Мови: **EN + AR** (як зараз). ES/FR/ZH на цей лендінг не робимо.

---

## 1. Що це за сторінка

Окремий комерційний лендінг послуги **митного кліренсу в Саудівській Аравії**.

Це не ще одна картка в каталозі `/services` і не стаття «Investment Sovereignty».  
Це сторінка з однією обіцянкою: **випустити імпорт/експорт через офіційні канали (ZATCA + FASAH), з оглядами SFDA/SASO-SABER де треба, і зрозумілим наступним кроком — котирування партії.**

Другий офер (відкрити власну брокерську компанію) лишається, але **нижче fold**, не в H1.

### Навіщо робимо

| Задача | Пояснення |
|---|---|
| Вивести послугу з каталогу | Зараз шаблон 120 послуг: спільні CTA, футер «business setup», related GRO/PRO/Mudad |
| Кваліфікувати лід | Форма про партію (порт, вантаж, країна), не Contact us → Suggestion |
| Реклама / прямі лінки | Стабільний URL, який можна крутити в Ads і WhatsApp |
| Довіра | Показуємо офіційні органи й платформи, не вигадані «гарантії без черг» |
| Зберегти SEO | 301 зі старого alias на лендінг |

### Чого хочемо від сторінки

1. Відвідувач за 10 секунд розуміє: це кліренс партії, не сетап компанії.
2. Може надіслати запит на котирування **не залишаючи сторінку**.
3. Бачить, через які **державні** системи це йде, і може клікнути на офіційний сайт.
4. Якщо хоче відкрити брокерську фірму — окремий блок, окрема дія.
5. Не обіцяємо термінів «гарантовано за N днів» і не вигадуємо тарифи мита — це визначає ZATCA / тип вантажу.

Успіх: заявки з форми `customs_clearance_quote` + дзвінки/WhatsApp з UTM лендінгу. Не кількість сторінок у каталозі.

---

## 2. Для кого (пріоритет)

1. **Головний:** компанія з CR, яка імпортує/експортує і потребує брокера (Fasah + декларація).
2. **Другий:** інвестор, який ще будує компанію, але вже планує першу партію — ведемо в котирування *і* коротко в setup, не навпаки.
3. **Третій:** підприємець, який хоче **ліцензію митного брокера** — окрема секція.

Не мішати всіх у першому абзаці (як зараз у Target Group).

---

## 3. Офіційні джерела (єдині лінки в контенті)

Лише ці домени. Внутрішні сторінки Motaded (`/platforms/zatca` тощо) — як пояснення, **зовнішня** кнопка завжди на `.gov.sa` / офіційний портал.

| Орган / система | Навіщо на лендінгу | Офіційний сайт |
|---|---|---|
| **ZATCA** — Zakat, Tax and Customs Authority | Митниця, мито, VAT на імпорт, акредитація брокера | [zatca.gov.sa](https://zatca.gov.sa/en/Pages/default.aspx) · митне право: [Customs regulations](https://zatca.gov.sa/en/RulesRegulations/Customs/Pages/default.aspx) · [Customs Law PDF](https://zatca.gov.sa/en/RulesRegulations/Customs/Documents/Customs%20Law_EN.pdf) · VAT: [VAT rules](https://zatca.gov.sa/en/rulesregulations/vat/pages/default.aspx) |
| **FASAH** | Електронний обмін даними імпорт/експорт, подача декларації | [fasah.sa](https://fasah.sa/) · [About](https://www.fasah.sa/trade/sau/html/en_US/subpage-about.html) |
| **SABER (SASO)** | Відповідність продукції, сертифікати перед відвантаженням | [saber.sa](https://saber.sa/) · [About](https://saber.sa/home/aboutsaber) · [Regulations](https://saber.sa/Regulations) |
| **SFDA** | Огляди партій food / drug / medical / cosmetics, де застосовно | [sfda.gov.sa](https://www.sfda.gov.sa/) |
| **Saudi Business Center (Meras)** | CR компанії, до якого прив’язується Fasah | [business.sa](https://business.sa/) |
| **Mawani** (за потреби в блоці портів) | Державні порти, не «ми швидші за всіх» | [mawani.gov.sa](https://mawani.gov.sa/) |

**Не ставимо** як джерела: блоги, агрегатори, внутрішні лінки «Investor / Employee Management / Company Formation» замість пояснення.

Формула VAT 15% на імпорт (база CIF + мито) — як **орієнтир з поточної сторінки**, з ремаркою: актуальні ставки й база — лише ZATCA. Не фіксувати як «закон Motaded».

Ліцензія брокера Motaded — **твердження клієнта**. На лендінгу: «Motaded працює як митний брокер / з акредитованими брокерами» тільки після підтвердження формулювання юридичним. Поруч — лінк на ZATCA, не печатка «official partner», якщо немає офіційного бейджа.

---

## 3.1. Що взяти з головної — і чого не копіювати

Головна (`/`, node 374, той самий тип `landing_page`) — **хаб сетапу компанії**, не лендінг однієї послуги. Блоки там:

| # | Paragraph | На головній | На customs-лендінг |
|---|---|---|---|
| 0 | `hero_split_banner` | Eyebrow «Official Gateway», H1 у 2 рядки, intro, 3 feature-картки, 2 CTA, фонове фото | **Так — той самий компонент**, інший текст і фото. Форму в hero не пхаємо (на головній її теж немає) |
| 1 | `view_block` partners | Стрічка логотипів «Trusted Government Platforms» | **Так, вужче:** лише ZATCA, FASAH, SABER, SFDA, SBC. Не банки й не «всі партнери сайту» |
| 2 | `glance_block` | Vision 2030, ВВП, FDI, населення (GASTAT / MISA) | **Не копіювати ринок KSA.** Glance лише як вигляд для snapshot послуги (хто / канали / оплата), без макроекономіки |
| 3 | `view_block` services | Каталог «Integrated Services» | **Ні** — знову розмиває офер |
| 4 | `key_sectors` | Сектори Vision 2030 | **Ні** |
| 5–6 | `system_masthead` + `investor_journey` | 4 кроки сетапу з офіційними платформами (MISA → CR → банк → Qiwa/ZATCA) | **Так, патерн:** masthead + journey з FASAH/ZATCA/SABER/SFDA на кроках кліренсу |
| 7–10 | events, chambers, library, insights | Контент-хаб | **Ні** (опційно 1–2 статті про імпорт у related, не стрічка блогу) |
| 11 | `business_cost_estimation` | Калькулятор сетапу | **Ні** — інша послуга |

На About us (`/about-us`) той самий словник: glance + `why_opportunities` + journey + `platforms_systems`. Звідти беремо **«Why Motaded»** (3 колонки), якого на головній немає як окремого блока, але є в feature-картках hero і у футері gateway.

**Висновок:** візуально сторінка має виглядати як головна (split-hero, eyebrow, логотипи органів, процес із платформами). Контент — тільки кліренс. Не переносимо GDP, сектори, events і CTA «Browse Services» / «Request a Consultation» на Contact us.

---

## 4. Блоки сторінки (зверху вниз)

Порядок = шлях користувача. Кожен блок: мета, контент, картинка, як зібрати в Drupal.

**Порядок (офіційний лендінг, EN):**  
hero (Clearance Assessment / Contact Our Team) → Service Overview → Service at a Glance → Scope of Services → Relevant Authorities and Platforms → Cargo Categories → Clearance Process → Requirements and Documents → Fees and Quotation → Working with Motaded → форма `#assessment` → FAQ → Contact Our Customs Clearance Team.

Раніше спека була «каркас»: багато назв блоків, у кожному 1 речення. Нижче — **повний текст**, інакше після збірки сторінка коротша за поточний node 960.

Форму лишаємо високо (після пояснення + логотипів), але **під нею має бути довгий скрол** — інакше лендінг = hero + форма.

### Блок 0. Шапка сайту

Звичайний Motaded header (як інші `landing_page`).  
**Прибрати** на цьому path глобальний pre-footer «Need help with your business setup?» — інакше знову продаємо сетап.

Поля: не в CT. Visibility блока в Layout / Block layout по alias.

---

### Блок 1. Hero (як на головній: `hero_split_banner`)

**Навіщо:** та сама візуальна мова, що `/`. Одна обіцянка + дві дії. Форми в hero немає — на головній теж кнопка, не форма.

| Поле (вже є в `hero_split_banner`) | Чернетка EN |
|---|---|
| `field_hero_split_eyebrow` | Official customs clearance in Saudi Arabia |
| `field_hero_split_headline_prefix` | Customs clearance |
| `field_hero_split_headline_accent` | through FASAH and ZATCA |
| `field_body` | We file your import or export on the official channels: declaration, inspections where required, duties and import VAT, then release. Quote before work starts. |
| `field_link` (primary) | Get a shipment quote → `#quote` |
| `field_link_secondary` | WhatsApp → `https://wa.me/966539797197` (не Browse Services) |
| `field_media` | Згенероване HERO-фото (на головній уже є media) |

**3 feature-картки** (`field_hero_split_features` → `why_feature_card`) — як «Supported by Motaded / Invest / Platforms» на головній:

| Title | Body | Icon | Link |
|---|---|---|---|
| File on FASAH | Declaration tied to your Commercial Register | `shield_check` | `#quote` або `/platforms/fasah` |
| Coordinate inspections | ZATCA; SFDA or SABER/SASO when the cargo requires it | `layers` | `#process` |
| Quote before work | Our fee is quoted; official duties and VAT stay with ZATCA | `chart` | `#quote` |

`field_hero_split_stats` з головної (GDP +8.7%, 36M+, FDI) **не ставити** — це не про кліренс.

**Не класти в hero:** відкриття брокерської компанії, Employee Management, GRO, «Request a Consultation» на `/contact-us`.

H1 арабською: **التخليص الجمركي** / **عبر منصة فسح وهيئة الزكاة والضريبة والجمارك**.

**Як у CT:** `hero_split_banner` — уже дозволений на `landing_page`. `hero_two_cols` — запасний, якщо раптом треба форма в правій колонці.

---

### Блок 1b. What this service is (текст, якого не вистачало)

**Навіщо:** без цього після hero одразу логотипи й форма — сторінка «тонка». 2–3 абзаци простою мовою, вичищені з node 960.

**Eyebrow:** The service  
**Title:** A customs broker for import and export files in KSA  

**Абзац 1.**  
Motaded handles the customs file for companies that already trade, or are about to send their first shipment. We prepare and submit the declaration on **FASAH**, follow the file with **ZATCA**, and coordinate **SFDA** or **SABER / SASO** inspections when the commodity falls under those rules. The aim is a complete file and a release decision — not a shortcut around the authority.

**Абзац 2.**  
You stay the importer or exporter of record. Your Commercial Register and the data on the invoice must match what goes into FASAH ([business.sa](https://business.sa/), [fasah.sa](https://fasah.sa/)). We tell you which documents are missing, calculate **our** fee as a quote, and separate it from official duties and import VAT (those lines follow [ZATCA](https://zatca.gov.sa/en/Pages/default.aspx)).

**Абзац 3.**  
This page is for **clearing a shipment**. If you need a company in Saudi Arabia first, say so on the form — we can run entity setup in parallel. If you want to **license a brokerage firm**, that is a different engagement (see the block below).

**Як у CT:** `section` або `media_lead` (`field_title` + `field_body`). Картинка не обов’язкова.

---

### Блок 2. Форма котирування (`#quote`)

**Навіщо:** головна конверсія. Не Contact us.

**Новий webform** `customs_clearance_quote` (не існуючі consultation / contact).

| Поле | Тип | Обов’язкове | Нотатка |
|---|---|---|---|
| First name | text | так | як на інших формах |
| Last name | text | так | |
| Work email | email | так | **Не блокувати Gmail** на цій формі — імпортер часто пише з особистої пошти |
| Phone | tel intl, default SA | так | |
| Company name | text | так | |
| Has CR in KSA? | radios yes/no | так | якщо ні — показати текст: спочатку entity + лінк на business setup, форму все одно приймаємо |
| Shipment direction | import / export | так | |
| Origin / destination country | text або select | так | |
| Port or airport (if known) | text | ні | |
| Cargo type | select: general / food / pharma or medical / chemicals / electronics / other | так | для food/pharma згадуємо SFDA; для consumer goods — SABER |
| Approximate value (SAR or USD) | number + currency | ні | |
| Need quote by (date) | date | ні | |
| Notes / documents | textarea + optional file | ні | інвойс, packing list — не вимагати на першому кроці |
| Consent | checkbox | так | |

Honeypot + reCAPTCHA — як на `setup_cost_estimate_lead`.  
Після сабміту: thank-you на цій же сторінці + CRM webhook (як contact / setup cost).

**Поля CT:** `landing_page.field_form` **вже є** — ставимо форму **одразу під hero + логострічкою**, як окремий блок (на головній форми немає, тут вона головна дія).  
`webform` як кореневий paragraph на `field_paragraphs` зараз не увімкнений — або користуємось `field_form` (рендериться внизу шаблону — тоді FAQ/paragraphs треба врахувати порядок), або вмикаємо `webform` у paragraphs / тримаємо форму в `hero_two_cols`. **Рекомендація v1:** paragraph-порядок: hero → логострічка → форма через увімкнений `webform` у `field_paragraphs`, `field_form` не заповнювати (інакше форма з’їде вниз після FAQ).

Картинка: не потрібна.

---

### Блок 2b. Logo rail — official systems (як partners на головній)

**Навіщо:** на `/` одразу після hero йде стрічка логотипів. Той самий прийом довіри, але **тільки митні/торгові органи**.

Заголовок (як на home, вужче): **Official systems this service uses**  
Підзаголовок: We file and coordinate in these government channels. Logos and names as published by the authorities.

Лого + лінк на офіційний сайт (не внутрішній `/platforms` як єдиний URL): ZATCA, FASAH, SABER, SFDA, Saudi Business Center. Опційно Mawani.

**Як у CT:** вузький `view_block` (окремий view з 5 platform nodes) **або** `platforms_systems` у компактному режимі. Не брати view `partners` з головної — там банки й «всі партнери».

Логострічка **не замінює** текст. Одразу під нею — повні картки (блок 3). Без карток лишається рядок іконок, як на головній, і сторінка знову «пуста».

---

### Блок 3. Official systems — повні картки (v1, обов’язково)

**Навіщо:** пояснити роль кожного органу. Текст ≈ 50–80 слів на картку + Official site. Внутрішня сторінка Motaded — друге посилання («How we work with this system»).

Заголовок: **Which official systems this file goes through**  
Lede: We do not replace these authorities. We prepare, file, and follow the shipment inside their channels.

| Картка | Текст (чернетка) | Official | Motaded |
|---|---|---|---|
| **ZATCA** | The Zakat, Tax and Customs Authority administers customs, duties, import VAT, and the rules for customs brokers. Duty rates and the VAT base are published by ZATCA, not by Motaded. We submit and follow the file; the release decision is the authority’s. | [zatca.gov.sa](https://zatca.gov.sa/en/Pages/default.aspx) · [Customs regulations](https://zatca.gov.sa/en/RulesRegulations/Customs/Pages/default.aspx) | `/platforms/zatca` |
| **FASAH** | FASAH is the national electronic window for import/export data between government and private parties. The customs declaration for the shipment is filed here and must match the importer’s Commercial Register and the commercial documents. | [fasah.sa](https://fasah.sa/) · [About](https://www.fasah.sa/trade/sau/html/en_US/subpage-about.html) | `/platforms/fasah` |
| **SABER (SASO)** | SABER is the official channel for product conformity (technical regulations). Many consumer and industrial goods need a certificate **before** the goods arrive. A SABER certificate does not replace the FASAH/ZATCA declaration. | [saber.sa](https://saber.sa/) · [About](https://saber.sa/home/aboutsaber) | `/platforms/saber` |
| **SFDA** | Food, drugs, medical devices, cosmetics and similar categories can require SFDA controls or inspections in addition to customs. We coordinate the appointment and the file; SFDA decides. | [sfda.gov.sa](https://www.sfda.gov.sa/) | `/platforms/saudi-food-and-drug-authority-sfda` |
| **Saudi Business Center** | FASAH and the importer number sit on a valid CR. If the company is not registered yet, clearance cannot finish — we can start entity work in parallel. | [business.sa](https://business.sa/) | setup / Meras page, якщо є |
| **Mawani** (коротша картка) | State ports and terminals. Entry point (sea / air / land) changes documents, inspections, and our quote. We do not publish a “fastest port”. | [mawani.gov.sa](https://mawani.gov.sa/) | — |

**Не писати** «ми швидші за ZATCA».

**Картинки:** офіційні лого, не генерувати. `platforms_systems` або `cards` + media з platform nodes.

---

### Блок 4. Official process (як `system_masthead` + `investor_journey` на головній)

**Навіщо:** на головній сетап показаний як державний шлях з логотипами платформ на кожному кроці. Те саме для кліренсу — сильніше, ніж голі `steps` без органів.

`system_masthead`:  
- eyebrow: `OFFICIAL CLEARANCE PROCESS`  
- title: How a shipment is cleared in Saudi Arabia  

`investor_journey` (якір `#process`), note як на home: *This process runs on official government systems. Timing depends on cargo, documents, and inspection slots — we do not guarantee a fixed number of days.*

Кожен крок — **заголовок + 70–100 слів**, не один рядок (на головній кроки короткі, бо сторінку тримає ринок/сектори; тут кроки — основний контент).

| Крок | Текст | Платформи |
|---|---|---|
| 1. Documents before the goods arrive | We review the commercial invoice, packing list, and bill of lading or air waybill against the intended HS classification and Incoterms. If the product is in a SABER technical regulation, the conformity certificate should exist **before** the vessel or aircraft arrives ([saber.sa](https://saber.sa/)). Food, pharma, medical and cosmetics files may need SFDA evidence ([sfda.gov.sa](https://www.sfda.gov.sa/)). Incomplete or mismatched documents are the usual reason a file waits. | SABER, SFDA, SBC |
| 2. Declaration and inspections | We submit the declaration through FASAH and follow it with ZATCA. Duties and import VAT are calculated on the official base — typically the shipment value plus freight and insurance plus customs duty; confirm the live rule on [ZATCA VAT](https://zatca.gov.sa/en/rulesregulations/vat/pages/default.aspx). If SFDA or SASO/SABER inspection is required, we coordinate the slot and attend as agreed. We cannot book a result or a fixed number of days. | FASAH, ZATCA, SFDA |
| 3. Official fees, release, handover | When the authority issues the charges, you pay the official lines (duty, VAT, inspection fees where they apply). After release we hand the shipment to the transporter you name. Our invoice is only the Motaded fee from the approved quote. | ZATCA, Mawani |

**Хто що робить** — окремий підблок (`cards` × 3), не один рядок:

| Роль | Робить | Не робить |
|---|---|---|
| **You (importer / exporter)** | Valid CR, true commercial documents, SABER/SFDA certificates for the product, payment of official duty and VAT | Ask Motaded to change an HS code to a lower duty without a legal basis |
| **Motaded** | File review, FASAH declaration, coordination at inspections, quote for our fee, status updates in AR/EN | Replace ZATCA or SFDA; guarantee a release date |
| **The authority** | Assessment, inspections, duty/VAT, release or hold | — |

Якщо `investor_journey` візуально занадто «сетапний» (іконки MISA) — fallback: `steps` + окремий `platforms_systems`. Але спочатку пробуємо journey: тип уже на `landing_page`.

**Картинки кроків:** іконки journey (як на home), STEPS-ілюстрації не обов’язкові. Генерація STEPS — лише якщо лишаємо `steps` замість journey.

---

### Блок 5. Snapshot (замість розкиданого service snapshot)

**Навіщо:** відповіді «для кого / скільки / мови / оплата» без канцеляриту.

| Рядок | Контент |
|---|---|
| Who | Importing and exporting companies with a KSA CR; PRO/logistics teams acting for them |
| Channels | FASAH + ZATCA; on-site at inspection points when required |
| Languages | Arabic, English (документи й договори). Chinese — тільки якщо клієнт підтвердить, що це реально дають; інакше **прибрати** з поточної сторінки |
| Duration | Typically days, not a fixed SLA — depends on cargo and appointments |
| Cost | Quote per shipment (port, inspections, urgency). Official duties/VAT are extra, set by ZATCA |
| Payment | Bank transfer against approved quotation |

**Картинка:** не обов’язкова; можна іконки з теми (`icon-target.svg` тощо).

**Як у CT:** `glance_block` — той самий компонент, що «Saudi Arabia at a glance» на `/` і About.  
Eyebrow: `SERVICE SNAPSHOT` (не `SAUDI ARABIA AT A GLANCE`).  
Title: `What to expect`.  
У stat-картки — рядки таблиці вище. **Source** на макроцифрах не вигадувати. Поле `source` можна поставити «Motaded quotation» лише на Cost/Payment, не на «15% VAT» (джерело — ZATCA).

Не тягнути поля `page` (`field_service_duration` тощо). Нові поля на `landing_page` не потрібні.

---

### Блок 5b. Why Motaded (як `why_opportunities` на About us)

**Навіщо:** на головній довіра розмазана (hero cards + gateway footer). На послузі потрібен короткий «чому ми», інакше після офіційних органів здається, що сторінка державна.

Eyebrow: `WHY MOTADED`  
Title: How we support clearance  

3 картки (не сетап, не Vision 2030):

| Title | Body |
|---|---|
| We work in the official systems | Filings and follow-up on FASAH and with ZATCA; SFDA / SABER when the cargo requires it. |
| Quote before we start | You see our fee first. Duties and import VAT are official lines, not hidden in our invoice. |
| One team for the file | Documents, inspections, release — one coordinator. We do not replace the authority’s decision. |

CTA в блоці: знову `#quote`, не `/contact-us`.

**Не писати:** «2810+ foreign investors», «fastest clearance», «official partner of ZATCA» без бейджа. Цифра з головної/маркетингу — про сетап, не про митницю.

**Як у CT:** `why_opportunities` уже дозволений на `landing_page`.

---

### Блок 5c. What we do (scope)

**Навіщо:** на поточній сторінці «Areas of work» були порожні, у body — три дії в одному реченні. Окремі картки заповнюють сторінку і знімають «а що входить?».

Title: **What is included in a clearance file**  
Lede: Scope is confirmed on the quote. Typical work:

| Картка | Текст |
|---|---|
| Pre-arrival file check | Invoice, packing list, transport document, CR data, HS / product class. We list gaps before the goods land. |
| FASAH declaration | Prepare and submit the electronic declaration; keep your data aligned with the CR on [business.sa](https://business.sa/). |
| Authority coordination | ZATCA follow-up; SFDA or SABER/SASO inspection when the cargo is in those channels. |
| Official charges vs our fee | We show duties/VAT as official lines (ZATCA). Our fee is a separate quoted amount. |
| Release and handover | After the release decision, we close the file and hand over to your transporter. |
| Status in Arabic and English | Written updates to the person you name on the form (PRO, GM, or logistics). |

**Як у CT:** `cards` (6 × `card`) або `why_opportunities` на 6 фіч, якщо верстка дозволяє.

---

### Блок 5d. Cargo types — which extra authority

**Навіщо:** імпортер шукає себе. Це контент, не каталог Motaded.

Title: **Does your cargo need more than a customs declaration?**  
Lede: The declaration on FASAH is required for the shipment. Extra channels depend on the product. We confirm on the quote — the lists below are orientation, not a legal determination. Always check the official site for your HS / product.

| Тип | Що зазвичай додається | Official |
|---|---|---|
| General cargo | FASAH + ZATCA. SABER if a technical regulation applies. | zatca.gov.sa · saber.sa |
| Food, special foods | Often SFDA establishment / product rules + possible inspection. | [sfda.gov.sa](https://www.sfda.gov.sa/) |
| Pharma / medical devices | SFDA registration / MDMA pathways in addition to customs. | SFDA · наш гід `/blog/sfda-medical-device-registration-mdma-2026-classes-and-fees-guide` (як related, не як закон) |
| Cosmetics | SFDA / eCosma plus customs. | SFDA · `/blog/cosmetics-import-registration-saudi-arabia-ecosma-2026-guide` |
| Electronics, toys, many consumer goods | SABER PCoC/SCoC **before** arrival is common. | [saber.sa](https://saber.sa/) |
| Chemicals / restricted | Extra permits may apply; we flag this on the file review — do not assume “general cargo”. | ZATCA / competent authority as applicable |

**Як у CT:** `cards` або `accordions`. Іконки теми, без генерації фото на кожен тип.

---

### Блок 5e. What usually delays a file

**Навіщо:** практичний блок, якого немає на головній і майже немає на node 960. Знімає «гарантуйте 48 годин».

Title: **What typically holds a shipment**  
(не «how we are faster»)

1. **Documents do not match** — values, weights, or consignee on the invoice vs packing list vs B/L.  
2. **SABER or SFDA evidence is missing** when the product requires it — the goods are already on the water.  
3. **CR / importer data** on FASAH does not match [business.sa](https://business.sa/).  
4. **Inspection slot** — SFDA or other exam, not Motaded’s calendar.  
5. **Unpaid official charges** — duty/VAT must clear before release.

Закриття: Send the invoice and packing list with the quote form. We say what is missing before we start.

**Як у CT:** `accordions` або нумерований `section`.

---

### Блок 6. Documents checklist

**Навіщо:** знімає «що готувати». Список — типові торгові документи + офіційні сертифікати, не вигаданий пакет Motaded.

- Commercial invoice, packing list, bill of lading / AWB  
- CR data matching FASAH (через [business.sa](https://business.sa/))  
- SABER PCoC/SCoC, якщо технічний регламент вимагає ([saber.sa](https://saber.sa/))  
- SFDA approvals, якщо категорія під SFDA  
- Insurance / Incoterms, узгоджені з декларацією  

Текст-дисклеймер: точний пакет залежить від HS-коду й режиму — підтверджуємо по партії.

**Картинка:** згенерувати одну (див. §6, DOCS) або без фото, лише список.

**Як у CT:** `cards` / `accordions` / `promo_split` зі списком у body. Полів чекліста в CT немає — **не заводити** окремий field, поки це один лендінг.

---

### Блок 7. What you pay (не лише VAT)

**Навіщо:** один абзац про 15% — мало. Імпортер хоче розкладку. Цифри мита **не вигадуємо**.

Title: **Three kinds of money on a clearance**  
Lede: Only the Motaded line is our quote. The other two are official.

| Лінія | Хто виставляє | Як рахується | Де перевірити |
|---|---|---|---|
| **Motaded fee** | Motaded | Per shipment: port/airport, cargo type, inspections, urgency. Quote before work. | This page / the quote |
| **Customs duty** | ZATCA | Depends on HS classification and the official tariff. We do not publish rates. | [ZATCA customs](https://zatca.gov.sa/en/RulesRegulations/Customs/Pages/default.aspx) |
| **Import VAT** | ZATCA | Standard rate on the official VAT pages (currently 15%). For imports the base typically includes shipment value, freight and insurance, **plus** customs duty. Confirm the live rule. | [ZATCA VAT](https://zatca.gov.sa/en/rulesregulations/vat/pages/default.aspx) |

Додатковий абзац: Inspection or lab fees can appear when SFDA or another body examines the goods. They are not part of the Motaded fee unless the quote says so.

Payment: bank transfer against the approved Motaded quotation. Official charges follow the authority’s payment channels.

**Картинка:** не генерувати інфографіку закону.

**Як у CT:** `section` + таблиця в body (Full HTML обережно) або 3 `card`. Glance stats сюди не дублювати.

---

### Блок 7b. Who this is for / who it is not

**Навіщо:** qualifying + об’єм тексту. На node 960 Target Group був один рядок.

| For | Not for (we still reply, but this is not the page) |
|---|---|
| Companies with a KSA CR that import or export goods | Individuals clearing a personal parcel / household goods (say so on the form; different process) |
| PRO / logistics teams acting for that company | “Get me a lower HS duty” with no documents |
| First shipment while the entity is being formed (we run both tracks) | Only looking for MISA / company formation — use the setup pages |
| Founders who want to **license a brokerage** (use the intent on the form) | Expecting Motaded to override a ZATCA or SFDA hold |

**Як у CT:** `split_promo` або дві колонки `cards`.

---

### Блок 8. Secondary offer — start a brokerage firm

**Навіщо:** не втратити третю аудиторію, не зламати H1.

Заголовок: **Want to license a customs brokerage company?**  

Текст (повний, не два речення):  
ZATCA publishes the licensing and professional rules for customs brokers ([Customs regulations](https://zatca.gov.sa/en/RulesRegulations/Customs/Pages/default.aspx)). If you want your **own** brokerage — legal form, Commercial Register, access to FASAH, and the filings the authority requires — Motaded can advise that track as a separate project. It is not included in a shipment quote.

We do not sell a “ready license”. The authority decides. On the form choose **Brokerage company setup** so the file does not sit in the clearance queue.

CTA: `Request brokerage-setup advice` → та сама форма з прихованим полем `intent=brokerage` **або** окремий радіо в формі «I need: shipment clearance / brokerage company setup».

Лінк: ZATCA Customs regulations.

**Картинка:** згенерувати (див. §6, BROKER-SETUP) — офіс/документи, без підробленої ліцензії ZATCA.

**Як у CT:** `promo_split` (`field_media` + текст + CTA) — уже дозволений на landing_page.

---

### Блок 9. FAQ

Взяти з поточної сторінки, вичистити лінки на business-setup всередині кожної відповіді.

Мінімум **8–10**, інакше FAQ коротший за поточну сторінку.

1. **Is Motaded a licensed customs broker?**  
   Відповідь клієнта (підтвердити юридичним). Рамка: licensing — ZATCA. Без «прискорює Company Formation».

2. **How is import VAT calculated?**  
   Як блок 7 + [ZATCA VAT](https://zatca.gov.sa/en/rulesregulations/vat/pages/default.aspx).

3. **Do you help set up a brokerage company?**  
   Так, окремий трек — блок 8.

4. **What if I do not have a CR yet?**  
   The FASAH file must match a registered importer. We accept the quote and can start entity work in parallel ([business.sa](https://business.sa/)).

5. **SABER vs the customs declaration?**  
   SABER = product conformity. FASAH/ZATCA = the shipment file. Often both. A certificate does not replace the declaration.

6. **How long does clearance take?**  
   Usually measured in days. It depends on document quality, cargo type, and inspection appointments. We do not sell a guaranteed SLA.

7. **Do I need to be at the port?**  
   Not if you appoint us and the documents are in order. Some inspections still require goods to be presented; we coordinate that.

8. **What do you need to quote?**  
   Invoice and packing list if you have them; direction (import/export); origin; cargo type; port if known. Full set is listed in Documents.

9. **Who pays duty and VAT?**  
   The importer, through official channels, unless your Incoterms say otherwise. That is separate from the Motaded fee.

10. **Do you clear exports as well as imports?**  
    Yes — say Export on the form. Documents and FASAH messages differ; we confirm on the file.

**Як у CT:** `landing_page.field_faq` **вже є** (faqfield). Краще воно, ніж FAQ у `field_keywords` як зараз на `page`.  
Не використовувати `field_keywords` під акордеон.

---

### Блок 10. Official reading + related Motaded pages

**Навіщо:** на головній «товщину» дають library/insights. Тут — **короткий reading list**, не стрічка всього блогу.

**A. Official (зовнішні, обов’язково)**  
- ZATCA home · Customs regulations · Customs Law PDF · VAT rules  
- FASAH home · About FASAH  
- SABER home · About SABER · Regulations  
- SFDA home · Mawani · Saudi Business Center  

**B. Motaded, лише релевантне** (ручний список, не taxonomy):  
- `/platforms/fasah`, `/platforms/zatca`, `/platforms/saber`, `/platforms/saudi-food-and-drug-authority-sfda`  
- `/blog/import-export-saudi-arabia`  
- `/blog/about-Fasah-Platform`, `/blog/about-Saber-platform`  
- SFDA гіди — тільки якщо вантаж food/medical/cosmetics (можна всі три як «If you import…»)  
- `/services/electronic-registration-importers-and-exporters` та `/services/adding-importer-number-new-port` — якщо сторінки живі й не SEO-сміття; інакше не лінкувати  

Не Mudad, не WPS, не GRO/PRO.

**Як у CT:** `featured_contents` (B) + `cards` з зовнішніми лінками (A) або один `section` зі списком.

---

### Блок 11. Фінальний CTA

Не «Need help with your business setup?».

**Need a clearance quote?**  
Повторити кнопку `#quote` + WhatsApp + Call.

**Як у CT:** `promo_split` / `banner` / `banner_sm`.  
Глобальний pre_footer на цьому alias — вимкнути.

---

### Блок 12. Footer

Звичайний site footer. Breadcrumb можна: Home → Services → Customs clearance (лендінг).

---

## 5. Що з поточної сторінки не переносимо

- Keyword-stuffing («Saudi Arabia» двічі в першому реченні)
- Автолінки Companies / Investor / General Manager / Employee Management / Government Services
- Secondary CTA «Go to other services»
- Related GRO, PRO, Mudad
- Share AddToAny у hero (на лендінгу не потрібен)
- Коментарі (`field_comments`)
- Yoast з порожнім focus keyword як вимога запуску
- Твердження «Chinese support», доки клієнт не підтвердить
- Ціну як schema.org Offer з рядка «Varies by shipment…» (ламає rich results)

---

## 6. Картинки

### Не генерувати

Логотипи й печатки **ZATCA, FASAH, SABER, SASO, SFDA, Mawani, SBC**.  
Джерело: офіційні сайти / уже завантажені platform logos. Підробка герба — ризик і для Ads, і юридично.

### Згенерувати (стиль Motaded: спокійно, KSA, без фейкових вивісок відомств)

| ID | Де | Кадр | Розмір | Не робити |
|---|---|---|---|---|
| **HERO** | Блок 1 | Документи + контейнер / аеропорт, денне світло, Саудівський комерційний контекст (нейтральний skyline/порт, без логотипів міністерств) | 1600×900, webp | Люди з читабельними паспортами; чужі бренди |
| **INTRO** | Блок 1b, опційно | Широкий кадр порту/складу, без вивісок відомств | 1400×800 | Герб, лого Fasah |
| **STEPS-1..3** | Лише якщо процес = `steps`, не journey | Як раніше: документи / склад / handover | 800×600 | Штамп «ZATCA approved», скрін Fasah |
| **DOCS** | Блок 6, опційно | Акуратна стопка торгових документів, top view | 1200×800 | Герб KSA, підписи |
| **BROKER-SETUP** | Блок 8 | Переговори в офісі Ер-Ріяда, без бейджів відомств | 1400×800 | «License certificate» з вигаданим номером |

Іконки кроків можна не генерувати — у темі вже є SVG (`icon-target.svg` тощо).

Після генерації: залити як Media image, alt англійською/арабською без keyword stuffing.

---

## 7. Content type і поля

### Рекомендація

Нова нода типу **`landing_page`**, не нова CT і не прапорець на `page`.  
Стару `page` (nid 960): 301 на новий alias + `field_display_on_services` = off (або картка в каталозі веде на лендінг).

### У `landing_page` уже є

| Поле | Використання на цьому лендінгу |
|---|---|
| `title` | H1 / admin |
| `body` | можна не використовувати, якщо все в paragraphs |
| `field_paragraphs` | усі секції |
| `field_form` | webform, якщо форма не в hero |
| `field_faq` | блок 9 |
| `field_meta` | title, description, OG |
| path alias | новий шлях, напр. `/customs-clearance-saudi-arabia` (узгодити з клієнтом) |

Дозволені paragraphs, які покривають блоки:  
`hero_split_banner` (як `/`), `view_block` (логострічка), `webform` (треба увімкнути в `field_paragraphs`), `system_masthead`, `investor_journey`, `glance_block`, `why_opportunities`, `platforms_systems`, `promo_split`, `cards`, `accordions`, `featured_contents`.

`hero_two_cols` + вкладений `webform` — запасний варіант, не v1 (інакше сторінка не схожа на головну).

### Є на `page` (nid 960), на лендінг **не копіюємо як поля**

`field_steps_to_obtain`, `field_objectives`, `field_areas_of_work`, `field_top_overview_section`, `field_target_audience`, `field_service_channels`, `field_service_duration`, `field_service_cost`, `field_payment_options`, `field_provided_languages`, `field_keywords` (FAQ), `field_phone_number`, `field_email_address`, `field_display_on_services`, `field_taxonomy`.

Контент з них **переписуємо** в paragraphs / FAQ / форму.

Телефон і email: або жорстко в темі лендінгу з існуючих контактів, або два рядки в body/promo. Окремі поля на `landing_page` не обов’язкові.

### Що додати (мінімум)

| Зміна | Навіщо | Обов’язково для v1? |
|---|---|---|
| Увімкнути paragraph `webform` у `landing_page.field_paragraphs` | Форма під hero+лого, не внизу після FAQ | Так |
| Новий **webform** `customs_clearance_quote` | Поля партії | Так |
| View або ручний `platforms_systems` на 5 органів | Logo rail як на `/` | Так |
| Підключити форму до CRM webhook | Як contact / setup cost | Бажано в v1 |
| Redirect 301 зі старого URL | SEO | Так |
| Block visibility: сховати setup pre-footer на цьому path | Не змішувати офери | Так |
| Boolean `field_hide_global_cta` на landing_page | Щоб редактори не залежили від path visibility | Ні, можна path |

Новий node type **не додаємо**.

---

## 8. URL, каталог, аналітика

- Новий alias (чернетка): `/customs-clearance-saudi-arabia` + `/ar/...`
- Старий: `/services/customs-broker-services-saudi-arabia` → 301
- Каталог `/services`: одна картка з новим URL, категорію можна лишити Government Relations або змінити на Logistics — узгодити
- Metatag: title на кшталт `Customs clearance in Saudi Arabia | Motaded`; description без подвійного «Saudi Arabia support in Saudi Arabia»
- Canonical на новий URL
- UTM на Ads і WhatsApp prefill: `I need a customs clearance quote`

---

## 9. Відкриті питання до клієнта

1. Фінальне формулювання: Motaded = ліцензований брокер, чи координатор мережі брокерів, чи обидва?
2. Чи реально дають Chinese на документах?
3. Бажаний URL.
4. Чи потрібен окремий intent «brokerage company setup» в тій самій формі?
5. Чи можна показувати діапазон fee Motaded («from … SAR / shipment»), чи лише «quote»?
6. Підтвердити телефон/WhatsApp і чи інший номер для митниці vs сетап.

Поки пункти 1 і 5 не закриті — на лендінгу не малюємо бейджів і не ставимо ціну в schema.

---

## 10. Порядок робіт після затвердження документа

1. Затвердити блоки + формулювання ліцензії.
2. Згенерувати HERO / STEPS / DOCS / BROKER-SETUP; забрати офіційні лого з platform media.
3. Створити webform + лендінг-ноду EN/AR.
4. Редірект, вимкнути setup-CTA, картка в каталозі.
5. Підключити CRM, перевірити мобайлі форму в hero.
