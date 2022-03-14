<?php
/* Plugin Name: Woocommerce User Role based Product price
   Plugin URI : 
   Description : Woocommerce Component for WordPress 
   Version : 1.0 
   Author : Nitika Tiwari
   
 */
 add_action( 'admin_init', 'wc_addon_has_woocommerce' );
function wc_addon_has_woocommerce() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        add_action( 'admin_notices', 'require_plugin_notice' );

        deactivate_plugins( plugin_basename( __FILE__ ) ); 

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}

function require_plugin_notice(){
    ?><div class="error"><p>Sorry, but Role based price Plugin requires the Woocommerce plugin to be installed and active.</p></div>
<?php
}

function add_roles_on_plugin_activation() {
       add_role( 'distributor', 'Distributor', array( 'read' => true, 'level_0' => true ) );
   }
register_activation_hook( __FILE__, 'add_roles_on_plugin_activation' );

/**
 * Filter the price based on user role
 */
function th_reseller_price( $price ) {

   if ( ! is_user_logged_in() )
      return $price;

   // Function which checks if logged in user is role
   $user = wp_get_current_user();
if ( in_array( 'customer', (array) $user->roles ) ) {
      $reseller_percentage = 0.9;
      $price = $price * $reseller_percentage;
   }
 if ( in_array( 'distributor', (array) $user->roles ) ) {
      $reseller_percentage = 0.8;
      $price = $price * $reseller_percentage;
   }
   return $price;

}
add_filter( 'woocommerce_product_get_price', 'th_reseller_price', 10, 2 );
add_filter( 'woocommerce_product_variation_get_price', 'th_reseller_price', 10, 2 );
add_filter( 'woocommerce_product_get_regular_price', 'th_reseller_price', 10, 2 );
//add_filter( 'woocommerce_product_get_sale_price', 'th_reseller_price', 10, 2 );
add_filter( 'woocommerce_product_data_tabs', 'user_role_product_data_tab' , 99 , 1 );

/**
* Add new tab on product page on admin to set price based on role
*/
function user_role_product_data_tab( $product_data_tabs ) {
    $product_data_tabs['user-based-price'] = array(
        'label' => __( 'User Role Pricing', 'my_text_domain' ),
        'target' => 'user_role_product_data',
    );
    return $product_data_tabs;
}
add_action( 'woocommerce_product_data_panels', 'add_my_custom_product_data_fields' );
function add_my_custom_product_data_fields() {
    global $woocommerce, $post;
    ?>
    <!-- id below must match target registered in above add_my_custom_product_data_tab function -->
    <div id="user_role_product_data" class="panel woocommerce_options_panel">
        <?php
		global $wp_roles;

    $all_roles = $wp_roles->get_names();
    $editable_roles = apply_filters('editable_roles', $all_roles);
	foreach($all_roles as $role) { 
		echo '<p><strong>'. $role . '</strong></p>';
        woocommerce_wp_checkbox( array( 
            'id'            => '_product_user_role_'. $role, 
            'wrapper_class' => 'show_if_simple', 
            'label'         => __( '', 'my_text_domain' ),
            'default'       => '0',
            'desc_tip'      => false,
        ) );
		woocommerce_wp_text_input( array( 
            'id'            => 'role_price_'. $role, 
            'wrapper_class' => 'show_if_simple', 
            'label'         => __( 'Price', 'my_text_domain' ),
            'default'       => '0',
            'desc_tip'      => false,
        ) );
		}
        ?>
    </div>
    <?php
}

add_action( 'woocommerce_process_product_meta', 'woocommerce_process_product_meta_fields_save' );
function woocommerce_process_product_meta_fields_save( $post_id ){
    // This is the case to save custom field data of checkbox. You have to do it as per your custom fields
    $woo_checkbox = isset( $_POST['_product_user_role_'] ) ? 'yes' : 'no';
    update_post_meta( $post_id, '_product_user_role_', $woo_checkbox );
}


?>
