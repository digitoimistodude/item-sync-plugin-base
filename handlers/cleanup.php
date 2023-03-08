<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-11-09 16:30:06
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-11-09 17:37:51
 *
 * @package item-sync-plugin-base
 */

namespace Item_Sync_Plugin_Base;

defined( 'ABSPATH' ) || exit;

function cleanup_items() {
  log( 'Starting item cleanup', 'debug' );

  $last_sync_end = get_option( prefix_key( 'sync_end' ) );
  if ( empty( $last_sync_end ) ) {
    log( 'Exiting cleanup, no last sync end time available', 'debug' );
    return;
  }

  $date_compare = new \DateTime( $last_sync_end, wp_timezone() );

  $items = [];
  $items_query = new \WP_Query( [
    'post_type'       => get_cpt_slug(),
    'posts_per_page'  => 500,
    'meta_query'      => [
      'relation'  => 'OR',
      [
        'key'     => prefix_key( 'sync_time', true ),
        'value'   => wp_date( 'Y-m-d H:i:s', $date_compare->format( 'U' ) - ( HOUR_IN_SECONDS * 2 ) ), // get items that have not been synced in last two hours
        'compare' => '<',
        'type'    => 'DATETIME',
      ],
      [
        'key'     => prefix_key( 'sync_time', true ),
        'compare' => 'NOT EXISTS',
      ],
    ],
  ] );

  if ( ! $items_query->have_posts() ) {
    log( 'No items to cleanup', 'debug' );
    return;
  }

  while ( $items_query->have_posts() ) {
    $items_query->the_post();

    $items[ get_the_id() ] = get_the_title();
  } wp_reset_query();

  log( 'Cleaning up ' . count( $items ) . ' items', 'debug' );

  foreach ( $items as $item_id => $name ) {
    $sync_id = get_post_meta( $item_id, prefix_key( 'sync_id', true ), true );
    if ( empty( $sync_id ) ) {
      log( "Skipping deletion of {$name} because sync id not exists, assuming manual addition" );
      continue;
    }
    
    log( "Deleting item: {$name} - last updated " . get_post_meta( $item_id, prefix_key( 'sync_time', true ), true ), 'debug' );

    // trash instead of removal, trash is cleaned up automatically anyways
    wp_trash_post( $item_id );
  }
} // end cleanup_items
