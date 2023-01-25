<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2023-01-25 14:23:51
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2023-01-25 14:30:00
 *
 * @package item-sync-plugin-base
 */

namespace Item_Sync_Plugin_Base;

defined( 'ABSPATH' ) || exit;

function save_item_terms( $item ) {
  /**
   * This is example and you will most definitely need to
   * update what and from where you want to save terms.
   *
   * Remove the return once you've changed the example.
   */
  return;

  $taxonomy = 'tax';

  $term_id = maybe_save_tax_term( $item['category']['name'], $taxonomy, [
    prefix_key( 'id' ) => $item['category']['id'],
  ] );

  if ( $term_id ) {
    wp_set_post_terms( $item['wp_post_id'], $term_id, $taxonomy );
  }
} // end save_item_terms

function maybe_save_tax_term( $term = null, $taxonomy = null, $metadata = [] ) {
  if ( empty( $term ) || empty( $taxonomy ) ) {
    return false; // Bail early if there's no term.
  }

  // Check if term exists in wp. If does, return term id.
  $term_exists = term_exists( $term, $taxonomy );
  if ( ! empty( $term_exists ) ) {
    return $term_exists['term_id'];
  }

  // Term didn't exist, try to insert it into wp.
  $insert_term = wp_insert_term( $term, $taxonomy );
  if ( is_wp_error( $insert_term ) ) {
    // Term insert failed, log it and bail.
    log( "Failed saving term {$term} to {$taxonomy}", 'debug', $insert_term );
    return false;
  }

  if ( ! empty( $metadata ) ) {
    foreach ( $metadata as $key => $value ) {
      update_term_meta( $insert_term['term_id'], $key, $value );
    }
  }

  return $insert_term['term_id'];
} // end maybe_save_tax_term
