<?php
/**
 * Plugin Name: My Toolset
 * Plugin URI:  http://github.com
 * Description: Prevent composer-managed WordPress objects being changed through the web interface.
 * Version:     0.1.0
 * Author:      Mike Pastore <mike@oobak.org>
 * Author URI:  http://perlkour.pl
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') or die('error: access denied');

class Composer_Lockout {
  public static $role_map = array(
    'administrator' => array(
      'update_core',
      'edit_plugins',
      'install_plugins',
      'update_plugins',
      'delete_plugins'
    )
  );

  public static function lock() {
    return $self::change_caps('remove');
  }

  public static function unlock() {
    return $self::change_caps('add');
  }

  public static function change_caps($direction) {
    if (!current_user_can('activate_plugins'))
      return;

    if ($direction != 'add' && $direction != 'remove')
      return;

    foreach (self::$role_map as $role_name => $caps) {
      $role = get_role($role_name);
      foreach ($caps as $cap)
        call_user_func(array($role, $direction . '_cap'), $cap);
    }
  }
}

register_activation_hook(__FILE__, array('Composer_Lockout', 'lock'));
register_deactivation_hook(__FILE__, array('Composer_Lockout', 'unlock'));
