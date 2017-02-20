<?php
/**
 * @file
 * pr_requests.features.user_role.inc
 */

/**
 * Implements hook_user_default_roles().
 */
function pr_requests_user_default_roles() {
  $roles = array();

  // Exported role: UNBF Staff.
  $roles['UNBF Staff'] = array(
    'name' => 'UNBF Staff',
    'weight' => 4,
  );

  return $roles;
}
