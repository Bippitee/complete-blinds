<?php

/** 
 * Plugin Name: Complete Blinds
 * Description: Complete Blinds product types, etc
 * Author: Ben Carragher
 * Version: 1.0
 * Author URI: https://www.foxandbear.dev
 * Text Domain: complete-blinds
 */


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
}

add_action('admin_enqueue_scripts', 'enqueue_complete_blinds_script');

include "admin-panel.php";
include "address-book.php";
include "woo/producttab.php";
include "woo/filterElements.php";
include "woo/taxonomyFilter.php";
include "media-folders.php";
include "windows.php";

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
}

//add inital values 
$terms = array(
  'Honeycomb' => 'honeycomb',
  'Metro Hood' => 'metro-hood',
  'Panel Glide' => 'panel-glide',
  'Roller' => array(
    'Double' => 'roller-double',
    'Double Linked' => 'roller-double-linked',
    'Single' => 'roller-single',
    'Single Linked' => 'roller-single-linked',
    'Skin and Base Rail' => 'roller-skin-and-base-rail',
    'Skin and Tube' => 'roller-skin-and-tube',
    'Skin Only' => 'roller-skin-only',
  ),
  'Roman' => 'roman',
  'Venetian' => array(
    'Aluminium' => 'venetian-aluminium',
    'Timber' => 'venetian-timber',
  ),
  'Vertical' => array(
    '89mm' => 'vertical-89mm',
    '100mm' => 'vertical-100mm',
    '127mm' => 'vertical-127mm',
    'Blade 89mm' => 'vertical-blade-89mm',
    'Blade 100mm' => 'vertical-blade-100mm',
    'Blade 127mm' => 'vertical-blade-127mm',
    'Track Only' => 'vertical-track-only',
  ),
    'Visage' => 'visage',
    'Vision' => 'vision',
);

// Loop through the terms and insert them
foreach ( $terms as $name => $slug_or_children ) {
  // If the term has children, insert the parent term first
  if ( is_array( $slug_or_children ) ) {
      $parent = wp_insert_term( $name, 'blind_type', array( 'slug' => sanitize_title( $name ) ) );
      foreach ( $slug_or_children as $child_name => $child_slug ) {
          wp_insert_term( $child_name, 'blind_type', array( 'parent' => $parent['term_id'], 'slug' => $child_slug ) );
      }
  } else {
      wp_insert_term( $name, 'blind_type', array( 'slug' => $slug_or_children ) );
  }
}

add_action( 'init', 'cb_register_taxonomy_blind_type' );



//resolves supplier name from product meta
add_action( 'graphql_register_types', function() {
  register_graphql_field( 'Product', 'supplier', [
     'type' => 'String',
     'description' => __( 'Supplier of the fabric', 'complete-blinds' ),
     'resolve' => function( $post ) {

      $args = array(
        'post_type' => 'address_book',
        'tax_query' => array(
            array(
                'taxonomy' => 'address_book_contact_type',
                'field' => 'slug',
                'terms' => 'supplier'
            )
        ),
        'orderby' => 'title',
        'order' => 'ASC',
    );

       $suppliers = get_posts( $args );

        $supplierID = get_post_meta( $post->ID, 'cblinds_product_info', true );
        $supplier = get_the_title( $supplierID );
        // $supplierWebsite = get_post_meta( $supplierID, '_address_book_website', true );
       
       return ! empty( $supplier ) ? $supplier : 'No Value';
     }
  ] );
} );


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