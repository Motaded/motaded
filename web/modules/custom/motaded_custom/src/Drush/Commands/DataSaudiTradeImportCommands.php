<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\motaded_custom\DataSaudi\DataSaudiTradeImporter;
use Drupal\motaded_custom\DataSaudi\DataSaudiTradeRepository;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Drush command for the DataSaudi monthly trade snapshot import.
 */
final class DataSaudiTradeImportCommands extends DrushCommands {

  public function __construct(
    private readonly DataSaudiTradeImporter $importer,
    private readonly DataSaudiTradeRepository $repository,
  ) {}

  /**
   * Fetch DataSaudi trade cubes and refresh local monthly snapshots.
   */
  #[CLI\Command(name: 'motaded:datasaudi-trade-import', aliases: ['mdti'])]
  #[CLI\Usage(name: 'drush mdti', description: 'Run DataSaudi trade import immediately (same as queue worker).')]
  public function import(): void {
    $outcome = $this->importer->importAll();
    if ($outcome->locked) {
      $this->logger()->warning('DataSaudi trade import skipped: another run holds the lock.');
      return;
    }
    $this->printSnapshot('balance', $this->repository->loadStatus('balance'), $this->repository->countBalance());
    $this->printSnapshot('product', $this->repository->loadStatus('product'), $this->repository->countProduct());
    foreach ($this->repository->productFlowCoverage() as $flow) {
      $this->io()->text(sprintf(
        'Product flow %d (%s): %d months %s..%s (not an annual total).',
        $flow['flow_id'],
        $flow['flow_key'],
        $flow['months'],
        $flow['period_min'],
        $flow['period_max']
      ));
    }
    $year = (int) gmdate('Y');
    $months = $this->repository->countBalanceMonthsForYear($year);
    $this->io()->text(sprintf(
      'Balance %d coverage: %d/12 months (incomplete years stay monthly).',
      $year,
      $months
    ));
    if ($outcome->succeeded()) {
      $this->logger()->success('DataSaudi trade import replaced both snapshots.');
      return;
    }
    $this->logger()->warning(sprintf(
      'DataSaudi trade import finished with gaps (balance=%s product=%s); previous snapshot kept where the fetch failed. See dblog.',
      $outcome->balanceReplaced ? 'replaced' : 'kept',
      $outcome->productReplaced ? 'replaced' : 'kept'
    ));
  }

  /**
   * @param array<string, mixed>|null $status
   */
  private function printSnapshot(string $dataset, ?array $status, int $count): void {
    if ($status === NULL) {
      $this->logger()->warning(sprintf('No %s snapshot in the database.', $dataset));
      return;
    }
    $this->io()->text(sprintf(
      '%s: %d rows, %s..%s, unit=%s, fetched_at=%s.',
      $dataset,
      $count,
      (string) $status['period_min'],
      (string) $status['period_max'],
      (string) $status['unit'],
      (string) $status['fetched_at']
    ));
  }

}
