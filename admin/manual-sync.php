<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2023-01-25 13:21:46
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2023-01-25 14:16:46
 *
 * @package item-sync-plugin-base
 */

namespace Item_Sync_Plugin_Base;

defined( 'ABSPATH' ) || exit;

// Allow manual forced sync on development
function dev_debug_run() {
  if ( 'development' !== wp_get_environment_type() ) {
    return;
  }

  if ( ! is_user_logged_in() ) {
    return;
  }

  if ( ! isset( $_GET['run'] ) ) {
    return;
  }

  if ( get_prefix() !== sanitize_title( $_GET['run'] ) ) {
    return;
  }

  $force = isset( $_GET['force'] );

  sync( $force );
} // end dev_debug_run

function add_manual_sync_menu_item() {
  if ( ! apply_filters( prefix_key( 'manual_sync_allow' ), false ) ) {
    return;
  }

  $cpt = get_cpt_slug();

  add_submenu_page(
    "edit.php?post_type={$cpt}",
    'Päivitä',
    'Päivitä',
    'publish_pages',
    prefix_key( 'manual-sync' ),
    __NAMESPACE__ . '\manual_sync_menu_item_callback'
  );
} // end add_manual_sync_menu_item

function manual_sync_link_callback() {
  if ( ! apply_filters( prefix_key( 'manual_sync_allow' ), false ) ) {
    return;
  }

  if ( ! current_user_can( 'publish_pages' ) ) {
    wp_die( 'Siulla ei ole oikeutta käynnistää manuaalista päivitystä.' );
  }

  // Get data for page
  $next_run_scheduled = wp_get_scheduled_event( prefix_key( 'cron' ) );
  $prev_run_started = get_option( prefix_key( 'sync_start' ) );
  $prev_run_ended = get_option( prefix_key( 'sync_end' ) );

  // If set to run, check nonce
  if ( isset( $_GET[ prefix_key( 'sync-nonce' ) ] ) ) {
    $notice_class   = 'notice notice-error';
    $notice_message = 'Sinulla ei ole oikeutta tehdä manuaalista päivitystä.';

    if ( wp_verify_nonce( $_GET[ prefix_key( 'sync-nonce' ) ] ) ) {
      // Run the sync action
      do_action( prefix_key( 'cron' ) );

      $notice_class   = 'notice notice-success is-dismissible';
      $notice_message = 'Manuaalinen päivitys käynnistetty.';
    }
  }

  // Output page
  echo '<div class="wrap">';
    echo '<h2>Tuonti ulkopuolisesta lähteestä</h2>';

    // Maybe notice?
    if ( isset( $notice_class ) && isset( $notice_message ) ) {
      printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $notice_class ), esc_html( $notice_message ) );
    }

    // Show next cron run
    if ( is_object( $next_run_scheduled ) ) {
      printf( '<p><b>Seuraava päivitys: </b>%1$</p>', wp_date( 'Y-m-d H:i:s', $next_run_scheduled->timestamp ) );
    }

    printf( '<p><b>Edellinen päivitys käynnistetty: </b>%1$s</p>', $prev_run_started );
    printf( '<p><b>Edellinen päivitys valmistunut: </b>%1$s</p>', $prev_run_ended );

    // Button for starting manual sync
    printf(
      '<p><a href="%1$s" class="button button-primary">Käynnistä</a></p>',
      wp_nonce_url( get_current_admin_url(), '-1', prefix_key( 'sync-nonce' ) )
    );
  echo '</div>';
} // end manual_sync_link_callback
