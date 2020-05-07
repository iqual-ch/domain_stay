<?php

/**
 * @file
 */

/**
 *
 */
function pagedesigner_domain_source_alter(&$source, $path, array $options) {
  \Drupal::service('page_cache_kill_switch')->trigger();
  \Drupal::service('domain_stay.source_alter')->setSource($source, $path, $options);
}

/**
 *
 */
function pagedesigner_page_attachments(array &$attachments) {
  \Drupal::service('domain_stay.source_alter')->alterCanonical($attachments);
}
