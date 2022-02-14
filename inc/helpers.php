<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-11-09 16:04:33
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-11-09 17:45:00
 *
 * @package item-sync-plugin-base
 */

namespace Item_Sync_Plugin_Base;

defined( 'ABSPATH' ) || exit;

function prefix_key( $key, $hidden = false ) {
  $prefix = get_prefix();
  return $hidden ? "_{$prefix}_{$key}" : "{$prefix}_{$key}";
} // end prefix_key

function get_api_url() {
  $tenant = get_api_tenant();
  return "https://{$tenant}.domain.fi";
} // end get_api_url

function get_api_tenant() {
  return getenv( 'PREFIX_API_TENANT' );
} // end get_api_tenant

function get_api_token() {
  return getenv( 'PREFIX_API_TOKEN' );
} // end get_api_token

function get_item_post_id_by_api_id( $item_id ) {
  global $wpdb;

  $return = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s", prefix_key( 'sync_id', true ), $item_id ) );

  return empty( $return ) ? false : $return[0];
} // end get_item_post_id_by_api_id
