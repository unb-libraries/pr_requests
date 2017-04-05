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

  // Exported menu link: navigation_design--communications-request:pr-requests.
  $menu_links['navigation_design--communications-request:pr-requests'] = array(
    'menu_name' => 'navigation',
    'link_path' => 'pr-requests',
    'router_path' => 'pr-requests',
    'link_title' => 'Design & Communications Request',
    'options' => array(
      'attributes' => array(
        'title' => 'A form used to submit Design and Communication Committee requests.',
      ),
      'alter' => TRUE,
      'identifier' => 'navigation_design--communications-request:pr-requests',
    ),
    'module' => 'system',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => 0,
    'customized' => 1,
    'parent_identifier' => 'navigation_requests:<front>',
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('Design & Communications Request');

  return $menu_links;
}
