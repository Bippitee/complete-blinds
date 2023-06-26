<?php
function create_pricing_table_post_type() {
    $labels = array(
        'name'               => 'Pricing Tables',
        'singular_name'      => 'Pricing Table',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Pricing Table',
        'edit_item'          => 'Edit Pricing Table',
        'new_item'           => 'New Pricing Table',
        'view_item'          => 'View Pricing Table',
        'search_items'       => 'Search Pricing Tables',
        'not_found'          => 'No Pricing Tables found',
        'not_found_in_trash' => 'No Pricing Tables found in Trash',
        'parent_item_colon'  => '',
        'menu_name'          => 'Pricing Tables'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'exclude_from_search' => true,
        'publicly_queryable'  => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => false,
        'rewrite'             => array( 'slug' => 'cb_pricing-table' ),
        'capability_type'     => 'post',
        'has_archive'         => false,
        'hierarchical'        => false,
        'menu_position'       => null,
        'show_in_graphql'     => true,
        'graphql_single_name' => 'pricingTable',
        'graphql_plural_name' => 'pricingTables',
        'supports'            => array( 'title' )
    );

    register_post_type( 'cb_pricing-table', $args );


    // ------------------------------------------------------------------
     // Register Widths Taxonomy
    $widths_labels = array(
        'name'              => 'Widths',
        'singular_name'     => 'Width',
        'search_items'      => 'Search Widths',
        'all_items'         => 'All Widths',
        'parent_item'       => 'Parent Width',
        'parent_item_colon' => 'Parent Width:',
        'edit_item'         => 'Edit Width',
        'update_item'       => 'Update Width',
        'add_new_item'      => 'Add New Width',
        'new_item_name'     => 'New Width Name',
        'menu_name'         => 'Widths',
    );

    $widths_args = array(
        'labels'            => $widths_labels,
        'hierarchical'      => false,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => false,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'widths' ),
    );

    register_taxonomy( 'widths', 'cb_pricing-table', $widths_args );

    // Register Drops Taxonomy
    $drops_labels = array(
        'name'              => 'Drops',
        'singular_name'     => 'Drop',
        'search_items'      => 'Search Drops',
        'all_items'         => 'All Drops',
        'parent_item'       => 'Parent Drop',
        'parent_item_colon' => 'Parent Drop:',
        'edit_item'         => 'Edit Drop',
        'update_item'       => 'Update Drop',
        'add_new_item'      => 'Add New Drop',
        'new_item_name'     => 'New Drop Name',
        'menu_name'         => 'Drops',
    );

    $drops_args = array(
        'labels'            => $drop_labels,
        'hierarchical'      => false,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => false,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'heights' ),
    );

    register_taxonomy( 'drops', 'cb_pricing-table', $drops_args );
    //-------------------------------------------------------------------
     // Pre-fill Width Terms for Pricing tables
    $blind_widths = generate_number_array( 610, 150, 18 ); //columns 1-18   
    $blind_drops = generate_number_array( 600, 100, 25 ); //rows 1-25

    foreach ( $blind_widths as $width ) {
        wp_insert_term( $width, 'widths', array(
            'slug' => 'width-' . sanitize_title( $width ),
        ) );
    }

    foreach ( $blind_drops as $height ) {
        wp_insert_term( $height, 'heights', array(
            'slug' => 'height-' . sanitize_title( $height ),
        ) );
    }

}

/*---------- HELPER FUNCTIONS ----------*/

// Generate an array of numbers
function generate_number_array($start, $increment, $count) {
    $numbers = array();
    
    for ($i = 0; $i < $count; $i++) {
        $numbers[] = $start + ($i * $increment);
    }
    
    return $numbers;
}