# Motaded sector data

Configuration module for **external data sources** used on sector pages and related features (World Bank, GASTAT, future APIs).

## Admin UI

**Configuration → Web services → Motaded sector data** (`/admin/config/motaded/sector-data`).

Each **Sector data source** record documents:

| Area | Purpose |
|------|---------|
| **Sector key / Usage key** | Where in PHP/Twig the definition is consumed (e.g. `logistics` + `trade_volume_chart`). |
| **Provider** | `worldbank`, `gastat` (reserved), or `none`. |
| **World Bank** | Country ISO3, primary/secondary WDI codes, optional API base override. |
| **Merge & scale** | How series combine (`sum_series_by_year`) and display scaling (`divide_1e9` for USD bn). |
| **Caching** | `cache_max_age` merged into the sector page render cache; config tags invalidate pages when you save. |

**Usage keys** (examples): `trade_volume_chart` (World Bank trade volume on Logistics), `logistics_gdp_share_chart` (GASTAT YoY GDP growth for transport/storage/communication — see `motaded_custom` and `GastatStatisticsClient`).

## Defaults

On install, `config/install` ships **logistics_trade_volume_merch** (World Bank `TX.VAL.MRCH.CD.WT` + `TM.VAL.MRCH.CD.WT`) and **logistics_gastat_gdp_share_chart** (GASTAT GDP share chart). Existing sites get the latter via `motaded_sector_data_update_9001()` if missing.

## Extending

1. Add a new source with a new **usage key** (or same usage + different sector).
2. In `motaded_custom` (or another feature module), resolve the active source with `entity_type.manager` → `sector_data_source` storage (query `status`, `sector_key`, `usage_key`, sort `weight`).
3. Implement fetch/merge for additional **provider** / **merge_mode** combinations next to `TradeVolumeChartBuilder`.

Planned: Drush/cron import schedules keyed off `usage_key` + `provider` (not implemented in this first version).

## Dependencies

- `motaded_custom` depends on this module and reads definitions for the trade volume chart.
