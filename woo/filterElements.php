<?php
/**
  * Filter list of features and remove those not needed  (marketing and anayltics)*
  */
 add_filter( 'woocommerce_admin_features', function( $features ) {
    return array_values(
        array_filter( $features, function($feature) {
            return $feature !== 'marketing' && $feature !== 'analytics';
        } )
    );
} );



/* -----------------------------------------------------------*
* Remove VIRTUAL AND DOWNLOADABLE product types (also need to remove endpoint from Woocommerce -> Settings -> Advanced -> Account Endpoints)
*  -----------------------------------------------------------*/

//  From dropdown
 add_filter( 'product_type_selector', 'remove_product_types' );
function remove_product_types( $types ){
    // unset( $types['grouped'] );
    unset( $types['external'] );
    // unset( $types['variable'] );

    return $types;
}

// From product type checkboxes
add_filter( 'product_type_options', function( $options ) {
	// remove "Virtual" checkbox
	if( isset( $options[ 'virtual' ] ) ) {
		unset( $options[ 'virtual' ] );
	}
	// remove "Downloadable" checkbox
	if( isset( $options[ 'downloadable' ] ) ) {
		unset( $options[ 'downloadable' ] );
	}
	return $options;
} );

// remove the separator next to the checkboxes via CSS - this is a hacky way to do it, but it works
add_action( 'admin_head', 'remove_product_type_separator' );
function remove_product_type_separator() {
    echo '<style>label[for="product-type"] {border-right: none !important;}</style>';
}