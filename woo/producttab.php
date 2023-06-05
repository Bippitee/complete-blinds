<?php
//Register Product Type
add_action( 'init', 'register_cblinds_product_type' );

function register_cblinds_product_type() {
    class WC_Product_Complete_Blinds extends WC_Product {
        public function __construct( $product ) {
            $this->product_type = 'complete-blinds';
            parent::__construct( $product );
        }
    }
};

// Add Tab to Product Data
add_filter( 'woocommerce_product_data_tabs', 'cblinds_product_tab' );

function cblinds_product_tab( $original_tabs) {
    $new_tab['complete-blinds'] = array(
        'label' => __( 'Complete Blinds', 'complete-blinds' ),
        'target' => 'complete-blinds_product_options',
        'class' => array( 'show_if_variable', 'show_if_simple', 'show_if_grouped' ), //maybe not variable, depends what ends up in here
    );

    $insert_at_position = 0; // This can be changed, from 0 up
    $tabs = array_slice( $original_tabs, 0, $insert_at_position, true ); // First part of original tabs
    $tabs = array_merge( $tabs, $new_tab ); // Add new
    $tabs = array_merge( $tabs, array_slice( $original_tabs, $insert_at_position, null, true ) ); // Glue the second part of original

    return $tabs;
};

/* -----------------------------------------------------------*
* Add fields to new tab
*------------------------------------------------------------*/
$supplierData = 'cblinds_product_info';

add_action( 'woocommerce_product_data_panels', 'cblinds_product_options_product_data' );

function cblinds_product_options_product_data() {
    global $supplierData;
    //get the address_book options where address_book_contact_type is "supplier"
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
    $options[''] = __( 'Select a value', 'woocommerce'); // default value
    foreach ( $suppliers as $supplier ) {
        $options[$supplier->ID] = $supplier->post_title;
    }

    ?>
    <div id='complete-blinds_product_options' class='panel woocommerce_options_panel'>
    <div class='options_group'>
        <?php
            woocommerce_wp_select(
                array(
                'id' => $supplierData,
                'label' => __( 'Supplier', 'complete-blinds' ),
                'options' => $options,
                'desc_tip' => 'true',
                'description' => __( 'Select a supplier from <br/>Address Book.<br/>', 'complete-blinds' ),
                'type' => 'select',
                )
            );
        ?>
        <!-- australian made checkbox -->
        <?php 
            woocommerce_wp_checkbox( 
                array( 
                    'id'            => 'cblinds_australian_made', 
                    'label'         => __( 'Australian Made', 'complete-blinds' ), 
                    'description'   => __( 'Select if this product is Australian Made', 'complete-blinds' ) 
                )
            );
        ?>
    </div> <!-- end options group -->
    </div> <!-- end panel -->
    <?php
}

/* -----------------------------------------------------------*
* SAVE
*------------------------------------------------------------*/
add_action( 'woocommerce_process_product_meta', 'save_supplier_settings' );

function save_supplier_settings( $post_id ){
    global $supplierData;

$cblinds_product_info = $_POST[$supplierData];

if( !empty( $cblinds_product_info ) ) {
update_post_meta( $post_id, $supplierData, esc_attr( $cblinds_product_info ) );
}
//cblinds_australian_made
$cblinds_australian_made = isset( $_POST['cblinds_australian_made'] ) ? 'yes' : 'no';
update_post_meta( $post_id, 'cblinds_australian_made', $cblinds_australian_made );

}

/* -----------------------------------------------------------*
* Add to front end - see hook guide https://www.businessbloomer.com/woocommerce-visual-hook-guide-single-product-page/
*------------------------------------------------------------*/

add_action( 'woocommerce_before_add_to_cart_form', 'cblinds_supplier_front' );

function cblinds_supplier_front () {
global $product;
global $supplierData;
$isAustralianMade = get_post_meta( $product->get_id(), 'cblinds_australian_made', true );
$supplierID = get_post_meta( $product->get_id(), $supplierData) ? get_post_meta( $product->get_id(), $supplierData )[0] : NULL;

    if($supplierID != NULL) 
    {
        echo '<p>';
    echo do_shortcode('[address_book id="' . $supplierID . '"]');
    echo do_shortcode('[address_book_website id="' . $supplierID . '"]');
    echo "<br/>";
        if ( $isAustralianMade == 'yes' ) {
            echo "Australian Made";
        }
        echo '</p>';
    }
}

/* -----------------------------------------------------------*
*  ADMIN STYLING
*------------------------------------------------------------*/
add_action( 'admin_head', 'cblinds_custom_style' );
function cblinds_custom_style() {
    $images_dir = plugins_url('../images/icons', __FILE__);
	?><style>
		#woocommerce-product-data .complete-blinds_options.active:hover > a:before,
		#woocommerce-product-data .complete-blinds_options > a:before {
			background: url(  '<?=$images_dir?>/fabric.svg' ) center center no-repeat;
			content: " " !important;
			background-size: 100%;
			width: 13px;
			height: 13px;
			display: inline-block;
			line-height: 1;
            rotate: 10deg;
            /* transition: .2s ease-in-out; */
		}
		@media only screen and (max-width: 900px) {
			#woocommerce-product-data .complete-blinds_options.active:hover > a:before,
			#woocommerce-product-data .complete-blinds_options > a:before,
			#woocommerce-product-data .complete-blinds_options:hover a:before {
				background-size: 35%;
			}
		}
		.complete-blinds_options:hover a:before {
			/* transform: rotate(5deg); */
		}

	</style><?php

}
