<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\DataSaudi;

/**
 * Result of a DataSaudi trade import attempt.
 */
final class DataSaudiImportOutcome {

  public function __construct(
    public readonly bool $locked,
    public readonly bool $balanceReplaced,
  ) {}

  public static function locked(): self {
    return new self(TRUE, FALSE);
  }

  public function succeeded(): bool {
    return !$this->locked && $this->balanceReplaced;
  }

}
