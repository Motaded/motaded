<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Sample Market Insight nodes for local/staging demos.
 */
final class MarketInsightSampleCommands extends DrushCommands {

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly LanguageManagerInterface $languageManager,
  ) {}

  /**
   * Creates sample market_insight nodes (Saudi at a glance style).
   */
  #[CLI\Command(name: 'motaded:market-insight-sample', aliases: ['mmis'])]
  #[CLI\Option(name: 'reset', description: 'Delete all market_insight nodes, then recreate samples.')]
  #[CLI\Usage(name: 'drush motaded:market-insight-sample', description: 'Create sample insights (skip existing titles).')]
  #[CLI\Usage(name: 'drush motaded:market-insight-sample --reset -y', description: 'Replace all market insights with samples.')]
  public function generate(array $options = ['reset' => FALSE]): void {
    $reset = filter_var($options['reset'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $node_storage = $this->entityTypeManager->getStorage('node');

    if ($reset) {
      if (!$this->io()->confirm('Delete ALL nodes of type market_insight?')) {
        $this->logger()->notice('Aborted.');
        return;
      }
      $nids = $node_storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', 'market_insight')
        ->execute();
      if ($nids) {
        $nodes = $node_storage->loadMultiple($nids);
        $node_storage->delete($nodes);
        $this->logger()->success('Deleted @count market_insight node(s).', ['@count' => count($nids)]);
      }
    }

    $langcode = $this->languageManager->getDefaultLanguage()->getId();

    $economy = $this->getOrCreateTerm('market_insight_category', 'Economy', $langcode);
    $investment = $this->getOrCreateTerm('market_insight_category', 'Investment', $langcode);
    $sector_term = $this->getOrCreateTerm('sector', 'General', $langcode);
    $tech = $this->getOrCreateTerm('sector', 'Technology', $langcode);
    $saudi = $this->getOrCreateTerm('region', 'Saudi Arabia', $langcode);
    $gcc = $this->getOrCreateTerm('region', 'GCC', $langcode);

    $samples = [
      [
        'title' => 'Saudi Arabia GDP Growth',
        'field_stat_prefix' => '+',
        'field_stat_value' => '8.7',
        'field_stat_suffix' => '%',
        'field_stat_label' => 'Annual GDP growth',
        'summary' => 'Saudi Arabia shows strong economic growth driven by Vision 2030 initiatives.',
        'body' => '<p>Saudi Arabia continues to expand its economy with strong growth across multiple sectors, supported by diversification and investment in infrastructure and services.</p>',
        'field_key_takeaways' => [
          'value' => '<ul><li>Growth remains broad-based across non-oil activities.</li><li>Public investment supports medium-term expansion.</li></ul>',
          'format' => 'basic_html',
        ],
        'field_category' => $economy,
        'field_sector' => $sector_term,
        'field_region' => $saudi,
        'field_source_text' => 'World Bank',
        'field_source_link' => ['uri' => 'https://www.worldbank.org/', 'title' => ''],
        'field_year' => 2024,
        'field_featured' => TRUE,
        'field_highlight' => TRUE,
        'field_order' => 0,
      ],
      [
        'title' => 'Foreign ownership',
        'field_stat_prefix' => '',
        'field_stat_value' => '100',
        'field_stat_suffix' => '%',
        'field_stat_label' => 'Foreign ownership',
        'summary' => 'Full foreign ownership is permitted in key sectors under updated investment rules.',
        'body' => '<p>Regulatory reforms continue to attract international investors with clearer frameworks for foreign-owned businesses.</p>',
        'field_key_takeaways' => [
          'value' => '<ul><li>Foreign investors can own 100% in many activities.</li><li>Check sector-specific licensing requirements.</li></ul>',
          'format' => 'basic_html',
        ],
        'field_category' => $investment,
        'field_sector' => $tech,
        'field_region' => $saudi,
        'field_source_text' => 'MISA',
        'field_source_link' => ['uri' => 'https://www.misa.gov.sa/', 'title' => ''],
        'field_year' => 2024,
        'field_featured' => TRUE,
        'field_highlight' => FALSE,
        'field_order' => 1,
      ],
      [
        'title' => 'Global economy ranking',
        'field_stat_prefix' => '',
        'field_stat_value' => 'Top 20',
        'field_stat_suffix' => '',
        'field_stat_label' => 'Global economy',
        'summary' => 'The Kingdom ranks among the largest economies by GDP in global comparisons.',
        'body' => '<p>Macroeconomic indicators place Saudi Arabia within the top tier of global economies when measured by nominal GDP.</p>',
        'field_key_takeaways' => [
          'value' => '<ul><li>Ranking depends on methodology (nominal vs. PPP).</li><li>Use official series for year-on-year comparisons.</li></ul>',
          'format' => 'basic_html',
        ],
        'field_category' => $economy,
        'field_sector' => $sector_term,
        'field_region' => $gcc,
        'field_source_text' => 'IMF',
        'field_source_link' => ['uri' => 'https://www.imf.org/', 'title' => ''],
        'field_year' => 2024,
        'field_featured' => TRUE,
        'field_highlight' => FALSE,
        'field_order' => 2,
      ],
      [
        'title' => 'Non-oil sector share (sample listing)',
        'field_stat_prefix' => '',
        'field_stat_value' => '50',
        'field_stat_suffix' => '%',
        'field_stat_label' => 'Non-oil GDP contribution (illustrative)',
        'summary' => 'Illustrative stat for listing cards; replace with official series in production.',
        'body' => '<p>This fourth node is not featured, useful for testing <code>/insights</code> style listings.</p>',
        'field_key_takeaways' => [
          'value' => '<ul><li>Sample stat only — replace with GASTAT figures.</li><li>Teaser image uses Card / listing image when set.</li></ul>',
          'format' => 'basic_html',
        ],
        'field_category' => $economy,
        'field_sector' => $tech,
        'field_region' => $saudi,
        'field_source_text' => 'GASTAT',
        'field_source_link' => ['uri' => 'https://www.stats.gov.sa/', 'title' => ''],
        'field_year' => 2023,
        'field_featured' => FALSE,
        'field_highlight' => FALSE,
        'field_order' => 10,
      ],
    ];

    $created = 0;
    $skipped = 0;
    foreach ($samples as $row) {
      $exists = $node_storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', 'market_insight')
        ->condition('title', $row['title'])
        ->range(0, 1)
        ->execute();
      if ($exists && !$reset) {
        $skipped++;
        $this->io()->writeln(sprintf('Skip (exists): %s', $row['title']));
        continue;
      }

      $values = [
        'type' => 'market_insight',
        'title' => $row['title'],
        'langcode' => $langcode,
        'uid' => 1,
        'status' => NodeInterface::PUBLISHED,
        'promote' => 0,
        'body' => [
          'value' => $row['body'],
          'summary' => $row['summary'],
          'format' => 'basic_html',
        ],
        'field_stat_prefix' => $row['field_stat_prefix'],
        'field_stat_value' => $row['field_stat_value'],
        'field_stat_suffix' => $row['field_stat_suffix'],
        'field_stat_label' => $row['field_stat_label'],
        'field_category' => ['target_id' => $row['field_category']],
        'field_sector' => ['target_id' => $row['field_sector']],
        'field_region' => ['target_id' => $row['field_region']],
        'field_source_text' => $row['field_source_text'],
        'field_year' => $row['field_year'],
        'field_featured' => (bool) $row['field_featured'],
        'field_highlight' => (bool) $row['field_highlight'],
        'field_order' => $row['field_order'],
        'field_insight_shell' => 'platform',
        'field_key_takeaways' => $row['field_key_takeaways'],
      ];
      if ($row['field_source_link']['uri'] !== '') {
        $values['field_source_link'] = $row['field_source_link'];
      }

      $node = $node_storage->create($values);
      $node->save();
      $created++;
      $this->io()->writeln(sprintf('Created nid=%d: %s', (int) $node->id(), $row['title']));
    }

    $this->logger()->success(sprintf(
      'Created %d sample node(s); skipped %d (use --reset to replace all).',
      $created,
      $skipped
    ));
  }

  /**
   * Gets an existing term by vocabulary + name or creates it.
   */
  private function getOrCreateTerm(string $vid, string $name, string $langcode): int {
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $tids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('vid', $vid)
      ->condition('name', $name)
      ->range(0, 1)
      ->execute();
    if ($tids) {
      return (int) reset($tids);
    }
    $term = Term::create([
      'vid' => $vid,
      'name' => $name,
      'langcode' => $langcode,
    ]);
    $term->save();
    $this->io()->writeln(sprintf('Created term %s / %s (tid=%d)', $vid, $name, (int) $term->id()));
    return (int) $term->id();
  }

}
