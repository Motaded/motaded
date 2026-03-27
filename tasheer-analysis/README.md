# Tasheer → Webform (через form alter)

Файли **`drupal-webform-data.json`** та **`drupal-webform-rules-flat.json`** тут використовуються як джерело для генерації `webform_options` і `conditions` конфігу.

## Що є на сайті

- Вебформа **`tasheer_visa_appointment`** (`/tasheer-visa-appointment`) — стандартний Webform.
- Опції винесені в **конфіги Webform options** (як `yes_no`, `country_codes` у проєкті):
  - `tasheer_nationality`
  - `tasheer_visa_type`
  - `tasheer_number_of_entries`
  - `tasheer_visa_validity`
- Поля посилаються на них через `'#options': tasheer_*`.
- Залежні опції (Nationality → Visa Type → Number of Entries → Visa Validity) задаються через `hook_form_alter()` у `motaded_custom`.
- Conditions не захардкоджені: зберігаються у конфігу **`motaded_custom.tasheer_visa`**.

У submit є серверна валідація комбінації за цими conditions.

## Оновити списки опцій після зміни JSON

1. Покладіть оновлений **`tasheer-analysis/drupal-webform-data.json`**.
2. Згенеруйте YAML для Webform options + conditions config:

```bash
php scripts/tasheer_sync_webform_options.php
```

3. Імпорт конфігу та кеш:

```bash
ddev drush cim -y && ddev drush cr
```

(Без DDEV: `drush cim -y && drush cr` з кореня проєкту.)

## Де редагувати conditions в адмінці

- Сторінка: **`/admin/config/services/tasheer-visa-conditions`**
- Там можна змінити:
  - target webform id;
  - IDs `webform_options`;
  - YAML-мапи `conditions` (`visa_type_by_nationality`, `entries_by_nat_visa_type`, `validity_by_nat_visa_type_entry`);
  - fallback `json_path` і текст `No options available`.

## Де дивитися правила в JSON

- `conditions.visaTypeByNationality` — які типи візи для якої національності.
- `conditions.entriesByNatVisaType` — ключ `nationalityId|visaTypeId` → entries.
- `conditions.validityByNatVisaTypeEntry` — ключ `nationalityId|visaTypeId|entriesId` → validity.

Це корисно для скриптів, тестів або майбутньої логіки; поточна вебформа покладається на згенеровані **повні** списки опцій.
