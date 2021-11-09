<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-11-09 16:08:59
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-11-09 17:43:56
 *
 * @package item-sync-plugin-base
 */

namespace Item_Sync_Plugin_Base;

if ( defined( 'WP_CLI' ) && WP_CLI ) {
  \WP_CLI::add_command( str_replace( '_', '-', get_prefix() ), __NAMESPACE__ . '\wp_cli_sync' );
}

function wp_cli_sync( $args, $assoc_args ) {
  if ( ! isset( $assoc_args['yes'] ) ) {
    \WP_CLI::confirm( 'Are you sure you want to proceed? Sync might take a while.', $assoc_args );
  }

  \WP_CLI::log( 'Sync started.' );

  sync();

  \WP_CLI::success( 'Sync finished.' );
} // end wp_cli_sync
