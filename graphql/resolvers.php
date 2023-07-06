<?php

//Product -> fabricType 
add_action( 'graphql_register_types', function () {
    // Register a custom resolver for the 'fabricType' field of the 'Product' type
    register_graphql_field( 'Product', 'fabricType', [
      'type' => [ 'list_of' => 'FabricType' ],
      'description' => __( 'The fabric type of the product.', 'complete-blinds' ),
      'resolve' => function( $product ) {
        // Get the fabric type term(s) associated with the product
        $fabric_types = get_the_terms( $product->ID, 'fabric_type' );
  
        // If fabric type terms exist, return the necessary data
        if ( ! empty( $fabric_types ) && ! is_wp_error( $fabric_types ) ) {
          // Create an array to hold the fabric type data
          $fabric_type_data = array();
  
          // Loop through the fabric type terms and add their data to the array
          foreach ( $fabric_types as $fabric_type ) {
            $fabric_type_data[] = array(
              'name' => $fabric_type->name,
              'slug' => $fabric_type->slug,
              'id' =>  $fabric_type->term_id, 
            );
          }
  
          return $fabric_type_data;
        }
  
        return null; // No fabric type found
      }
    ] );
  });

//Product -> blindType
add_action( 'graphql_register_types', function () {
    // Register a custom resolver for the 'blindType' field of the 'Product' type
    register_graphql_field( 'Product', 'blindTypeVals', [
      'type' => [ 'list_of' => 'BlindType' ],
      'description' => __( 'The blind type of the product.', 'complete-blinds' ),
      'resolve' => function( $product ) {
        // Get the blind type term(s) associated with the product with no parent
        $blind_types = get_terms( array(
          'taxonomy' => 'blind_type',
          'hide_empty' => false,
          'parent' => 0,
        ) );
  
        // If blind type terms exist, return the necessary data
        if ( ! empty( $blind_types ) && ! is_wp_error( $blind_types ) ) {
          // Create an array to hold the blind type data
          $blind_type_data = array();
  
          // Loop through the blind type terms and add their data to the array
          foreach ( $blind_types as $blind_type ) {
            //get 'cblinds_pricing_group_' . $blind_type->slug value
            $pricing_group = get_post_meta( $product->ID, 'cblinds_pricing_group_' . $blind_type->slug, true );
            if( !empty( $pricing_group ) ) {
              $blind_type_data[] = array(
                'name' => (string) $blind_type->name,
                'slug' => (string) $blind_type->slug,
                'id' =>  (string) $blind_type->term_id, 
                'pricing_group' => (Int) $pricing_group,
              );
            }
          }
        
          
          return $blind_type_data;
        }
  
        return null; // No blind type found
      }
    ] );
  });


//Product -> Supplier
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

//Pricing table lookup given group_number and blind_type, found in database $wpdb->prefix . 'blind_pricing';
add_action('graphql_register_types', function() {
    register_graphql_field('RootQuery', 'pricingTableValues', [
      'type' => ['list_of' => 'PricingTableValue'],
      'description' => __('Pricing of the product', 'complete-blinds'),
      'args' => [
        'group_number' => [
          'type' => 'Int',
          'description' => __('The pricing group number', 'complete-blinds'),
          'required' => true
        ],
        'blind_type' => [
          'type' => 'String',
          'description' => __('The blind type slug', 'complete-blinds'),
          'required' => true
        ]
      ],
      'resolve' => function($post, $args) {
        global $wpdb;
        $group_number = $args['group_number'];
        $blind_type_slug = $args['blind_type'];  
        $blind_type = get_term_by('slug', $blind_type_slug, 'blind_type');
        $query = "SELECT * FROM {$wpdb->prefix}blind_pricing WHERE group_number = %d AND blind_type = %s";
        $prepQuery = $wpdb->prepare($query, $group_number, $blind_type_slug);  
        $pricing = $wpdb->get_results($prepQuery);
        
        $pricing_values = array();
        foreach ($pricing as $row) {
            $pricing_value = array(
            'blind_type' => (string) $row->blind_type,
            'group_number' => (int) $row->group_number,
            'blind_width' => (float) $row->blind_width,
            'blind_drop' => (float) $row->blind_drop,
            'price' => (float) $row->price,
            );
            $pricing_values[] = $pricing_value;
        }
                
        return !empty($pricing_values) ? $pricing_values : ['No Value'];
      }
    ]);
  }
);

//Top level blind types i.e. no parent
add_action('graphql_register_types', function() {
    register_graphql_field('RootQuery', 'TopLevelBlindType', [
      'type' => ['list_of' => 'TopLevelBlindType'],
      'description' => __('Blind types', 'complete-blinds'),
      'resolve' => function($post) {
        $blind_types = get_terms(array(
          'taxonomy' => 'blind_type',
          'hide_empty' => false,
          'parent' => 0,
        ));
  
        $blind_type_data = array();
        foreach ($blind_types as $blind_type) {
          $blind_type_value = array(
            'name' => (string) $blind_type->name,
            'slug' => (string) $blind_type->slug,
            'id' => (string) $blind_type->term_id,
          );
          $blind_type_data[] = $blind_type_value;
        }
  
        return !empty($blind_type_data) ? $blind_type_data : ['No Value'];
      }
    ]);
  }
);

add_action('graphql_register_types', function() {
  register_graphql_field('MediaItem', 'dominantColor', [
      'type' => 'String',
      'description' => __('Dominant color of the media item', 'complete-blinds'),
      'resolve' => function($mediaItem) {
        $meta_data = wp_get_attachment_metadata($mediaItem->id);
        if (isset($meta_data['dominant_color'])) {
          $dominantColor = $meta_data['dominant_color'];
          return !empty($dominantColor) ? $dominantColor : 'No Value';
      }
    }
  ]);
});

add_action('graphql_register_types', function() {
  register_graphql_field('MediaItem', 'base64', [
      'type' => 'String',
      'description' => __('Converted to base64 to fix CORS issues', 'complete-blinds'),
      'resolve' => function($mediaItem) {
        $imageData=base64_encode(file_get_contents($mediaItem->guid));
        $mimeType = wp_check_filetype($mediaItem->guid)['type'];
        error_log(print_r(wp_check_filetype($mediaItem->guid), true));

        
        return !empty($imageData) ? 'data:'.$mimeType.';base64,'.$imageData : 'No Value';
      }
  ]);
});