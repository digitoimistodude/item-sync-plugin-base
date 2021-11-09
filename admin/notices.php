<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-11-09 16:55:56
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-11-09 17:37:50
 *
 * @package item-sync-plugin-base
 */

namespace Item_Sync_Plugin_Base;

defined( 'ABSPATH' ) || exit;

function maybe_show_item_update_notice() {
  $screen = get_current_screen();

  if ( ! $screen ) {
    return;
  }

  if ( get_cpt_slug() !== $screen->post_type ) {
    return;
  }

  if ( 'post' === $screen->base && isset( $_GET['post'] ) ) {
    $item_id = get_post_meta( intval( $_GET['post'] ), prefix_key( 'sync_id', true ), true );

    if ( ! empty( $item_id ) ) {
      add_action( 'admin_notices', __NAMESPACE__ . '\item_update_notice' );
    }
  }
} // end maybe_show_item_update_notice

function item_update_notice() {
  $class = 'notice notice-error';
  $message = '<b>HUOM!</b> Sisältö tuodaan automaattisesti ulkopuolisesta lähteestä. Tehdyt muutokset ylikirjoittuvat seuraavan päivityksen yhteydessä.';
  printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses_post( $message ) );
} // end item_update_notice
