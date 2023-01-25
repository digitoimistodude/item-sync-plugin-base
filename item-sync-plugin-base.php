<?php
/**
 * Plugin Name: Item Sync Plugin Base
 * Description: Describe what this sync plugin does.
 * Plugin URI: https://dude.fi
 * Author: Digitoimisto Dude Oy
 * Author URI: https://dude.fi
 * Version: 0.3.0
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 *
 * @Author: Timi Wahalahti
 * @Date:   2021-11-09 16:01:03
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2023-01-25 14:11:42
 *
 * @package item-sync-plugin-base
 */

namespace Item_Sync_Plugin_Base;

defined( 'ABSPATH' ) || exit;

function get_plugin_version() {
  return 030;
} // end get_plugin_version

function get_prefix() {
  return 'item_sync_plugin_base';
} // end get_prefix

function get_cpt_slug() {
  return 'cpt';
} // end get_cpt_slug

/**
 * Pure function files.
 */
include plugin_dir_path( __FILE__ ) . '/inc/logging.php';
include plugin_dir_path( __FILE__ ) . '/inc/helpers.php';
include plugin_dir_path( __FILE__ ) . '/inc/request.php';
include plugin_dir_path( __FILE__ ) . '/inc/wp-cli.php';

/**
 * Handlers for singular item sync and clenup.
 */
include plugin_dir_path( __FILE__ ) . '/handlers/item.php';
include plugin_dir_path( __FILE__ ) . '/handlers/cleanup.php';

/**
 * Cron and other automated jobs related functionalities.
 */
include plugin_dir_path( __FILE__ ) . '/inc/cron.php';
register_activation_hook( __FILE__,   __NAMESPACE__ . '\schedule_cron_events' ); // Add cron event for sync on activation
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\deschedule_cron_events' );
add_action( 'init',                   __NAMESPACE__ . '\dev_debug_run' ); // Allow running the sync from browser on development
add_action( 'admin_init',             __NAMESPACE__ . '\schedule_cron_events' ); // Ensure cron event is in place
add_action( prefix_key( 'cron' ),     __NAMESPACE__ . '\sync' ); // Cron sync event
add_action( prefix_key( 'cleanup' ),  __NAMESPACE__ . '\cleanup' ); // Cron cleanup event

/**
 * Admin side functionalities.
 */
include plugin_dir_path( __FILE__ ) . '/admin/notices.php';
add_action( 'current_screen', __NAMESPACE__ . '\maybe_show_update_notice' );

include plugin_dir_path( __FILE__ ) . '/admin/manual-sync.php';
add_action( 'admin_menu', __NAMESPACE__ . '\add_manual_sync_menu_item' );
