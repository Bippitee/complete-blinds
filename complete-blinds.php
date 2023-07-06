<?php

/** 
 * Plugin Name: Complete Blinds
 * Description: Complete Blinds product types, etc
 * Author: Ben Carragher
 * Version: 1.0
 * Author URI: https://www.foxandbear.dev
 * Text Domain: complete-blinds
 * Includes 'dominant-color-images' plugin for dominant color images from WordPress Perfomance - not available as standalone plugin
 */

register_activation_hook( __FILE__, 'create_pricing_table' );
 
function create_pricing_table() {
    global $wpdb;

    // Set pricing table name
    $table_name = $wpdb->prefix . 'blind_pricing';

    // Define the SQL query to create the pricing table
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT(11) NOT NULL AUTO_INCREMENT,
            blind_type VARCHAR(255) NOT NULL,
            group_number INT(11) NOT NULL,
            blind_width DECIMAL(10,2) NOT NULL,
            blind_drop DECIMAL(10,2) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            PRIMARY KEY  (id),
            KEY blind_type (blind_type),
            KEY group_number (group_number),
            KEY blind_width (blind_width),
            KEY blind_drop (blind_drop)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    // Execute the SQL query
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}


if (! defined('ABSPATH')) {
    return;
}

function enqueue_complete_blinds_script() {
  wp_enqueue_script('complete-blinds-script', plugin_dir_url(__FILE__) . '/js/complete_blinds_script.js', array(), '1.0', true);
  $option = get_option( 'complete_blinds_google_maps_api_key' );

    $scriptData = array(
        'key' => $option,
    );

    wp_localize_script('complete-blinds-script', 'api', $scriptData);
//   wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . get_option( 'complete_blinds_google_maps_api_key' ) . '&libraries=places', array(), '1.0', true );
  wp_enqueue_script( 'address-book-admin', plugin_dir_url(__FILE__) . '/js/address-book-admin.js', array( 'jquery' ), '1.0', true );

  //pricing tables
  wp_enqueue_script( 'pricing_tables', plugin_dir_url( __FILE__ ) . 'js/pricing_tables.js', array( 'jquery' ), '1.0', true );
  wp_localize_script( 'pricing_tables', 'myAjax', array(
    'ajaxurl' => admin_url( 'admin-ajax.php' ),
    'blind_pricing_nonce' => wp_create_nonce( 'blind_pricing_nonce_action' ),
) );
}

add_action('admin_enqueue_scripts', 'enqueue_complete_blinds_script');

function enqueue_complete_blinds_style() {
  wp_enqueue_style('pricing-tables-style', plugin_dir_url(__FILE__) . '/css/pricing-tables-style.css');
}

add_action('admin_enqueue_scripts', 'enqueue_complete_blinds_style');

include "admin-panel.php";
include "address-book.php";
include "woo/producttab.php";
include "woo/filterElements.php";
include "woo/taxonomyFilter.php";
// include "media-folders.php";
include "pricing-tables.php";
include "graphql/typedefs.php";
include "graphql/resolvers.php";
include "dominant-color-images/dominant-color-images.php";

// GraphQL authentication et. al. 
define( 'GRAPHQL_JWT_AUTH_SECRET_KEY', 'purple-monkey-dishwasher' );



 //taxonomy for fabric types
function cb_register_taxonomy_fabric_type() {
	 $labels = array(
		 'name'              => _x( 'Fabric Types', 'taxonomy general name' ),
		 'singular_name'     => _x( 'Fabric Type', 'taxonomy singular name' ),
		 'search_items'      => __( 'Search Fabric Types' ),
		 'all_items'         => __( 'All Fabric Types' ),
		 'parent_item'       => __( 'Parent Fabric Type' ),
		 'parent_item_colon' => __( 'Parent Fabric Type:' ),
		 'edit_item'         => __( 'Edit Fabric Type' ),
		 'update_item'       => __( 'Update Fabric Type' ),
		 'add_new_item'      => __( 'Add New Fabric Type' ),
		 'new_item_name'     => __( 'New Fabric Type Name' ),
		 'menu_name'         => __( 'Fabric Type' ),
         'not_found'         => __( 'No fabric types found.' ),
	 );
	 $args   = array(
		 'hierarchical'      => true, // make it hierarchical (like categories)
		 'labels'            => $labels,
		 'show_ui'           => true,
		 'show_admin_column' => true,
		 'query_var'         => true,
         'show_in_graphql'   => true,
         'graphql_single_name' => 'fabric_type',
         'graphql_plural_name' => 'fabric_types',
		 'rewrite'           => [ 'slug' => 'fabric_type' ],
	 );
	 register_taxonomy( 'fabric_type', [ 'product' ], $args );

   //add inital values
    $terms = array(
      'Blockout' => 'bo',
      'Light Filtering' => 'lf',
      'Screen' => 'scn',
      'Sheer' => 'shr',
    );

    $terms_exist = get_terms( 'fabric_type', array( 'hide_empty' => false ) );
    if ( empty( $terms_exist ) && ! is_wp_error( $terms_exist ) ) {
      foreach ( $terms as $term => $slug ) {
        wp_insert_term( $term, 'fabric_type', array( 'slug' => $slug ) );
      }
    }

}
add_action( 'init', 'cb_register_taxonomy_fabric_type' );

//taxonomy for blind types (roller, venetian, etc)
function cb_register_taxonomy_blind_type() {
     $labels = array(
         'name'              => _x( 'Blind Types', 'taxonomy general name' ),
         'singular_name'     => _x( 'Blind Type', 'taxonomy singular name' ),
         'search_items'      => __( 'Search Blind Types' ),
         'all_items'         => __( 'All Blind Types' ),
         'parent_item'       => __( 'Parent Blind Type' ),
         'parent_item_colon' => __( 'Parent Blind Type:' ),
         'edit_item'         => __( 'Edit Blind Type' ),
         'update_item'       => __( 'Update Blind Type' ),
         'add_new_item'      => __( 'Add New Blind Type' ),
         'new_item_name'     => __( 'New Blind Type Name' ),
         'menu_name'         => __( 'Blind Type' ),
         'not_found'         => __( 'No blind types found.' ),
     );
     $args   = array(
         'hierarchical'      => true, // make it hierarchical (like categories)
         'labels'            => $labels,
         'show_ui'           => true,
         'show_admin_column' => false,
         'query_var'         => true,
         'show_in_graphql'   => true,
         'graphql_single_name' => 'blind_type',
         'graphql_plural_name' => 'blind_types',
                 
         'rewrite'           => [ 'slug' => 'blind_type' ],
     );
     register_taxonomy( 'blind_type', [ 'product' ], $args );


//add inital values 
$terms = array(
  'Honeycomb' => 'honeycomb',
  'Metro Hood' => 'metro-hood',
  'Panel Glide' => 'panel-glide',
  'Roller' => array(
    'Double' => 'double',
    'Double Linked' => 'double-linked',
    'Single' => 'single',
    'Single Linked' => 'single-linked',
    'Skin and Base Rail' => 'skin-and-base-rail',
    'Skin and Tube' => 'skin-and-tube',
    'Skin Only' => 'skin-only',
  ),
  'Roman' => 'roman',
  'Venetian' => array(
    'Aluminium' => 'aluminium',
    'Timber' => 'timber',
  ),
  'Vertical' => array(
    '89mm' => '89mm',
    '100mm' => '100mm',
    '127mm' => '127mm',
    'Blade 89mm' => 'blade-89mm',
    'Blade 100mm' => 'blade-100mm',
    'Blade 127mm' => 'blade-127mm',
    'Track Only' => 'track-only',
  ),
    'Visage' => 'visage',
    'Vision' => 'vision',
);

//set a variable to check if the terms have been added already
$terms_exist = get_terms( 'blind_type', array( 'hide_empty' => false ) );
//if they don't exist, add them
if ( empty( $terms_exist ) && ! is_wp_error( $terms_exist ) ) {
// Loop through the terms and insert them
      foreach ( $terms as $name => $slug_or_children ) {
        // If the term has children, insert the parent term first
        if ( is_array( $slug_or_children ) ) {
            $parent_slug = sanitize_title( $name );
            $parent = wp_insert_term( $name, 'blind_type', array( 'slug' => $parent_slug ) );
            if ( is_wp_error( $parent ) ) {
                echo 'Error inserting parent term: ' . $parent->get_error_message() . PHP_EOL;
                continue;
            }
            foreach ( $slug_or_children as $child_name => $child_slug ) {
                $child_slug = $parent_slug . '/' . $child_slug;
                wp_insert_term( $child_name, 'blind_type', array( 'parent' => $parent['term_id'], 'slug' => $child_slug ) );
            }
        } else {
            wp_insert_term( $name, 'blind_type', array( 'slug' => $slug_or_children ) );
        }
      }
}

}

add_action( 'init', 'cb_register_taxonomy_blind_type' );

//gets SiteLogo from customizer
add_action( 'graphql_register_types', function() {
	register_graphql_field( 'RootQuery', 'siteLogo', [
		'type' => 'MediaItem',
		'description' => __( 'The logo set in the customizer', 'complete-blinds' ),
		'resolve' => function() {

			$logo_id = get_theme_mod( 'custom_logo' );

			if ( ! isset( $logo_id ) || ! absint( $logo_id ) ) {
				return null;
			}

			$media_object = get_post( $logo_id );
			return new \WPGraphQL\Model\Post( $media_object );

		}
	]  );

} );


add_filter('rest_pre_serve_request', 'cors_headersxxxx', 0, 4 );
    function cors_headersxxxx() {
      header( 'Access-Control-Allow-Origin: *');
      header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
      header( 'Access-Control-Allow-Credentials: true' );
      header( 'Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With, X-USER-ID' );
    }


    add_filter('rest_api_init', 'wp_add_cors_support');
    function wp_add_cors_support() {
      $enable_cors = true;
      if ($enable_cors) {
        $headers = 'Access-Control-Allow-Headers, X-Requested-With, Content-Type, Accept, Origin, Authorization';
        header( sprintf( 'Access-Control-Allow-Headers: %s', $headers ) );
      }
    }