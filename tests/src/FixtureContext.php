<?php

namespace Drupal\Tests\lightning_api;

use Drupal\node\Entity\NodeType;
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

    // If Lightning Core's FixtureContext created the test content type before
    // now, react to it retroactively.
    $node_type = NodeType::load('test');
    if ($node_type) {
      lightning_api_entity_insert($node_type);
    }

    $this->container->get('entity_type.bundle.info')->clearCachedBundles();
  }

  /**
   * @AfterScenario
   */
  public function tearDown() {
    // This pointless if statement is needed to evade a too-strict coding
    // standards rule.
    if (TRUE) {
      parent::tearDown();
    }
  }

}
