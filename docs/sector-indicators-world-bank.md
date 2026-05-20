# Секторні картки (KPI + тренд) — World Bank як primary source

Документ описує, як підключити **секторні** показники та міні-графіки до UI (головна: 1 KPI + sparkline; сторінка сектора: кілька KPI + повний графік), узгоджено з існуючим підходом **cron → API → нормалізація → БД → рендер без fetch з браузера**.

---

## 1. Джерела

| Пріоритет | Джерело | Примітки |
|-----------|---------|----------|
| Primary | **World Bank API v2** | `https://api.worldbank.org/v2/country/SAU/indicator/{CODE}?format=json` |
| Опційно | IMF | Окремий адаптер пізніше |
| Fallback | Manual / config | Якщо серія відсутня або API нестабільний для поля |

**Не викликати World Bank з фронта** — лише серверний імпорт (черга + cron), як для `market_indicators`.

---

## 2. Логіка «сектор = набір індикаторів»

У World Bank **немає** сутності «sector» як готового списку. У проєкті вводимо:

- **`sector_key`** — machine name (`tourism`, `energy`, …).
- **`indicator_key`** — канонічний ключ рядка/серії (`tourism_arrivals`, `energy_oil_rents`, …).
- **`wb_code`** — код WDI для запиту.

Один сектор на картці головної = **один primary `indicator_key`** для великого KPI + **одна серія років** для sparkline (той самий код або узгоджена «headline» серія з конфігу).

На **сторінці сектора** — до **3 KPI** (три різні `indicator_key` або одна серія + два додаткові) + **повний графік** (та сама або розширена серія).

---

## 3. Mapping WDI → сектори (v1 пропозиція)

Базовий URL:  
`GET https://api.worldbank.org/v2/country/SAU/indicator/{CODE}?format=json&per_page=500`

| sector_key | Роль | indicator_key | World Bank code | Примітка UI |
|------------|------|---------------|-----------------|-------------|
| **tourism** | headline | `tourism_arrivals` | `ST.INT.ARVL` | Arrivals |
| tourism | extra | `tourism_receipts` | `ST.INT.RCPT.CD` | Receipts (US$) |
| **energy** | headline | `energy_oil_rents` | `NY.GDP.PETR.RT.ZS` | Oil rents % GDP |
| energy | extra | `energy_total_natural_resource_rents` | `NY.GDP.TOTL.RT.ZS` | Total natural resource rents % GDP |
| **manufacturing** | headline | `mfg_va_pct_gdp` | `NV.IND.MANF.ZS` | Manufacturing VA % GDP |
| manufacturing | extra | `mfg_va_usd` | `NV.IND.MANF.CD` | Manufacturing VA current US$ |
| **technology** | headline | `tech_internet_users` | `IT.NET.USER.ZS` | Internet users % |
| technology | extra | `tech_mobile_subscriptions` | `IT.CEL.SETS.P2` | Mobile cellular per 100 |
| **finance** | headline | `finance_private_credit` | `FS.AST.PRVT.GD.ZS` | Domestic credit to private sector % GDP |
| **logistics** | headline | `logistics_lpi` | `LP.LPI.OVRL.XQ` | LPI overall (1–5) |
| logistics | extra | `logistics_air_passengers` | `IS.AIR.PSGR` | Air passengers (absolute; формат окремо) |
| **healthcare** | headline | `health_exp_pct_gdp` | `SH.XPD.CHEX.GD.ZS` | Current health expenditure % GDP |
| **construction** | headline | `construction_gcf_pct_gdp` | `NE.GDI.TOTL.ZS` | Gross capital formation % GDP |

> **LP.LPI.*** та деякі серії оновлюються не щороку — у UI показувати фактичний `year` з останнього non-null значення.

---

## 4. Модель даних (узгодження з market pipeline)

Рекомендовано **окрема таблиця** (або префікс `sector_`), щоб не змішувати з `market_indicators` / `market_indicator_series`.

### 4.1 Таблиця `sector_indicator_points` (приклад)

| Поле | Тип | Опис |
|------|-----|------|
| id | serial | |
| sector_key | varchar(64) | `tourism`, … |
| indicator_key | varchar(64) | `tourism_arrivals`, … |
| wb_code | varchar(32) | Для аудиту/дебагу |
| year | int | |
| value_raw | numeric | Для графіків |
| value_display | varchar(128) | Для KPI тексту |
| source_name | varchar(255) | `World Bank` |
| source_url | text | |
| provider | varchar(32) | `worldbank` / `manual` |
| fetched_at | timestamp | |

**UNIQUE** `(sector_key, indicator_key, year)`.

Один імпорт: для кожної пари `(sector_key, indicator_key)` завантажити серію, нормалізувати, UPSERT. Sparkline на головній = останні **N років** (наприклад 6, як GDP) з **одного** `indicator_key` (headline).

### 4.2 Конфіг модуля (рекомендовано)

PHP/YAML-конфіг: для кожного `sector_key` — `headline_indicator`, `card_title`, `icon`, `sparkline_years`, опційно `detail_indicators[]` для сторінки сектора.

---

## 5. Пайплайн

Той самий патерн, що й для market indicators:

1. **Cron** (щодня / раз на добу) → post в **queue**.
2. **Worker** → HTTP до WB (таймаут, ретраї).
3. **Normalize** (%, US$, index, absolute — окремі formatters per `indicator_key` або per `type`).
4. **UPSERT** у `sector_indicator_points`.
5. **Invalidate** cache tag на кшти типу `sector_indicators`.
6. **Preprocess** (homepage + sector node) → Twig / SVG / `drupalSettings` **без** зовнішніх запитів з клієнта.

Спільний `WorldBankIndicatorClient` можна **розширити** методом `fetchSeries($code)` уже наявним — додати лише orchestrator `SectorIndicatorsImporter` + репозиторій.

---

## 6. UI

| Місце | Що показуємо |
|-------|----------------|
| **Homepage** (сітка карток) | 1 headline: `value_display` + `year`; sparkline з останніх N точок того ж `indicator_key`; лінк на sector page. |
| **Sector page** | До 3 KPI (три `indicator_key` або headline + 2 з `detail_indicators`); повний графік (ширша серія / той самий вікно років). |

Графік: як і для GDP — **inline SVG** з preprocess або один малий JS-модуль, якщо потрібна інтерактивність (на старті достатньо SVG).

---

## 7. Обмеження та fallback

- Деякі індикатори мають **рідкі** роки — показувати останній доступний рік у KPI.
- **IS.AIR.PSGR** — великі абсолюти; формат `value_display` окремо (M/B).
- **Manual** — для полів без стабільної серії або коли WB повертає порожньо: не стирати останній успішний snapshot.

---

## 8. Зв’язок з поточним кодом

У модулі `motaded_custom` уже є:

- `WorldBankIndicatorClient::fetchSeries($code)`
- черга `market_indicators_import`, нормалізація, БД, cache invalidation

**Наступний крок імплементації:** додати схему `sector_indicator_points`, конфіг mapping секторів, сервіс `SectorIndicatorsImporter`, чергу або розширити існуючий cron-воркер викликом секторного імпорту після market (або окрема черга `sector_indicators_import`).

---

*Версія: 1.0 — підготовка до секторних карток з World Bank.*
