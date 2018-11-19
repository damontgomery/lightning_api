<?php

namespace Drupal\Tests\lightning_api;

use Drupal\Tests\lightning_core\FixtureBase;

/**
 * Performs set-up and tear-down tasks before and after each test scenario.
 */
final class FixtureContext extends FixtureBase {

  /**
   * @BeforeScenario
   */
  public function setUp() {
    $this->config('lightning_api.settings')
      ->set('entity_json', TRUE)
      ->set('bundle_docs', TRUE)
      ->save();
  }

  /**
   * @AfterScenario
   */
  public function tearDown() {
    parent::tearDown();
  }

}
