<?php
// Insert pricing data into the custom table
function insert_pricing_data( $blind_type, $group_number, $width, $drop, $price ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'blind_pricing';

    $data = array(
        'blind_type'   => $blind_type,
        'group_number' => $group_number,
        'blind_width'  => $width,
        'blind_drop'   => $drop,
        'price'        => $price,
    );

    $wpdb->insert( $table_name, $data );
    return $wpdb->insert_id;
}

function batch_insert_pricing_data( $data ) {
     $data = stripslashes( $data ); // Remove slashes from the JSON string
    // Decode the JSON string
    $entries = json_decode( $data, true );
    $error_code = json_last_error();

    if ( $error_code !== JSON_ERROR_NONE ) {
        // Handle the error
        error_log( 'Error: JSON decoding failed with error code ' . $error_code );
        return;
    }
     // Check if the $entries variable is null
     if ( $entries === null ) {
        // Handle the error
        error_log( 'Error: Invalid JSON data.' );
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'blind_pricing';

    $q = "INSERT INTO $table_name (blind_type, group_number, blind_width, blind_drop, price) VALUES ";

    
    foreach ($entries as $entry) {
        $q .= $wpdb->prepare( "(%s, %d, %f, %f, %f),", $entry['blind_type'], $entry['group_number'], $entry['blind_width'], $entry['blind_drop'], $entry['price'] );
    }

    $q = rtrim( $q, ',' ); // Remove the trailing comma
    
    $wpdb->query( $q );


    return $wpdb->insert_id;
}


// Retrieve pricing data from the custom table
function get_pricing_data( $blind_type = '', $group_number = '0' ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'blind_pricing';
    
    $where = array();

    if ( ! empty( $blind_type ) ) {
        $where[] = $wpdb->prepare( 'blind_type = %s', $blind_type );
    }  else {
        //don't return anything if no blind type is specified
        return;
    }

    if ( ! empty( $group_number ) ) {
        $group_number = intval( $group_number ); // Convert $group_number to integer
        $where[] = $wpdb->prepare( 'group_number = %d', $group_number );
    } else {
        $where[] = $wpdb->prepare( 'group_number = %d', 0 );
    }

    $where_clause = ! empty( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '';
    $query = "SELECT * FROM $table_name $where_clause";
   
    $pricing_data = $wpdb->get_results( $query );
    return $pricing_data;
}


// Update pricing data in the custom table
function update_pricing_data( $id, $price ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'blind_pricing';

    $data = array(
        'price'        => $price,
    );

    $where = array( 'id' => $id );

    $wpdb->update( $table_name, $data, $where );
}



// Delete pricing data from the custom table
function delete_pricing_data( $id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'blind_pricing';

    $where = array( 'id' => $id );

    $wpdb->delete( $table_name, $where );
}

//delete pricing data by blind type and group number
function delete_pricing_by_blind_type_and_group_number( $blind_type, $group_number ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'blind_pricing';

    $where = array( 'blind_type' => $blind_type, 'group_number' => $group_number );

    $wpdb->delete( $table_name, $where );
}


// AJAX handler for retrieving pricing data
add_action( 'wp_ajax_get_pricing_data', 'get_pricing_data_ajax' );
add_action( 'wp_ajax_nopriv_get_pricing_data', 'get_pricing_data_ajax' );

function get_pricing_data_ajax() {
    // Verify the AJAX request
    check_ajax_referer( 'blind_pricing_nonce_action', 'security' );

    // Get the selected blind type and group from the AJAX request
    $blind_type = $_POST['blind_type'] ?? [];
    $group_number = $_POST['group_number'] ?? '0';

    // If no values are selected, return an error message
    if ( empty( $blind_type ) ) {
        wp_send_json_error( 'Select a blind and group' );
    }

    // Get the pricing data based on the selected blind type and group
    $pricing_data = get_pricing_data( $blind_type, $group_number );
    
    if(!$pricing_data) {
        wp_send_json_error( 'No pricing data found' );
    } else {    
        // Generate the HTML markup for the pricing data table
        $table_html = render_pricing_table_data( $pricing_data );
        
        wp_send_json_success( $table_html );
    }
    //log to 'console' the data being sent back
    // error_log( print_r( $pricing_data, true ) );
}

function render_pricing_table_data( $pricing_data ) {
    //$blind_widths should be all the unique blind widths from the pricing data
    $blind_widths = array_unique( wp_list_pluck( $pricing_data, 'blind_width' ) );
    //$blind_drops should be all the unique blind drops from the pricing data
    $blind_drops = array_unique( wp_list_pluck( $pricing_data, 'blind_drop' ) );
    // $blind_widths = generate_number_array( 610, 150, 16 ); //columns 1-18   
    // $blind_drops = generate_number_array( 600, 100, 25 ); //rows 1-25

    $table_html = '<p><strong>Click on a price to edit, or the button below to delete values and upload a new file</strong></p>';
    $table_html .= '<table class="pricing-table widefat striped">';
    $table_html .= '<thead><tr>';
    $table_html .= '<th></th>'; //blank cell in top left
    foreach ( $blind_widths as $width ) {
        //widths should not have a decimal point or trailing zeros
        $width = rtrim( rtrim( $width, '0' ), '.' );
        $table_html .= '<th class="width">' . $width . '</th>';
    }
    $table_html .= '</tr></thead>';
    $table_html .= '<tbody id="pricing-table-body">';
    foreach ( $blind_drops as $drop ) {
        //drop should not have a decimal point or trailing zeros
        $drop = rtrim( rtrim( $drop, '0' ), '.' );
        $table_html .= '<tr>';
        $table_html .= '<th class="drop">' . $drop . '</th>';
        foreach ( $blind_widths as $width ) {
            //find the price for this width and drop
            $price_found = false;
            foreach ( $pricing_data as $price ) {
                if ( is_array( $price ) ) {
                    if ( $price['blind_width'] == $width && $price['blind_drop'] == $drop ) {
                        $table_html .= '<td id="'. $price['id'] .'">' . $price['price'] . '</td>';
                        $price_found = true;
                        break;
                    }
                } elseif ( is_object( $price ) ) {
                    if ( $price->blind_width == $width && $price->blind_drop == $drop ) {
                        $table_html .= '<td data-group="'. $price->group_number .'" id="'. $price->id .'">' . $price->price . '</td>';
                        $price_found = true;
                        break;
                    }
                }
            }
            if ( ! $price_found ) {
                $table_html .= '<td></td>'; // leave the cell empty if price not found
            }
        }
        $table_html .= '</tr>';
    }
    $table_html .= '</tbody></table>';
    //add button to delete all pricing data for this blind type and group
    $table_html .= '<button id="delete-pricing-data"  class="button button-danger">Delete <strong>'. $pricing_data[0]->blind_type .' Group '. $pricing_data[0]->group_number .'</strong> Data</button>';
    
    return $table_html;
}

//AJAX handler for updating pricing data
add_action( 'wp_ajax_update_pricing_data', 'update_pricing_data_ajax' );

function update_pricing_data_ajax() {
    // Verify the AJAX request
    check_ajax_referer( 'blind_pricing_nonce_action', 'security' );

    // Get the selected blind type and group, width, drop and price and id from the AJAX request
   
    $price = $_POST['price'];
    $id = $_POST['id'];

    update_pricing_data( $id, $price );
    wp_send_json_success( $id );   
}

//AJAX handler for new pricing data
add_action( 'wp_ajax_new_pricing_data', 'new_pricing_data_ajax' );

function new_pricing_data_ajax() {
    // Verify the AJAX request
    check_ajax_referer( 'blind_pricing_nonce_action', 'security' );

    // Get the selected blind type and group, width, drop and price and id from the AJAX request
    $blind_type = $_POST['blind_type'];
    $group_number = $_POST['group_number'];
    $blind_width = $_POST['blind_width'];
    $blind_drop = $_POST['blind_drop'];
    $price = $_POST['price'];

    $id = insert_pricing_data( $blind_type, $group_number, $blind_width, $blind_drop, $price );
    
    if(!$id) {
        wp_send_json_error( 'Error inserting pricing data' );
    } else {
        wp_send_json_success( $id );
    }
}

//AJAX handler for batch insert pricing data
add_action( 'wp_ajax_batch_insert_pricing_data', 'batch_insert_pricing_data_ajax' );
add_action( 'wp_ajax_nopriv_batch_insert_pricing_data', 'batch_insert_pricing_data_ajax' );

function batch_insert_pricing_data_ajax() {
    // Verify the AJAX request
    check_ajax_referer( 'blind_pricing_nonce_action', 'security' );    
    // Get the selected blind type and group, width, drop and price and id from the AJAX request
    $data = $_POST['entries'];
    
    $id = batch_insert_pricing_data( $data );
    
    if(!$id) {
        wp_send_json_error( 'Error inserting pricing data' );
    } else {
        wp_send_json_success( $id );
    }
}

//AJAX handler for bulk_delete_pricing_data
add_action( 'wp_ajax_bulk_delete_pricing_data', 'bulk_delete_pricing_data_ajax' );
add_action( 'wp_ajax_nopriv_bulk_delete_pricing_data', 'bulk_delete_pricing_data_ajax' );

function bulk_delete_pricing_data_ajax() {
    // Verify the AJAX request
    check_ajax_referer( 'blind_pricing_nonce_action', 'security' );    
    // Get the selected blind type and group, width, drop and price and id from the AJAX request
    $blind_type = $_POST['blind_type'];
    $group_number = $_POST['group_number'];
    
    $id = delete_pricing_by_blind_type_and_group_number( $blind_type, $group_number );
    
    if(!$id) {
        wp_send_json_error( 'Error deleting pricing data' );
    } else {
        wp_send_json_success( $id );
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