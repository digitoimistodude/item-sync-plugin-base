<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-11-09 16:22:00
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-11-09 17:37:50
 *
 * @package item-sync-plugin-base
 */

namespace Item_Sync_Plugin_Base;

defined( 'ABSPATH' ) || exit;

function save_item( $item ) {
  if ( ! isset( $item['id'] ) ) {
    return;
  }

  log( "Updating item API ID: {$item['id']}", 'debug' );

  // Try to get WP post ID matching this item
  $item_post_id = get_item_post_id_by_api_id( $item['id'] );
  if ( $item_post_id ) {
    log( "Item WP ID is {$item_post_id}", 'debug' );
  } else {
    log( 'Item WP ID not found', 'debug' );
  }

  $save = [
    'ID'            => $item_post_id,
    'post_type'     => get_cpt_slug(),
    'post_status'   => 'publish',
    'post_title'    => $item['name'],
    'meta_input'    => [
      prefix_key( 'sync_id', true )   => $item['id'],
      prefix_key( 'sync_time', true ) => wp_date( 'Y-m-d H:i:s' ),
    ],
  ];

  // Save post
  $insert = wp_insert_post( $save );
  if ( $insert ) {
    $item['wp_post_id'] = $insert; // Add WP ID to item details

    if ( $item_post_id ) {
      log( 'Item updated', 'debug' );
    } else {
      log( "New item saved with WP ID {$insert}", 'debug' );
    }
  } else {
    log( 'Item save failed', 'error' );
    return;
  }
} // end save_item
