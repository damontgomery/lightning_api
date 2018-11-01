<?php

namespace Drupal\Tests\lightning_api;

use Drupal\lightning_core\ConfigHelper as Config;
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

    if (! $this->container->get('module_handler')->moduleExists('lightning_page')) {
      $config = new Config(
        $this->container->get('extension.list.module')->get('lightning_page'),
        $this->container->get('config.factory'),
        $this->container->get('entity_type.manager')
      );
      // Remove any pre-existing configuration that may be left from over from
      // a _very_ old database fixture path, before the Page content type was
      // split out into its own module.
      $config->deleteAll();
    }
    $this->installModule('lightning_page');

    /** @var \Drupal\node\NodeTypeInterface $node_type */
    $node_type = NodeType::load('page');
    $dependencies = $node_type->getDependencies();
    $dependencies['enforced']['module'][] = 'lightning_page';
    $node_type->set('dependencies', $dependencies)->save();
  }

  /**
   * @AfterScenario
   */
  public function tearDown() {
    parent::tearDown();
  }

}
