<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-11-09 16:06:10
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-11-09 17:37:48
 *
 * @package item-sync-plugin-base
 */

namespace Item_Sync_Plugin_Base;

defined( 'ABSPATH' ) || exit;

function schedule_cron_events() {
  if ( ! \wp_next_scheduled( prefix_key( 'cron' ) ) ) {
    wp_schedule_event( time(), 'hourly', prefix_key( 'cron' ) );
  }
} // end schedule_cron_events

function deschedule_cron_events() {
  wp_clear_scheduled_hook( prefix_key( 'cron' ) );
} // end deschedule_cron_events

function sync() {
  update_option( prefix_key( 'sync_start' ), wp_date( 'Y-m-d H:i:s' ) );

  $response = call_api();
  if ( ! $response ) {
    return;
  }

  foreach ( $response['items'] as $item ) {
    save_item( $item );
  }

  update_option( prefix_key( 'sync_end' ), wp_date( 'Y-m-d H:i:s' ) );

  wp_schedule_single_event( time() + ( MINUTE_IN_SECONDS * 5 ), prefix_key( 'cleanup' ) );
} // end sync

function cleanup() {
  update_option( prefix_key( 'cleanup_start' ), wp_date( 'Y-m-d H:i:s' ) );

  cleanup_items();

  update_option( prefix_key( 'cleanup_end' ), wp_date( 'Y-m-d H:i:s' ) );
} // end cleanup
