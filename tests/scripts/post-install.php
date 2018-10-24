<?php

include_once drupal_get_path('module', 'lightning_core') . '/tests/scripts/post-install.php';

Drupal::configFactory()
  ->getEditable('lightning_api.settings')
  ->set('entity_json', TRUE)
  ->set('bundle_docs', TRUE)
  ->save();

// Install the Page content type and Views UI for testing.
Drupal::service('module_installer')->install(['lightning_page', 'views_ui']);
