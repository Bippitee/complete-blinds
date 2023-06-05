<?php

function delete_cb_window_configurations() {
    $args = array(
        'post_type' => 'cb_window',
        'posts_per_page' => -1, // Retrieve all cb_window posts
    );

    $configurations = get_posts($args);

    foreach ($configurations as $configuration) {
        wp_delete_post($configuration->ID, true); // Delete the post
    }

    // Delete the 'cb_window_configurations_added' option
    delete_option('cb_window_configurations_added');
    unregister_taxonomy('cb_window_size');
    unregister_taxonomy('cb_window_style');
}

// add_action('init', 'delete_cb_window_configurations');

function create_custom_cb_window_door_post_type() {
    $labels = array(
        'name'               => 'Windows',
        'singular_name'      => 'Window',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Window',
        'edit_item'          => 'Edit Window',
        'new_item'           => 'New Window',
        'view_item'          => 'View Window',
        'search_items'       => 'Search Windows',
        'not_found'          => 'No windows found',
        'not_found_in_trash' => 'No windows found in Trash',
        'parent_item_colon'  => '',
        'menu_name'          => 'Windows'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'cb_windows' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'show_in_graphql'   => true,
        'graphql_single_name' => 'window',
        'graphql_plural_name' => 'windows',
        'supports'           => array( 'title' )
    );

    register_post_type( 'cb_window', $args );

    //doors
   $labels = array(
        'name'               => 'Doors',
        'singular_name'      => 'Door',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Door',
        'edit_item'          => 'Edit Door',
        'new_item'           => 'New Door',
        'view_item'          => 'View Door',
        'search_items'       => 'Search Door',
        'not_found'          => 'No doors found',
        'not_found_in_trash' => 'No doors found in Trash',
        'parent_item_colon'  => '',
        'menu_name'          => 'Doors'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'cb_doors' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'show_in_graphql'   => true,
        'graphql_single_name' => 'door',
        'graphql_plural_name' => 'doors',
        'supports'           => array( 'title' )
    );

    register_post_type( 'cb_door', $args );

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

    register_taxonomy( 'widths', array( 'cb_window', 'cb_door' ), $widths_args );

    // Register Heights Taxonomy
    $heights_labels = array(
        'name'              => 'Heights',
        'singular_name'     => 'Height',
        'search_items'      => 'Search Heights',
        'all_items'         => 'All Heights',
        'parent_item'       => 'Parent Height',
        'parent_item_colon' => 'Parent Height:',
        'edit_item'         => 'Edit Height',
        'update_item'       => 'Update Height',
        'add_new_item'      => 'Add New Height',
        'new_item_name'     => 'New Height Name',
        'menu_name'         => 'Heights',
    );

    $heights_args = array(
        'labels'            => $heights_labels,
        'hierarchical'      => false,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => false,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'heights' ),
    );

    register_taxonomy( 'heights', array( 'cb_window', 'cb_door' ), $heights_args );
    //-------------------------------------------------------------------
     // Pre-fill Width Terms for Windows
    $window_widths = array( '610', '730', '850', '970', '1090', '1210', '1450', '1810', '1930', '2530', '1570', '2170', '2410', '2650' );
    $window_heights = array( '600', '772', '944', '1030', '1200', '1370', '1540', '1800', '2058', '2100' );

    foreach ( $window_widths as $width ) {
        wp_insert_term( $width, 'widths', array(
            'slug' => 'window-' . sanitize_title( $width ),
        ) );
    }

    foreach ( $window_heights as $height ) {
        wp_insert_term( $height, 'heights', array(
            'slug' => 'window-' . sanitize_title( $height ),
        ) );
    }

    // Pre-fill Width Terms for Doors
    $door_widths = array( '900', '1210', '1310', '1340', '1450', '1514', '1520', '1570', '1710', '1736', '1780', '1810', '2170', '2238', '2324', '2410', '2684', '2962', '3118', '3224', '3584', '3598', '3686', '4410', '4624', '5134', '5344', '6424' );
    $door_heights = array( '2058', '2100','2143', '2400' );

    foreach ( $door_widths as $width ) {
        wp_insert_term( $width, 'widths', array(
            'slug' => 'door-' . sanitize_title( $width ),
        ) );
    }

    foreach ( $door_heights as $height ) {
        wp_insert_term( $height, 'heights', array(
            'slug' => 'door-' . sanitize_title( $height ),
        ) );
    }
}


add_action( 'init', 'create_custom_cb_window_door_post_type' );

// Add Window Size META
function render_dimensions_meta_box($post, $args) {
    // Get saved values
    $selected_dimensions = get_post_meta($post->ID, 'selected_dimensions', true);

    // Get all available widths and heights
    $terms = get_terms(array(
        'taxonomy' => 'widths',
        'hide_empty' => false,
    ));

    //get post type, filter widths and heights by post type singular name lowercase
    $post_type = get_post_type_object(get_post_type())->labels->singular_name;
    $filterTerm = strtolower($post_type) . '-';
   

    // Filter terms starting with 'window-'
    $available_widths = array_filter($terms, function ($term) use ($filterTerm) {
        return strpos($term->slug, $filterTerm) === 0;
    });

    $terms = get_terms(array(
        'taxonomy' => 'heights',
        'hide_empty' => false,
    ));

    // Filter terms starting with 'window-'
    $available_heights = array_filter($terms, function ($term) use ($filterTerm) {
        return strpos($term->slug, $filterTerm) === 0;
    });

    // Sort the widths and heights
    usort($available_widths, function ($a, $b) {
        return $a->name - $b->name;
    });

    usort($available_heights, function ($a, $b) {
        return $a->name - $b->name;
    });

    // Output the checkboxes grid
echo '<table class="widefat striped">';

// Output the table headers (heights)
echo '<thead><tr>';
// echo '<th></th>'; // Empty cell for the top-left corner
echo '<th style="text-align:center; font-weight: 500" colspan="' . (count($available_heights)) + 2 .  '">Frame Height (mm)</th>'; // Label cell spanning the width of heights
echo '</tr>';

// Output the table headers (heights)
echo '<tr>';
echo '<th></th><th></th>'; // Empty cell for the top-left corner

foreach ($available_heights as $height) {
    echo '<th class="height-label" data-heightName="' . $height->name . '">' . $height->name . '</th>';
}

echo '</tr></thead>';
echo '<tbody><tr>';
echo '<th width="16px" style="font-weight: 500;" rowspan="' . (count($available_widths)) . '"><span style="writing-mode: vertical-rl; transform: rotate(180deg); text-align: left; max-height: 150px;">Frame Width (mm)</span></th>'; // Label cell for the left column

// Output the table rows (widths)
foreach ($available_widths as $width) {
    echo '<th class="width-label">' . $width->name . '</th>';

    foreach ($available_heights as $height) {
        $checked = '';

        // Check if the width and height combination is selected
            if (is_array($selected_dimensions) && in_array($width->term_id . '-' . $height->term_id, $selected_dimensions)) {
                $checked = 'checked';
            }

            echo '<td><input data-heightNameInput="' . $height->name . '" type="checkbox" name="dimensions[]" value="' . $width->term_id . '-' . $height->term_id . '" ' . $checked . '></td>';
        }

    echo '</tr>';
}

echo '</tbody></table>';

echo '<script>';
echo 'jQuery(function($) {';
echo '    $(".height-label").on("click", function() {';
echo '        var heightName = $(this).data("heightname");';
//check if data attribute 'allchecked' is set or exists, if not set it to true, if it is set, toggle it
echo '        if ($(this).data("allchecked") === undefined) {';
echo '            $(this).data("allchecked", true);';
echo '        } else {';
echo '            $(this).data("allchecked", !$(this).data("allchecked"));';
echo '        }';
//if allchecked is true, check all checkboxes in the same column as the clicked header
echo '        if ($(this).data("allchecked")) {';

echo '        var inputs = $("input[data-heightNameInput=\'" + heightName + "\']");';
echo '        inputs.prop("checked", true);';
echo '        } else {';
//if allchecked is false, uncheck all checkboxes in the same column as the clicked header
echo '        var inputs = $("input[data-heightNameInput=\'" + heightName + "\']");';
echo '        inputs.prop("checked", false);';
echo '        }';
echo '    });';
echo '    $(".width-label").on("click", function() {';
echo '        var rowIndex = $(this).parent().index() + 1;';
// Check if data attribute 'allchecked' is set or exists, if not set it to true, if it is set, toggle it
echo '        if ($(this).data("allchecked") === undefined) {';
echo '            $(this).data("allchecked", true);';
echo '        } else {';
echo '            $(this).data("allchecked", !$(this).data("allchecked"));';
echo '        }';
// If allchecked is true, check all checkboxes in the same column as the clicked header
echo '        if ($(this).data("allchecked")) {';
echo '            $("table tr:nth-child(" + rowIndex + ") input[type=checkbox]").prop("checked", true);';
echo '        } else {';
// If allchecked is false, uncheck all checkboxes in the same column as the clicked header
echo '            $("table tr:nth-child(" + rowIndex + ") input[type=checkbox]").prop("checked", false);';
echo '        }';
echo '    });';
echo '});';
echo '</script>';
}

function add_custom_meta_box() {
    add_meta_box(
        'dimensions',
        'Dimensions',
        'render_dimensions_meta_box',
        array('cb_window', 'cb_door'),
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_custom_meta_box');

function save_dimensions_meta($post_id) {
    $post_type = get_post_type($post_id);

    if (in_array($post_type, array('cb_window', 'cb_door')) && isset($_POST['dimensions'])) {
        $dimensions = $_POST['dimensions'];

        $selected_dimensions = array(); // Combined width and height values

        foreach ($dimensions as $dimension) {
            $ids = explode('-', $dimension);

            if (isset($ids[0]) && isset($ids[1])) {
                $width_id = $ids[0];
                $height_id = $ids[1];
                $selected_dimensions[] = $width_id . '-' . $height_id;
            }
        }

        // Save the selected dimensions as post meta
        update_post_meta($post_id, 'selected_dimensions', $selected_dimensions);
    }
}

add_action('save_post', 'save_dimensions_meta');


add_action('save_post', 'save_dimensions_meta');
