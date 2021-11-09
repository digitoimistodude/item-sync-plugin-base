<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-11-09 16:05:10
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-11-09 17:37:45
 *
 * @package item-sync-plugin-base
 */

namespace Item_Sync_Plugin_Base;

defined( 'ABSPATH' ) || exit;

function log( $message, $level = 'info', $wp_error = null ) {
  // WP CLI
  if ( defined( 'WP_CLI' ) && WP_CLI ) {
    $wp_cli_level = $level;
    if ( 'debug' !== $level || 'warning' !== $level || 'error' !== $level ) {
      $wp_cli_level = 'log';
    }

    \WP_CLI::$wp_cli_level( $message );

    if ( $wp_error && 'debug' === $level ) {
      \WP_CLI::$wp_cli_level( wp_json_encode( $wp_error ) );
    }
  }

  // Log file
  write_to_log( $message, $wp_error );

  // Query monitor
  if ( $wp_error ) {
    do_action( 'qm/error', $message );
    do_action( 'qm/error', $wp_error );
  } else {
    do_action( "qm/{$level}", $message );
  }

  // Simple history
  if ( 'info' === $level || 'notice' === $level ) {
    apply_filters( 'simple_history_log', $message, null, $level );
  }
} // end log

function write_to_log( $message, $wp_error = null ) {
  if ( ! constant( 'WP_DEBUG_LOG' ) ) {
    return;
  }

  error_log( $message );

  if ( ! empty( $wp_error ) ) {
    error_log( print_r( $wp_error, true ) );
  }
} // end write_to_log
