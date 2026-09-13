<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\DataSaudi;

/**
 * Outcome of one DataSaudi Tesseract request (or a completed page set).
 */
final class DataSaudiFetchResult {

  public const OK = 'ok';

  public const EMPTY = 'empty';

  public const ERROR = 'error';

  /**
   * @param list<array<string, mixed>> $rows
   */
  public function __construct(
    public readonly string $status,
    public readonly array $rows = [],
    public readonly string $message = '',
    public readonly int $reportedTotal = 0,
  ) {}

  public static function ok(array $rows, int $reportedTotal = 0): self {
    if ($rows === []) {
      return new self(self::EMPTY, [], 'DataSaudi returned zero usable rows.', $reportedTotal);
    }
    return new self(self::OK, $rows, '', $reportedTotal !== 0 ? $reportedTotal : count($rows));
  }

  public static function empty(string $message, int $reportedTotal = 0): self {
    return new self(self::EMPTY, [], $message, $reportedTotal);
  }

  public static function error(string $message): self {
    return new self(self::ERROR, [], $message);
  }

  public function isUsable(): bool {
    return $this->status === self::OK && $this->rows !== [];
  }

}
