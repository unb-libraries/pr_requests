<?php
/**
 * @file
 * pr_requests.features.menu_links.inc
 */

/**
 * Implements hook_menu_default_menu_links().
 */
function pr_requests_menu_default_menu_links() {
  $menu_links = array();

  // Exported menu link: navigation_design--pr-requests:pr-requests.
  $menu_links['navigation_design--pr-requests:pr-requests'] = array(
    'menu_name' => 'navigation',
    'link_path' => 'pr-requests',
    'router_path' => 'pr-requests',
    'link_title' => 'Design & PR Requests',
    'options' => array(
      'attributes' => array(
        'title' => 'A form used to submit Design and PR Group requests.',
      ),
      'alter' => TRUE,
      'identifier' => 'navigation_design--pr-requests:pr-requests',
    ),
    'module' => 'system',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => 0,
    'customized' => 0,
    'parent_identifier' => 'navigation_requests:<front>',
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('Design & PR Requests');

  return $menu_links;
}
