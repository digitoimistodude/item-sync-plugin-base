<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-11-09 16:08:32
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-11-09 17:37:49
 *
 * @package item-sync-plugin-base
 */

namespace Item_Sync_Plugin_Base;

defined( 'ABSPATH' ) || exit;

function call_api( $params = [], $args = [] ) {
  $api_url = get_api_url();

  if ( ! empty( $params ) ) {
    $api_url = add_query_arg( $params, $api_url );
  }

  $args = wp_parse_args( $args, [] );

  log( "API called {$api_url}", 'debug' );

  $response = wp_remote_request( $api_url, $args );

  $response_code = wp_remote_retrieve_response_code( $response );
  if ( 200 !== $response_code ) {
    log( "API returned error code {$response_code}", 'error' );
    return false;
  }

  $body = wp_remote_retrieve_body( $response );

  if ( empty( $body ) ) {
    log( 'API returned empty body', 'debug' );
    return false;
  }

  $xml = simplexml_load_string( $body );
  if ( ! $xml ) {
    log( 'API returned invalid xml', 'error' );
    return false;
  }

  return json_decode( json_encode( $xml ), true );
} // end call_api
