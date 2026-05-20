<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Logo;

/**
 * Configuration for node logo import (chambers, platforms, etc.).
 */
final class LogoImportConfig {

  /**
   * @param list<array{0: string, 1: string}> $sort
   */
  public function __construct(
    public readonly string $bundle,
    public readonly string $imageDirRel,
    public readonly string $officialDirRel,
    public readonly string $publicUriDir,
    public readonly string $mediaNamePrefix,
    public readonly string $aliasParent,
    public readonly string $defaultSlug,
    public readonly string $overridesFile,
    public readonly string $datasetFile,
    public readonly ?string $brandSitesFile,
    public readonly string $userAgent,
    public readonly array $sort,
    public readonly string $label,
  ) {}

  public static function chambers(): self {
    return new self(
      bundle: 'chamber',
      imageDirRel: '/data/chambers/images',
      officialDirRel: '/data/chambers/official',
      publicUriDir: 'public://chambers/logos',
      mediaNamePrefix: 'Chamber logo: ',
      aliasParent: 'chambers',
      defaultSlug: 'chamber',
      overridesFile: 'data/chamber_import.logos.php',
      datasetFile: 'data/chamber_import.dataset.php',
      brandSitesFile: NULL,
      userAgent: 'Mozilla/5.0 (compatible; MotadedChamberLogoImport/1.0)',
      sort: [
        ['field_priority', 'ASC'],
        ['title', 'ASC'],
      ],
      label: 'Chamber',
    );
  }

  public static function platforms(): self {
    return new self(
      bundle: 'platform',
      imageDirRel: '/data/platforms/images',
      officialDirRel: '/data/platforms/official',
      publicUriDir: 'public://platforms/logos',
      mediaNamePrefix: 'Platform logo: ',
      aliasParent: 'platforms',
      defaultSlug: 'platform',
      overridesFile: 'data/platform_import.logos.php',
      datasetFile: 'data/platform_import.dataset.php',
      brandSitesFile: 'data/platform_import.brand_sites.php',
      userAgent: 'Mozilla/5.0 (compatible; MotadedPlatformLogoImport/1.0)',
      sort: [
        ['title', 'ASC'],
      ],
      label: 'Platform',
    );
  }

}
