<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-11-09 16:22:00
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2023-02-03 11:00:05
 *
 * @package item-sync-plugin-base
 */

namespace Item_Sync_Plugin_Base;

defined( 'ABSPATH' ) || exit;

function save_item( $item, $force ) {
  $data_hash_key = prefix_key( 'data_hash', true );

  if ( ! isset( $item['id'] ) ) {
    return;
  }

  log( "Updating item API ID: {$item['id']}", 'debug' );
  log( $item, 'debug' );

  // Try to get WP post ID matching this item
  $item_post_id = get_item_post_id_by_api_id( $item['id'] );
  if ( $item_post_id ) {
    log( "Item WP ID is {$item_post_id}", 'debug' );
  } else {
    log( 'Item WP ID not found', 'debug' );
  }

  $data_hash = md5( json_encode( $item ) );

  /**
   * If item exists already in databse, check the new data
   * hash againts stored one to check if anything has changed.
   * In case hashes are same, we can safely assume that data
   * has not changed and skip the save process of this item.
   */
  if ( $item_post_id && ! $force ) {
    $data_hash_old = get_post_meta( $item_post_id, $data_hash_key, true );

    if ( $data_hash === $data_hash_old ) {
      update_post_meta( $item_post_id, prefix_key( 'sync_time', true ), wp_date( 'Y-m-d H:i:s' ) );
      log( 'Item skipped. New and old data hash matches, assuming no data changes', 'debug' );
      return;
    }
  }

  $save = [
    'ID'            => $item_post_id,
    'post_type'     => get_cpt_slug(),
    'post_status'   => 'publish',
    'post_title'    => $item['name'],
    'meta_input'    => [
      prefix_key( 'sync_id', true )         => $item['id'],
      prefix_key( 'sync_time', true )       => wp_date( 'Y-m-d H:i:s' ),
      prefix_key( 'updated_time', true )    => wp_date( 'Y-m-d H:i:s' ),
      prefix_key( 'data_hash_base', true )  => $item,
      $data_hash_key                        => $data_hash,
    ],
  ];

  /**
   * Consider disabling Simple History logging during the item save if
   * the sync runs often and there are hundereds of items being synced.
   * Simple History logging might end up increasing your database size.
   */
  // add_filter( 'simple_history/log/do_log', '__return_false' );

  // Save post
  $insert = wp_insert_post( $save );

  // add_filter( 'simple_history/log/do_log', '__return_true' );

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

  // save_item_terms( $item );
} // end save_item
