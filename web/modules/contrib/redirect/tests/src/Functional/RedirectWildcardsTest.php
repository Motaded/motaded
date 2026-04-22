<?php

declare(strict_types=1);

namespace Drupal\Tests\redirect\Functional;

use Drupal\redirect\Entity\Redirect;
use Drupal\Tests\BrowserTestBase;

/**
 * Test the functionality of the Redirect module wildcard support.
 *
 * @group redirect
 */
class RedirectWildcardsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['redirect'];

  /**
   * {@inheritdoc}
   */
  protected $profile = 'minimal';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Create a simple wildcard redirect.
    $redirect = Redirect::create();
    $redirect->setSource('/test/source/*');
    $redirect->setRedirect('/test/destination/successful');
    $redirect->setStatusCode(301);
    $redirect->save();

    // Create a more specific wildcard redirect, which should be used
    // when applicable, instead of the simple redirect that will also match.
    $redirect = Redirect::create();
    $redirect->setSource('/test/source/more-specific/*');
    $redirect->setRedirect('/test/destination/more-specific/successful');
    $redirect->setStatusCode(301);
    $redirect->save();

    // Create a more redirect with wildcard in both source and destination,
    // to check that the matching part in the source replaces the wildcard
    // in destination.
    $redirect = Redirect::create();
    $redirect->setSource('/test/source/wildcard-replace/*');
    $redirect->setRedirect('/test/destination/wildcard-replace/successful/*');
    $redirect->setStatusCode(301);
    $redirect->save();

    // Enable wildcard support in configuration.
    $this->config('redirect.settings')
      ->set('wildcard_enabled', TRUE)
      ->save();

  }

  /**
   * Test simple redirect.
   */
  public function testSimpleRedirect() {
    $this->drupalGet('test/source/whatever');
    $this->assertSession()->addressEquals('test/destination/successful');
  }

  /**
   * Test the most specific redirect is used.
   */
  public function testRedirectSpecificity() {
    $this->drupalGet('test/source/whatever');
    $this->assertSession()->addressEquals('/test/destination/successful');

    $this->drupalGet('test/source/more-specific/whatever');
    $this->assertSession()->addressEquals('/test/destination/more-specific/successful');
  }

  /**
   * Test the matching part from source replaces the wildcard in destination.
   */
  public function testRedirectWilcardInDestination() {
    $this->drupalGet('/test/source/wildcard-replace/whatever');
    $this->assertSession()->addressEquals('/test/destination/wildcard-replace/successful/whatever');
  }

  /**
   * Test the config option to disable wildcard support.
   */
  public function testConfigOption() {

    // Disable wildcard support in configuration.
    $this->config('redirect.settings')->set('wildcard_enabled', FALSE)->save();

    $this->drupalGet('/test/source/whatever');
    $this->assertSession()->addressEquals('/test/source/whatever');

  }

}
