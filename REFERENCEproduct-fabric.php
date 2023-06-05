<?php
//Register Product type
 add_action( 'init', 'register_cblinds_product_type' );
function register_cblinds_product_type() {
    class WC_Product_Demo extends WC_Product {

        public function __construct( $product ) {
            $this->product_type = 'fabric';
            parent::__construct( $product );
        }
    }
}


//CHANGED TO CHECKBOX INSTEAD
// //Add product type to DROPDOWN
// add_filter( 'product_type_selector', 'add_cblinds_product_type' );
// function add_cblinds_product_type( $types ){
// $types[ 'fabric' ] = __( 'Fabric', 'complete-blinds' );
// return $types; 
// }


//add product type TAB
add_filter( 'woocommerce_product_data_tabs', 'cblinds_product_tab' );
function cblinds_product_tab( $original_tabs) {

$new_tab['fabric'] = array(
'label' => __( 'Fabric', 'complete-blinds' ),
'target' => 'fabric_product_options',
'class' => array( 'show_if_variable', 'show_if_fabric'),
);

    $insert_at_position = 1; // This can be changed, from 0 up
	$tabs = array_slice( $original_tabs, 0, $insert_at_position, true ); // First part of original tabs
	$tabs = array_merge( $tabs, $new_tab ); // Add new
	$tabs = array_merge( $tabs, array_slice( $original_tabs, $insert_at_position, null, true ) ); // Glue the second part of original

	return $tabs;

}


//add TAB FIELDS
add_action( 'woocommerce_product_data_panels', 'cblinds_custom_product_options_product_tab_content' );

function cblinds_custom_product_options_product_tab_content() {
    //get the address_book options where address_book_contact_type is "supplier"

    // Dont forget to change the ID in the div with the TARGET of the product tab (line 30?)
    ?><div id='fabric_product_options' class='panel woocommerce_options_panel'><?php
    ?><div class='options_group'><?php

    woocommerce_wp_checkbox( array(
    'id' => '_enable_custom_product',
    'label' => __( 'Enable Custom product Type'),
    ) );

    woocommerce_wp_text_input(
        array(
            'id' => 'cblinds_product_info',
            'label' => __( 'Custom Product details', 'complete-blinds' ),
            'placeholder' => '',
            'desc_tip' => 'true',
            'description' => __( 'Enter Fabric details.', 'complete-blinds' ),
            'type' => 'text'
        )
    );
    ?></div>
    </div><?php
}



/* -----------------------------------------------------------*
* SAVE
*------------------------------------------------------------*/
add_action( 'woocommerce_process_product_meta', 'save_fabric_product_settings' );

function save_fabric_product_settings( $post_id ){

$cblinds_product_info = $_POST['cblinds_product_info'];

if( !empty( $cblinds_product_info ) ) {
update_post_meta( $post_id, 'cblinds_product_info', esc_attr( $cblinds_product_info ) );
}
}

/* -----------------------------------------------------------*
* Add to front end
*------------------------------------------------------------*/

add_action( 'woocommerce_single_product_summary', 'cblinds_product_front' );

function cblinds_product_front () {
global $product;
if ( 'fabric' == $product->get_type() ) { 
echo( get_post_meta( $product->get_id(), 'cblinds_product_info' )[0] );
}
}

/* -----------------------------------------------------------*
*  ADMIN STYLING
*------------------------------------------------------------*/
add_action( 'admin_head', 'cblinds_custom_style' );
function cblinds_custom_style() {
    $images_dir = plugins_url('/images/icons', __FILE__);
	?><style>
		#woocommerce-product-data .fabric_options.active:hover > a:before,
		#woocommerce-product-data .fabric_options > a:before {
			background: url(  '<?=$images_dir?>/fabric.svg' ) center center no-repeat;
			content: " " !important;
			background-size: 100%;
			width: 13px;
			height: 13px;
			display: inline-block;
			line-height: 1;
            rotate: 10deg;
            transition: .2s ease-in-out;
		}
		@media only screen and (max-width: 900px) {
			#woocommerce-product-data .fabric_options.active:hover > a:before,
			#woocommerce-product-data .fabric_options > a:before,
			#woocommerce-product-data .fabric_options:hover a:before {
				background-size: 35%;
			}
		}
		.fabric_options:hover a:before {
			transform: scale(1.5) rotate(5deg);

		}

	</style><?php

}

/* -----------------------------------------------------------*
*  add product type CHECKBOX (next to Virtual/Downloadable)
*------------------------------------------------------------*/

add_filter("product_type_options", function ($product_type_options) {

    $product_type_options["fabric"] = [
        "id"            => "_fabric",
        "wrapper_class" => 'show_if_simple show_if_variable',
        "label"         => __("Fabric", 'complete-blinds'),
        "description"   => __("Fabric products need to include more information.",'complete-blinds'),
        "default"       => "no",
    ];

    return $product_type_options;

});

//save value from above
add_action("save_post_product", function($post_ID, $product, $update) {

    update_post_meta(
          $product->ID
        , "_fabric"
        , isset($_POST["_fabric"]) ? "yes" : "no"
    );

}, 10, 3);

/* -----------------------------------------------------------*
* Handle checkbox changes (JS)
*------------------------------------------------------------*/
function action_admin_footer() {
    ?>
    <script>
        jQuery(document).ready(function($) {
    // Check the initial state of the checkbox
    var is_fabric = $('input#_fabric').is(':checked');
    toggleFabricRules(is_fabric);

    $('input#_fabric').change(function() {
        var is_fabric = $(this).is(':checked');
        toggleFabricRules(is_fabric);
    });

    function toggleFabricRules(is_fabric) {
        if (is_fabric) {
            $('.show_if_fabric').show();
        } else {
            $('.show_if_fabric').hide();
        }
    }
});
    </script>
    <?php
};

add_action('admin_footer', 'action_admin_footer');
