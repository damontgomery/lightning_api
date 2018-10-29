<?php

namespace Drupal\Tests\lightning_api;

use Behat\Behat\Context\Context;
use Drupal\lightning_core\ConfigHelper as Config;

/**
 * Performs set-up and tear-down tasks before and after each test scenario.
 */
final class FixtureContext implements Context {

  /**
   * Original Lightning API settings config data.
   *
   * @var array
   */
  private $settings;

  /**
   * Whether Lightning Page was installed during the scenario.
   *
   * @var bool
   */
  private $pageInstalled;

  /**
   * Whether Views UI was installed during the scenario.
   *
   * @var bool
   */
  private $viewsUiInstalled;

  /**
   * @BeforeScenario
   */
  public function setUp() {
    $config_factory = \Drupal::configFactory();

    $this->settings = $config_factory->get('lightning_api.settings')->get();

    $config_factory->getEditable('lightning_api.settings')
      ->set('entity_json', TRUE)
      ->set('bundle_docs', TRUE)
      ->save();

    $module_handler = \Drupal::moduleHandler();
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $module_installer */
    $module_installer = \Drupal::service('module_installer');

    if (! $module_handler->moduleExists('lightning_page')) {
      // Remove any pre-existing configuration that may be left from over from
      // a _very_ old database fixture path, before the Page content type was
      // split out into its own module.
      $config_factory->getEditable('core.base_field_override.node.page.promote')->delete();
      $config_factory->getEditable('core.entity_form_display.node.page.default')->delete();
      $config_factory->getEditable('core.entity_view_display.node.page.default')->delete();
      $config_factory->getEditable('core.entity_view_display.node.page.teaser')->delete();
      $config_factory->getEditable('field.field.node.page.body')->delete();
      $config_factory->getEditable('node.type.page')->delete();

      $this->pageInstalled = $module_installer->install(['lightning_page']);
    }
    if (! $module_handler->moduleExists('views_ui')) {
      $this->viewsUiInstalled = $module_installer->install(['views_ui']);
    }
  }

  /**
   * @AfterScenario
   */
  public function tearDown() {
    \Drupal::configFactory()
      ->getEditable('lightning_api.settings')
      ->setData($this->settings)
      ->save(TRUE);

    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $module_installer */
    $module_installer = \Drupal::service('module_installer');

    if ($this->pageInstalled) {
      // Delete the config first so that subsequent scenarios do not fail with a
      // PreExistingConfigException.
      Config::forModule('lightning_page')->deleteAll();

      // Now uninstall the actual module.
      $module_installer->uninstall(['lightning_page']);
    }
    if ($this->viewsUiInstalled) {
      $module_installer->uninstall(['views_ui']);
    }
  }

}
