<?php
/**
 * Plugin Name: Give an automatic coupon
 * Plugin URI: https://github.com/ggfat/give-automatic-coupon
 * Description: A plugin that offers an automatic discount coupon for every purchase made by setting a deadline
 * Author:  GGFat
 * Author URI: https://www.ggfat.com
 * Version: 1.0
 * Text Domain: give-automatic-coupon
 * Copyright: © 2020 GGFat (https://www.ggfat.com)
 * WC tested up to: 3.7
 */
 
 if (!defined('ABSPATH')) {
  die('-1');
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	if ( ! class_exists( 'give_automatic_coupon' ) ) :
	
	/**
	 * Localisation
	 **/
	load_plugin_textdomain( 'give-automatic-coupon', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );


	class give_automatic_coupon {
	  /**
	  * Construct the plugin.
	  */
	  public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	  }
	  /**
	  * Initialize the plugin.
	  */
	  public function init() {
		  add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'give_automatic_coupon_links' ) );	
		  include_once 'includes/class-wgac-setting-tab.php';
		  include_once 'includes/class-wgac-set-coupon.php';
		  if(WCGAC_Set_Coupon::check_coupon_enabled()){
		  	add_action( 'woocommerce_order_status_changed', array('WCGAC_Set_Coupon','create_coupon' ), 10, 3 );
			add_action( 'woocommerce_account_dashboard', array('WCGAC_Set_Coupon','add_coupon_to_myaccount' ));
		  }
	  }

	  public function give_automatic_coupon_links( $links ) {
		$links[] = '<a href="admin.php?page=wc-settings&tab=settings_give_coupon">Settings</a>';
		return $links;
	  }
  	  

	}
	$give_automatic_coupon = new give_automatic_coupon( __FILE__ );
	endif;
}
?>