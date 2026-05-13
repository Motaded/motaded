<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\SectorIndicators;

/**
 * World Bank WDI + GASTAT mapping for sector cards and sector pages.
 *
 * @see docs/sector-indicators-world-bank.md
 */
final class SectorIndicatorDefinitions {

  public const SOURCE_NAME = 'World Bank';

  public const SOURCE_URL = 'https://data.worldbank.org/country/saudi-arabia';

  public const PROVIDER = 'worldbank';

  public const GASTAT_PROVIDER = 'gastat';

  public const GASTAT_SOURCE_NAME = 'General Authority for Statistics (Saudi Arabia)';

  public const GASTAT_SOURCE_URL = 'https://www.stats.gov.sa/en';

  /** GASTAT Open Data API (JSON). */
  public const GASTAT_API_URL = 'https://api.stats.gov.sa/v1/stats/DPV_NA_ISIC_Y_EFNA0201?format=JSON';

  /** Shared “Saudi economy” series shown on every sector page (imported once). */
  public const ECONOMY_WIDE_SECTOR_KEY = 'economy_wide';

  /** Special GASTAT row matcher: transport + (storage|communication). */
  public const GASTAT_MATCH_TRANSPORT_STORAGE_COMM = 'transport_storage_comm';

  /** GASTAT: activity label contains substring (case-insensitive), excludes “excluding”. */
  public const GASTAT_MATCH_MANUFACTURING = 'manufacturing';

  public const GASTAT_MATCH_CONSTRUCTION = 'construction';

  public const GASTAT_MATCH_ELECTRICITY_GAS_WATER = 'electricity_gas_water';

  public const INDICATOR_LOGISTICS_GASTAT_TRANSPORT_COMM_GROWTH = 'logistics_gastat_transport_comm_growth';

  public const INDICATOR_CONSTRUCTION_GASTAT_GDP_GROWTH = 'construction_gastat_gdp_growth';

  public const INDICATOR_MANUFACTURING_GASTAT_GDP_GROWTH = 'manufacturing_gastat_gdp_growth';

  /**
   * Non–World Bank series written to {sector_indicator_points} with provider = gastat.
   *
   * @var list<array{
   *   sector_key: string,
   *   indicator_key: string,
   *   external_code: string,
   *   format: string,
   *   kpi_label: string,
   *   gastat_match: string
   * }>
   */
  public const GASTAT_IMPORT_ROWS = [
    [
      'sector_key' => 'logistics',
      'indicator_key' => self::INDICATOR_LOGISTICS_GASTAT_TRANSPORT_COMM_GROWTH,
      'external_code' => 'DPV_NA_ISIC_Y_EFNA0201',
      'format' => 'percent_plain',
      'kpi_label' => 'GDP growth, transport storage & communication',
      'gastat_match' => self::GASTAT_MATCH_TRANSPORT_STORAGE_COMM,
    ],
    [
      'sector_key' => 'construction',
      'indicator_key' => self::INDICATOR_CONSTRUCTION_GASTAT_GDP_GROWTH,
      'external_code' => 'DPV_NA_ISIC_Y_EFNA0201',
      'format' => 'percent_plain',
      'kpi_label' => 'GDP growth, construction',
      'gastat_match' => self::GASTAT_MATCH_CONSTRUCTION,
    ],
    [
      'sector_key' => 'manufacturing',
      'indicator_key' => self::INDICATOR_MANUFACTURING_GASTAT_GDP_GROWTH,
      'external_code' => 'DPV_NA_ISIC_Y_EFNA0201',
      'format' => 'percent_plain',
      'kpi_label' => 'GDP growth, manufacturing',
      'gastat_match' => self::GASTAT_MATCH_MANUFACTURING,
    ],
    [
      'sector_key' => 'energy',
      'indicator_key' => 'energy_gastat_electricity_gas_water_growth',
      'external_code' => 'DPV_NA_ISIC_Y_EFNA0201',
      'format' => 'percent_plain',
      'kpi_label' => 'GDP growth, electricity, gas & water',
      'gastat_match' => self::GASTAT_MATCH_ELECTRICITY_GAS_WATER,
    ],
  ];

  /**
   * Canonical keys for sector_page hubs (must match _motaded_custom_sector_key_from_sector_page()).
   *
   * @return list<string>
   */
  public static function sectorPageKeys(): array {
    return [
      'tourism',
      'energy',
      'manufacturing',
      'technology',
      'finance',
      'logistics',
      'healthcare',
      'construction',
    ];
  }

  /**
   * GASTAT YoY GDP growth chart: sector_key => match token for GastatStatisticsClient.
   *
   * Sectors without entry have no GASTAT time-series block.
   *
   * @return array<string, string>
   */
  public static function sectorGastatChartMatch(): array {
    return [
      'logistics' => self::GASTAT_MATCH_TRANSPORT_STORAGE_COMM,
      'construction' => self::GASTAT_MATCH_CONSTRUCTION,
      'manufacturing' => self::GASTAT_MATCH_MANUFACTURING,
      'energy' => self::GASTAT_MATCH_ELECTRICITY_GAS_WATER,
    ];
  }

  /**
   * All imported series (headline + extra for homepage cards and sector pages).
   *
   * @var list<array{
   *   sector_key: string,
   *   sector_label: string,
   *   indicator_key: string,
   *   wb_code: string,
   *   format: string,
   *   kpi_label: string,
   *   card_kpi_label?: string,
   *   role: string,
   *   icon_key?: string
   * }>
   */
  public const ALL_ROWS = [
    // —— economy_wide: same WB series for every sector page “trade / GDP” context ——
    ['sector_key' => self::ECONOMY_WIDE_SECTOR_KEY, 'sector_label' => 'Saudi Arabia', 'indicator_key' => 'economy_merch_exports', 'wb_code' => 'TX.VAL.MRCH.CD.WT', 'format' => 'usd_short', 'kpi_label' => 'Merchandise exports (current US$)', 'role' => 'extra'],
    ['sector_key' => self::ECONOMY_WIDE_SECTOR_KEY, 'sector_label' => 'Saudi Arabia', 'indicator_key' => 'economy_merch_imports', 'wb_code' => 'TM.VAL.MRCH.CD.WT', 'format' => 'usd_short', 'kpi_label' => 'Merchandise imports (current US$)', 'role' => 'extra'],
    ['sector_key' => self::ECONOMY_WIDE_SECTOR_KEY, 'sector_label' => 'Saudi Arabia', 'indicator_key' => 'economy_trade_pct_gdp', 'wb_code' => 'NE.TRD.GNFS.ZS', 'format' => 'percent_plain', 'kpi_label' => 'Trade (% of GDP)', 'role' => 'extra'],

    ['sector_key' => 'tourism', 'sector_label' => 'Tourism', 'indicator_key' => 'tourism_arrivals', 'wb_code' => 'ST.INT.ARVL', 'format' => 'compact_int', 'kpi_label' => 'International tourist arrivals', 'card_kpi_label' => 'Tourist arrivals', 'role' => 'headline', 'icon_key' => 'tourism'],
    ['sector_key' => 'tourism', 'sector_label' => 'Tourism', 'indicator_key' => 'tourism_receipts', 'wb_code' => 'ST.INT.RCPT.CD', 'format' => 'usd', 'kpi_label' => 'International tourism receipts', 'role' => 'extra'],
    ['sector_key' => 'tourism', 'sector_label' => 'Tourism', 'indicator_key' => 'tourism_expenditure', 'wb_code' => 'ST.INT.XPND.CD', 'format' => 'usd', 'kpi_label' => 'International tourism expenditure (current US$)', 'role' => 'extra'],

    ['sector_key' => 'energy', 'sector_label' => 'Energy', 'indicator_key' => 'energy_oil_rents', 'wb_code' => 'NY.GDP.PETR.RT.ZS', 'format' => 'percent_plain', 'kpi_label' => 'Oil rents (% of GDP)', 'card_kpi_label' => 'Oil rents (% GDP)', 'role' => 'headline', 'icon_key' => 'energy'],
    ['sector_key' => 'energy', 'sector_label' => 'Energy', 'indicator_key' => 'energy_use_per_capita', 'wb_code' => 'EG.USE.PCAP.KG.OE', 'format' => 'index', 'kpi_label' => 'Energy use (kg of oil equivalent per capita)', 'role' => 'extra'],
    ['sector_key' => 'energy', 'sector_label' => 'Energy', 'indicator_key' => 'energy_renewable_final_pct', 'wb_code' => 'EG.FEC.RNEW.ZS', 'format' => 'percent_plain', 'kpi_label' => 'Renewable energy consumption (% of total final energy consumption)', 'role' => 'extra'],

    ['sector_key' => 'manufacturing', 'sector_label' => 'Manufacturing', 'indicator_key' => 'mfg_va_pct_gdp', 'wb_code' => 'NV.IND.MANF.ZS', 'format' => 'percent_plain', 'kpi_label' => 'Manufacturing, value added (% of GDP)', 'card_kpi_label' => 'Manufacturing value added (% GDP)', 'role' => 'headline', 'icon_key' => 'manufacturing'],
    ['sector_key' => 'manufacturing', 'sector_label' => 'Manufacturing', 'indicator_key' => 'mfg_va_usd', 'wb_code' => 'NV.IND.MANF.CD', 'format' => 'usd', 'kpi_label' => 'Manufacturing, value added (current US$)', 'role' => 'extra'],
    ['sector_key' => 'manufacturing', 'sector_label' => 'Manufacturing', 'indicator_key' => 'mfg_exports_pct_merch', 'wb_code' => 'TX.VAL.MANF.ZS.UN', 'format' => 'percent_plain', 'kpi_label' => 'Manufactures exports (% of merchandise exports)', 'role' => 'extra'],

    ['sector_key' => 'technology', 'sector_label' => 'Technology', 'indicator_key' => 'tech_high_exports_pct', 'wb_code' => 'TX.VAL.TECH.MF.ZS', 'format' => 'percent_plain', 'kpi_label' => 'High-technology exports (% of manufactured exports)', 'card_kpi_label' => 'High-tech exports (% mfg exports)', 'role' => 'headline', 'icon_key' => 'technology'],
    ['sector_key' => 'technology', 'sector_label' => 'Technology', 'indicator_key' => 'tech_ict_service_exports', 'wb_code' => 'BX.GSR.CCIS.ZS', 'format' => 'usd', 'kpi_label' => 'ICT service exports (BoP, current US$)', 'role' => 'extra'],
    ['sector_key' => 'technology', 'sector_label' => 'Technology', 'indicator_key' => 'tech_internet_users', 'wb_code' => 'IT.NET.USER.ZS', 'format' => 'percent_plain', 'kpi_label' => 'Individuals using the Internet (% of population)', 'card_kpi_label' => 'Individuals using the Internet', 'role' => 'extra'],

    ['sector_key' => 'finance', 'sector_label' => 'Finance', 'indicator_key' => 'finance_private_credit', 'wb_code' => 'FS.AST.PRVT.GD.ZS', 'format' => 'percent_plain', 'kpi_label' => 'Domestic credit to private sector (% of GDP)', 'card_kpi_label' => 'Domestic credit to private sector (% GDP)', 'role' => 'headline', 'icon_key' => 'finance'],
    ['sector_key' => 'finance', 'sector_label' => 'Finance', 'indicator_key' => 'finance_bank_branches', 'wb_code' => 'FB.CBK.BRCH.P5', 'format' => 'per_100', 'kpi_label' => 'Commercial bank branches (per 100,000 adults)', 'role' => 'extra'],
    ['sector_key' => 'finance', 'sector_label' => 'Finance', 'indicator_key' => 'finance_broad_money_pct_gdp', 'wb_code' => 'FM.LBL.BMNY.GD.ZS', 'format' => 'percent_plain', 'kpi_label' => 'Broad money (% of GDP)', 'role' => 'extra'],

    ['sector_key' => 'logistics', 'sector_label' => 'Logistics', 'indicator_key' => 'logistics_lpi', 'wb_code' => 'LP.LPI.OVRL.XQ', 'format' => 'index', 'kpi_label' => 'Logistics performance index (1–5)', 'card_kpi_label' => 'Logistics performance index (LPI)', 'role' => 'headline', 'icon_key' => 'logistics'],
    ['sector_key' => 'logistics', 'sector_label' => 'Logistics', 'indicator_key' => 'logistics_air_passengers', 'wb_code' => 'IS.AIR.PSGR', 'format' => 'compact_int', 'kpi_label' => 'Air transport, passengers carried', 'role' => 'extra'],
    ['sector_key' => 'logistics', 'sector_label' => 'Logistics', 'indicator_key' => 'logistics_key_air_freight', 'wb_code' => 'IS.AIR.GOOD.MT.K1', 'format' => 'air_freight_wb', 'kpi_label' => 'Air freight (thousand ton-km)', 'role' => 'extra'],
    ['sector_key' => 'logistics', 'sector_label' => 'Logistics', 'indicator_key' => 'logistics_key_container_teu', 'wb_code' => 'IS.SHP.GOOD.TU', 'format' => 'teu_suffix', 'kpi_label' => 'Container port traffic (TEU)', 'role' => 'extra'],
    ['sector_key' => 'logistics', 'sector_label' => 'Logistics', 'indicator_key' => 'logistics_rail_network_km', 'wb_code' => 'IS.RRS.TOTL.KM', 'format' => 'compact_int', 'kpi_label' => 'Rail lines (total route-km)', 'role' => 'extra'],

    ['sector_key' => 'healthcare', 'sector_label' => 'Healthcare', 'indicator_key' => 'health_exp_pct_gdp', 'wb_code' => 'SH.XPD.CHEX.GD.ZS', 'format' => 'percent_plain', 'kpi_label' => 'Current health expenditure (% of GDP)', 'card_kpi_label' => 'Health expenditure (% GDP)', 'role' => 'headline', 'icon_key' => 'healthcare'],
    ['sector_key' => 'healthcare', 'sector_label' => 'Healthcare', 'indicator_key' => 'health_hospital_beds', 'wb_code' => 'SH.MED.BEDS.ZS', 'format' => 'per_100', 'kpi_label' => 'Hospital beds (per 1,000 people)', 'role' => 'extra'],
    ['sector_key' => 'healthcare', 'sector_label' => 'Healthcare', 'indicator_key' => 'health_life_expectancy', 'wb_code' => 'SP.DYN.LE00.IN', 'format' => 'index', 'kpi_label' => 'Life expectancy at birth (years)', 'role' => 'extra'],

    ['sector_key' => 'construction', 'sector_label' => 'Construction', 'indicator_key' => 'construction_gcf_fixed_pct_gdp', 'wb_code' => 'NE.GDI.FTOT.ZS', 'format' => 'percent_plain', 'kpi_label' => 'Gross fixed capital formation (% of GDP)', 'card_kpi_label' => 'Gross fixed capital formation (% GDP)', 'role' => 'headline', 'icon_key' => 'construction'],
    ['sector_key' => 'construction', 'sector_label' => 'Construction', 'indicator_key' => 'construction_urban_pop_growth', 'wb_code' => 'SP.URB.GROW', 'format' => 'percent_plain', 'kpi_label' => 'Urban population growth (annual %)', 'role' => 'extra'],
  ];

  /**
   * @return list<array<string, mixed>>
   */
  public static function headlineRows(): array {
    $out = [];
    foreach (self::ALL_ROWS as $row) {
      if (($row['role'] ?? '') === 'headline') {
        $out[] = $row;
      }
    }
    return $out;
  }

  /**
   * @return array<string, mixed>|null
   */
  public static function headlineForSectorKey(string $sectorKey): ?array {
    foreach (self::headlineRows() as $row) {
      if (($row['sector_key'] ?? '') === $sectorKey) {
        return $row;
      }
    }
    return NULL;
  }

  /**
   * World Bank rows for a sector page (excluding economy_wide — those are merged separately).
   *
   * @return list<array<string, mixed>>
   */
  public static function worldBankRowsForSectorPage(string $sectorKey): array {
    $out = [];
    foreach (self::ALL_ROWS as $row) {
      if (($row['sector_key'] ?? '') === $sectorKey) {
        $out[] = $row;
      }
    }
    return $out;
  }

  /**
   * economy_wide rows for global key stats on sector pages.
   *
   * @return list<array<string, mixed>>
   */
  public static function economyWideRows(): array {
    return self::worldBankRowsForSectorPage(self::ECONOMY_WIDE_SECTOR_KEY);
  }

}
