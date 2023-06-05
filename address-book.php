<?php 
function address_book_register_post_type() {
    $labels = array(
        'name'               => __('Address Book', 'complete-blinds'),
        'singular_name'      => __('Contact', 'complete-blinds'),
        'menu_name'          => __('Address Book', 'complete-blinds'),
        'name_admin_bar'     => __('Address Book', 'complete-blinds'),
        'add_new'            => __('Add New', 'complete-blinds'),
        'add_new_item'       => __('Add New Contact', 'complete-blinds'),
        'edit_item'          => __('Edit Contact', 'complete-blinds'),
        'new_item'           => __('New Contact', 'complete-blinds'),
        'view_item'          => __('View Contact', 'complete-blinds'),
        'search_items'       => __('Search Contacts', 'complete-blinds'),
        'not_found'          => __('No contacts found', 'complete-blinds'),
        'not_found_in_trash' => __('No contacts found in rubbish', 'complete-blinds'),
        'all_items'          => __('All Contacts', 'complete-blinds'),
    );

    $args = array(
        'labels'      => $labels,
        'public'      => true,
        'has_archive' => true,
        'supports'    => array(),
        'show_in_menu'=> false,
        'show_ui'     => true,
        'menu_icon'   => 'dashicons-businessman',
    );

    register_post_type( 'address_book', $args );
}
add_action( 'init', 'address_book_register_post_type' );


//add custom meta box to address_book post type
function address_book_add_meta_boxes($post) {
    add_meta_box('address_book_meta_box', 'Contact Details', 'address_book_build_meta_box', 'address_book', 'normal', 'high');
}

add_action('add_meta_boxes_address_book', 'address_book_add_meta_boxes');

//remove main editor from address_book post type
function address_book_remove_editor() {
    remove_post_type_support('address_book', 'editor');
}

add_action('init', 'address_book_remove_editor');

//build custom meta box for address_book post type
function address_book_build_meta_box($post) {
    //make sure the form request comes from WordPress
    wp_nonce_field(basename(__FILE__), 'address_book_meta_box_nonce');
    //retrieve the _address_book_fb field value from the database
    $main_contact = get_post_meta($post->ID, '_address_book_main_contact', true);
    $email = get_post_meta($post->ID, '_address_book_email', true);
    $phone = get_post_meta($post->ID, '_address_book_phone', true);
    $website = get_post_meta($post->ID, '_address_book_website', true);
    $address = get_post_meta($post->ID, '_address_book_address', true);
    $city = get_post_meta($post->ID, '_address_book_city', true);
    $state = get_post_meta($post->ID, '_address_book_state', true);
    $postcode = get_post_meta($post->ID, '_address_book_postcode', true);
    $country = get_post_meta($post->ID, '_address_book_country', true);
    $placeId = get_post_meta($post->ID, '_address_book_placeId', true); //For use with Google Maps API
    
    ?>
    <table class="form-table" id="address_book_table_with_autocomplete">
        <tr>
            <th><label for="address_book_address">Address</label></th>
            <td>
                <input type="text" name="address_book_address" id="address_book_address" class="regular-text" value="<?php echo esc_attr($address); ?>" />
                <input type="hidden" name="address_book_placeId" id="address_book_placeId" value="<?php echo esc_attr($placeId); ?>" />
            </td>
        </tr>
        <tr>
            <th><label for="address_book_city">Suburb</label></th>
            <td><input type="text" name="address_book_city" id="address_book_city" class="regular-text" value="<?php echo esc_attr($city); ?>" /></td>
        </tr>
        <tr>
            <th><label for="address_book_state">State</label></th>
            <td><input type="text" name="address_book_state" id="address_book_state" class="regular-text" value="<?php echo esc_attr($state); ?>" /></td>
        </tr>
        <tr>
            <th><label for="address_book_postcode">Postcode</label></th>
            <td><input type="text" name="address_book_postcode" id="address_book_postcode" class="regular-text" value="<?php echo esc_attr($postcode); ?>" /></td>
        </tr>
        <tr>
            <th><label for="address_book_country">Country</label></th>
            <td><input type="text" name="address_book_country" id="address_book_country" class="regular-text" value="<?php echo esc_attr($country); ?>" /></td>
        </tr>
        <tr>
            <th><label for="address_book_phone">Phone</label></th>
            <td><input type="text" name="address_book_phone" id="address_book_phone" class="regular-text" value="<?php echo esc_attr($phone); ?>" /></td>
        </tr>
        <tr>
            <th><label for="address_book_website">Website</label></th>
            <td><input type="text" name="address_book_website" id="address_book_website" class="regular-text" value="<?php echo esc_attr($website); ?>" /></td>
        </tr>
        <tr>
           <th><label for="address_book_email">Email</label></th>
           <td><input type="text" name="address_book_email" id="address_book_email" class="regular-text" value="<?php echo esc_attr($email); ?>" /></td>
       </tr>
        <tr>
            <th><label for="address_book_main_contact">Main Contact (if applicable)</label></th>
            <td><input type="text" name="address_book_main_contact" id="address_book_main_contact" class="regular-text" value="<?php echo esc_attr($main_contact); ?>" /></td>
        </tr>
       
    </table>
    <?php
}



//save custom meta box data to database
function address_book_save_meta_boxes_data($post_id) {
    //verify meta box nonce
    if (!isset($_POST['address_book_meta_box_nonce']) || !wp_verify_nonce($_POST['address_book_meta_box_nonce'], basename(__FILE__))) {
        return;
    }
    //return if autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    //check the user's permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    //store custom fields values
    //main contact name
    if (isset($_REQUEST['address_book_main_contact'])) {
        update_post_meta($post_id, '_address_book_main_contact', sanitize_text_field($_POST['address_book_main_contact']));
    }
    //email
    if (isset($_REQUEST['address_book_email'])) {
        update_post_meta($post_id, '_address_book_email', sanitize_text_field($_POST['address_book_email']));
    }
    //website
    if (isset($_REQUEST['address_book_website'])) {
        update_post_meta($post_id, '_address_book_website', sanitize_text_field($_POST['address_book_website']));
    }
    //phone
    if (isset($_REQUEST['address_book_phone'])) {
        update_post_meta($post_id, '_address_book_phone', sanitize_text_field($_POST['address_book_phone']));
    }
    //address
    if (isset($_REQUEST['address_book_address'])) {
        update_post_meta($post_id, '_address_book_address', sanitize_text_field($_POST['address_book_address']));
    }
    //city
    if (isset($_REQUEST['address_book_city'])) {
        update_post_meta($post_id, '_address_book_city', sanitize_text_field($_POST['address_book_city']));
    }
    //state
    if (isset($_REQUEST['address_book_state'])) {
        update_post_meta($post_id, '_address_book_state', sanitize_text_field($_POST['address_book_state']));
    }
    //postcode
    if (isset($_REQUEST['address_book_postcode'])) {
        update_post_meta($post_id, '_address_book_postcode', sanitize_text_field($_POST['address_book_postcode']));
    }
    //country
    if (isset($_REQUEST['address_book_country'])) {
        update_post_meta($post_id, '_address_book_country', sanitize_text_field($_POST['address_book_country']));
    }
    //placeId
    if (isset($_REQUEST['address_book_placeId'])) {
        update_post_meta($post_id, '_address_book_placeId', sanitize_text_field($_POST['address_book_placeId']));
    }
    
}

add_action('save_post', 'address_book_save_meta_boxes_data');

//add custom columns to address_book post type
function address_book_custom_columns($columns) {
    $columns = array(
        'cb' => 'cb',
        'title' => 'Company Name',
        'address_book_city' => 'Suburb',
        'address_book_website' => 'Website',
        'address_book_email' => 'Email',
        'address_book_phone' => 'Phone',
        // 'address_book_address' => 'Address',
        // 'address_book_state' => 'State',
        // 'address_book_postcode' => 'Postcode',
        // 'address_book_country' => 'Country',
        // 'date' => 'Date'
        // 'address_book_placeId' => 'Place ID' <-- this will likely never be used
        // 'address_book_main_contact' => 'Main Contact',
        'address_book_contact_type' => 'Contact Type',
    );
    return $columns;
}

add_filter('manage_address_book_posts_columns', 'address_book_custom_columns');

//populate custom columns with data
function address_book_custom_columns_data($column, $post_id) {
    //email column
    if ('address_book_email' === $column) {
        echo '<a href="mailto:' . esc_attr(get_post_meta($post_id, '_address_book_email', true)) . '">' . esc_attr(get_post_meta($post_id, '_address_book_email', true)) . '</a>';
    }
    //website column
    if ('address_book_website' === $column) {
        echo '<a href="'. esc_attr(get_post_meta($post_id, '_address_book_website', true)) . '" target="_blank">' . esc_attr(get_post_meta($post_id, '_address_book_website', true)) . '</a>';
    }
    //phone column
    if ('address_book_phone' === $column) {
        echo esc_attr(get_post_meta($post_id, '_address_book_phone', true));
    }
    //address column
    if ('address_book_address' === $column) {
        echo esc_attr(get_post_meta($post_id, '_address_book_address', true));
    }
    //city column
    if ('address_book_city' === $column) {
        echo esc_attr(get_post_meta($post_id, '_address_book_city', true));
    }
    //state column
    if ('address_book_state' === $column) {
        echo esc_attr(get_post_meta($post_id, '_address_book_state', true));
    }
    //postcode column
    if ('address_book_postcode' === $column) {
        echo esc_attr(get_post_meta($post_id, '_address_book_postcode', true));
    }
    //country column
    if ('address_book_country' === $column) {
        echo esc_attr(get_post_meta($post_id, '_address_book_country', true));
    }
    //placeId column
    if ('address_book_placeId' === $column) {
        echo esc_attr(get_post_meta($post_id, '_address_book_placeId', true));
    }
    //contact type column
    if ('address_book_contact_type' === $column) {
        $terms = get_the_terms($post_id, 'address_book_contact_type');
        if (!empty($terms)) {
            foreach ($terms as $term) {
                echo esc_html($term->name);
            }
        } else {
            echo '<p>No contact type found</p>';
        }
    }
}

add_action('manage_address_book_posts_custom_column', 'address_book_custom_columns_data', 10, 2);

//make custom columns sortable
function address_book_sortable_columns($columns) {
    $columns['address_book_email'] = 'address_book_email';
    $columns['address_book_website'] = 'address_book_website';
    $columns['address_book_phone'] = 'address_book_phone';
    $columns['address_book_address'] = 'address_book_address';
    $columns['address_book_city'] = 'address_book_city';
    $columns['address_book_state'] = 'address_book_state';
    $columns['address_book_postcode'] = 'address_book_postcode';
    $columns['address_book_country'] = 'address_book_country';
    $columns['address_book_contact_type'] = 'address_book_contact_type';
    return $columns;
}

add_filter('manage_edit-address_book_sortable_columns', 'address_book_sortable_columns');

//make custom columns sortable by meta value
function address_book_sortable_columns_by_meta($query) {
    if (!is_admin()) {
        return;
    }
    $orderby = $query->get('orderby');
    if ('address_book_email' === $orderby) {
        $query->set('meta_key', '_address_book_email');
        $query->set('orderby', 'meta_value');
    }
    if ('address_book_website' === $orderby) {
        $query->set('meta_key', '_address_book_website');
        $query->set('orderby', 'meta_value');
    }
    if ('address_book_phone' === $orderby) {
        $query->set('meta_key', '_address_book_phone');
        $query->set('orderby', 'meta_value');
    }
    if ('address_book_address' === $orderby) {
        $query->set('meta_key', '_address_book_address');
        $query->set('orderby', 'meta_value');
    }
    if ('address_book_city' === $orderby) {
        $query->set('meta_key', '_address_book_city');
        $query->set('orderby', 'meta_value');
    }
    if ('address_book_state' === $orderby) {
        $query->set('meta_key', '_address_book_state');
        $query->set('orderby', 'meta_value');
    }
    if ('address_book_postcode' === $orderby) {
        $query->set('meta_key', '_address_book_postcode');
        $query->set('orderby', 'meta_value');
    }
    if ('address_book_country' === $orderby) {
        $query->set('meta_key', '_address_book_country');
        $query->set('orderby', 'meta_value');
    }
    if ('address_book_contact_type' === $orderby) {
        $query->set('meta_key', '_address_book_contact_type');
        $query->set('orderby', 'meta_value');
    }
}

add_action('pre_get_posts', 'address_book_sortable_columns_by_meta');


//change 'Add Title' text in admin panel
function address_book_change_default_title( $title ){
     $screen = get_current_screen();
 
     if  ( 'address_book' == $screen->post_type ) {
          $title = '';
     }
 
     return $title;
}

add_filter( 'enter_title_here', 'address_book_change_default_title' );


//taxonomy for contact types
function cb_register_taxonomy_contact_type() {
     $labels = array(
         'name'              => _x( 'Contact Type', 'taxonomy general name' ),
         'singular_name'     => _x( 'Contact Type', 'taxonomy singular name' ),
         'search_items'      => __( 'Search Contact Types' ),
         'all_items'         => __( 'All Contact Types' ),
         'parent_item'       => __( 'Parent Contact Type' ),
         'parent_item_colon' => __( 'Parent Contact Type:' ),
         'edit_item'         => __( 'Edit Contact Type' ),
         'update_item'       => __( 'Update Contact Type' ),
         'add_new_item'      => __( 'Add New Contact Type' ),
         'new_item_name'     => __( 'New Contact Type Name' ),
         'menu_name'         => __( 'Contact Type' ),
     );
     $args   = array(
         'hierarchical'      => true, // make it hierarchical (like categories)
         'labels'            => $labels,
         'show_ui'           => true,
         'show_admin_column' => true,
         'query_var'         => true,
         'rewrite'           => [ 'slug' => 'contact_type' ],
     );
     register_taxonomy( 'address_book_contact_type', [ 'address_book' ], $args );
}

add_action( 'init', 'cb_register_taxonomy_contact_type' );


//add shortcode to show title of address_book post type by id
function address_book_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'id' => '',
        ),
        $atts,
        'address_book'
    );
    $id = $atts['id'];
    $title = get_the_title($id);
    return $title;
}

add_shortcode('address_book', 'address_book_shortcode');

//add shortcode to return title and website of address_book post type by id
function address_book_title_website_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'id' => '',
        ),
        $atts,
        'address_book'
    );
    $id = $atts['id'];
    $website = get_post_meta($id, '_address_book_website', true);
    $linksvg = '<i style="margin: 0 0 0 4px;"><svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
  <path d="M12.232 4.232a2.5 2.5 0 013.536 3.536l-1.225 1.224a.75.75 0 001.061 1.06l1.224-1.224a4 4 0 00-5.656-5.656l-3 3a4 4 0 00.225 5.865.75.75 0 00.977-1.138 2.5 2.5 0 01-.142-3.667l3-3z" />
  <path d="M11.603 7.963a.75.75 0 00-.977 1.138 2.5 2.5 0 01.142 3.667l-3 3a2.5 2.5 0 01-3.536-3.536l1.225-1.224a.75.75 0 00-1.061-1.06l-1.224 1.224a4 4 0 105.656 5.656l3-3a4 4 0 00-.225-5.865z" />
</svg></i>';
    return '<span><a href="' . $website . '" target="_blank">' . $linksvg . '</a></span>';
}

add_shortcode('address_book_website', 'address_book_title_website_shortcode');

//link user to address_book post type from admin panel
function add_extra_user_fields( $user ) {
    if ( ! current_user_can( 'administrator' ) ) {
        return;
    }
    $userid = $user->ID;
    $args = array(
        'post_type' => 'address_book',
        'posts_per_page' => -1,
    );
    $query = new WP_Query( $args );

    $terms = get_terms( array(
        'taxonomy' => 'address_book_contact_type',
        'hide_empty' => false,
    ) );
    
    ?>
    <div style="background: #fcfcfc; padding: 1rem; margin: 0 -20px;">
    <h3 style="color: #2c3993">Address Book Linking</h3>
    <table class="form-table">
        <tr>
            <th style="color: #2c3993"><label for="Customer Type">Customer Type</label></th>
            <td>
                <select style="min-width: 350px; max-width: 100%;" id="contact-type-selector" name="address_book_contact_type">
                    <option value="">Select a Customer Type</option>
                    <?php foreach ( $terms as $term ) { ?>
                        <option value="<?php echo $term->slug; ?>" <?php
                        $saved_contact_type = get_user_meta( $userid, 'address_book_contact_type', true );
                        if ( $saved_contact_type == $term->slug ){ 
                            echo 'selected'; 
                        }?>>
                        <?php echo $term->name; ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <th style="color: #2c3993"><label for="Address Book Entry">Address Book Entry</label></th>
            <td>
                <select style="min-width: 350px; max-width: 100%;" name="address_book_profile" id="address-book-profile">
                    <option value="">Select an Address Book Entry</option>
                    <?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?>
                        <option value="<?php the_ID(); ?>" <?php
                        $saved_address_book_profile = get_user_meta( $userid, 'address_book_profile', true );
                        if ( $saved_address_book_profile == get_the_ID() ){ 
                            echo 'selected'; 
                        }?>>
                        <?php the_title(); ?>
                        </option>
                    <?php endwhile; endif; ?>
                </select>
            </td>
        </tr>
    </table>
    </div>
    <?php
}

add_action( 'show_user_profile', 'add_extra_user_fields' );
add_action( 'edit_user_profile', 'add_extra_user_fields' );

function save_extra_user_fields( $user_id ) {
    if ( !current_user_can( 'edit_user', $user_id ) ) {
        return false;
    } else {
        update_user_meta( $user_id, 'address_book_contact_type', sanitize_text_field( $_POST['address_book_contact_type'] ) );
        update_user_meta( $user_id, 'address_book_profile', sanitize_text_field( $_POST['address_book_profile'] ) );
    }
}

add_action( 'personal_options_update', 'save_extra_user_fields' );
add_action( 'edit_user_profile_update', 'save_extra_user_fields' );


// Enqueue JavaScript to handle the dropdown interaction
function enqueue_addressbook_script() {
      wp_enqueue_script( 'user-admin', plugin_dir_url(__FILE__) . '/js/user-admin.js', array( 'jquery' ), '1.0', true );
}

add_action( 'admin_enqueue_scripts', 'enqueue_addressbook_script' );


//REST endpoint
// Register custom REST API endpoint
function register_address_book_entries_endpoint() {
    register_rest_route( 'complete-blinds/v1', '/get-address-book-entries/(?P<contact_type>[a-zA-Z0-9_-]+)', array(
        'methods' => 'GET',
        'callback' => 'get_address_book_entries_callback',
    ) );
}

add_action( 'rest_api_init', 'register_address_book_entries_endpoint' );

// Callback function to retrieve address book entries based on contact type
function get_address_book_entries_callback( $request ) {
    $contact_type = $request->get_param( 'contact_type' );

    $args = array(
        'post_type' => 'address_book',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'address_book_contact_type',
                'field' => 'slug',
                'terms' => $contact_type,
            ),
        ),
    );


    $query = new WP_Query( $args );

    $entries = array();
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $entries[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'label' => get_the_title() . ' (' . get_post_meta( get_the_ID(), '_address_book_city', true ) . ')',
                'selected' => (get_user_meta( get_current_user_id(), 'address_book_profile', true ) == get_the_ID()),
            );
        }
    }

    wp_reset_postdata();

    return $entries;
}