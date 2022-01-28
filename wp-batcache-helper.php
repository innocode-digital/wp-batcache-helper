<?php
/**
 * Plugin Name: Batcache Helper
 * Description: Improves Batcache cache flushing.
 * Version: 1.0.1
 * Author: Innocode
 * Author URI: https://innocode.com
 * Tested up to: 5.9.0
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Repeat condition from Batcache Manager.
if (
    ! isset( $GLOBALS['batcache'] ) ||
    ! is_object( $GLOBALS['batcache'] ) ||
    ! isset( $GLOBALS['wp_object_cache'] ) ||
    ! method_exists( $GLOBALS['wp_object_cache'], 'incr' )
) {
    return;
}

if ( ! function_exists( 'innocode_batcache_flush_post' ) ) {
    function innocode_batcache_flush_post( int $post_id ) : array {
        return function_exists( 'batcache_clear_url' )
            ? array_map( 'batcache_clear_url', innocode_get_post_rel_urls( $post_id ) )
            : [];
    }
}

if ( ! function_exists( 'innocode_batcache_disable_default' ) ) {
    function innocode_batcache_disable_default() {
        remove_action( 'clean_post_cache', 'batcache_post' );
    }
}

add_action( 'plugins_loaded', 'innocode_batcache_disable_default' );

if ( ! function_exists( 'innocode_batcache_handle_post' ) ) {
    function innocode_batcache_handle_post( string $new_status, string $old_status, WP_Post $post ) {
        if ( ! in_array( 'publish', [ $new_status, $old_status ], true ) ) {
            return;
        }

        innocode_batcache_flush_post( $post->ID );
    }
}

add_action( 'transition_post_status', 'innocode_batcache_handle_post', 10, 3 );
add_action( 'delete_post', 'innocode_batcache_flush_post' );
add_action( 'wp_update_comment_count', 'innocode_batcache_flush_post' );
