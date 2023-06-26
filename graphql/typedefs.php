<?php

//Fabric Type definition
add_action( 'graphql_register_types', function () {
  register_graphql_object_type( 'FabricType', [
    'description' => __( 'Fabric type details.', 'complete-blinds' ),
    'fields'      => [
      'name' => [
        'type'        => 'String',
        'description' => __( 'The name of the fabric type.', 'complete-blinds' ),
      ],
      'slug' => [
        'type'        => 'String',
        'description' => __( 'The slug of the fabric type.', 'complete-blinds' ),
      ],
      'id'   => [
        'type'        => 'ID',
        'description' => __( 'The ID of the fabric type.', 'complete-blinds' ),
      ],
    ],
  ] );
});

//Blind Type definition
add_action( 'graphql_register_types', function () {
  register_graphql_object_type( 'BlindType', [
    'description' => __( 'Blind type details.', 'complete-blinds' ),
    'fields'      => [
      'name' => [
        'type'        => 'String',
        'description' => __( 'The name of the blind type.', 'complete-blinds' ),
      ],
      'slug' => [
        'type'        => 'String',
        'description' => __( 'The slug of the blind type.', 'complete-blinds' ),
      ],
      'id'   => [
        'type'        => 'ID',
        'description' => __( 'The ID of the blind type.', 'complete-blinds' ),
      ],
        'pricing_group' => [
            'type'        => 'Int',
            'description' => __( 'The pricing group of the blind type.', 'complete-blinds' ),
        ],
    ],
  ] );
});

//Pricing table values definition
add_action( 'graphql_register_types', function () {
  register_graphql_object_type( 'PricingTableValue', [
    'description' => __( 'Pricing table values.', 'complete-blinds' ),
    'fields'      => [
      'blind_type' => [
        'type'        => 'String',
        'description' => __( 'The blind type (roller, vertical, etc).', 'complete-blinds' ),
      ],
      'group_number' => [
        'type'        => 'Integer',
        'description' => __( 'The pricing group of the blind.', 'complete-blinds' ),
      ],
      'blind_width' => [
        'type'        => 'Float',
        'description' => __( 'The width of the blind.', 'complete-blinds' ),
      ],
      'blind_drop' => [
        'type'        => 'Float',
        'description' => __( 'The drop of the blind.', 'complete-blinds' ),
      ],
      'price'   => [
        'type'        => 'Float',
        'description' => __( 'The price of the blind.', 'complete-blinds' ),
      ],
    ],
  ] );
});