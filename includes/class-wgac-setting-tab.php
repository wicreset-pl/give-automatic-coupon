<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if ( ! class_exists( 'WGCA_Settings' ) ) :


function wgac_add_settings() {

	class WGCA_Settings extends WC_Settings_Page {

		public function __construct() {		
		
			$this->id    = 'settings_give_coupon';
			$this->label = __( 'Give Automatic Coupon', 'give-automatic-coupon' );		
				
			add_filter( 'woocommerce_settings_tabs_array',        array( $this, 'add_settings_page' ), 20 );
			add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );			
			add_action( 'woocommerce_settings_' . $this->id,      array( $this, 'output' ) );
			add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
		}
		
	public function output_sections() {

		global $current_section;

		$sections = $this->get_sections();

		echo '<h1>'.$this->label.' (Santa)</h1><br>';
	}		
				
		public function get_settings() {

				$settings = apply_filters( 'wgac_settings', array(			
					array(
						'name'     => __( 'Settings coupon', 'give-automatic-coupon' ),
						'type'     => 'title',
						'desc'     => '',
						'id'       => 'wgac_section_coupon_title'
					),		
					array(
						'type'     => 'checkbox',
						'name'     => __( 'Enabled', 'give-automatic-coupon' ),
						'desc'     => __( 'Enable the plugin', 'give-automatic-coupon' ),
						'default'  => 'no',
						'id'       => 'wgac_coupon_enabled',						
					),					
					array(
						'name'             => __( 'Discount Percentage', 'give-automatic-coupon'),
						'type'              => 'number',
						'desc_tip'       => __( 'The discount percentage to be applied.', 'give-automatic-coupon'),
						'default'           => '',
						'css'      => 'width:150px;',
						'id'   => 'wgac_discount_percentage'				
					),
					array(
						'name'             => __( 'Expiration day', 'give-automatic-coupon'),
						'type'              => 'number',
						'desc_tip'       => __( 'The number of days that the coupon validity.', 'give-automatic-coupon'),
						'default'           => '',
						'css'      => 'width:150px;',
						'id'   => 'wgac_expiration_date'
					),
					array(
						'name'             => __( 'Description coupon', 'give-automatic-coupon'),
						'type'              => 'text',
						'desc_tip'       => __( 'Set the description for coupon.', 'give-automatic-coupon'),
						'default'           => '',
						'id'   => 'wgac_coupon_description'
					),	
					array(
						'type' => 'sectionend',
						'id' => 'wgac_section_coupon_end'
					),	
					array(
						'name'     => __( 'Settings Email', 'give-automatic-coupon' ),
						'type'     => 'title',
						'desc'     => '',
						'id'       => 'wgac_section_email_title'
					),
					array(
						'name'             => __( 'Email Subject', 'give-automatic-coupon'),
						'type'              => 'text',
						'desc_tip'       => __( 'Set the subject for email to send to customer.', 'give-automatic-coupon'),
						'default'           => '',
						'id'   => 'wgac_subject_email'
					),					
					array(
						'name'             => __( 'Email Content', 'give-automatic-coupon'),
						'type'              => 'textarea',
						'desc'			=> __('<i>Enter [couponcode] instead of the coupon code</i>', 'give-automatic-coupon'),
						'desc_tip'       => __( 'Set the content for email to send to customer.', 'give-automatic-coupon'),
						'default'           => '',
						'css'      => 'height:500px;',						
						'id'   => 'wgac_content_email'
					),
					array(
						'type' => 'sectionend',
						'id' => 'wgac_section_email_end'
					),																	
				) );

			return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings );
			
		}
		
		
		public function output() {
			$settings = $this->get_settings();
			WC_Admin_Settings::output_fields( $settings );
		}
		
		
		public function save() {
			$settings = $this->get_settings();
			WC_Admin_Settings::save_fields( $settings );
		}

	}
	
	return new WGCA_Settings();

}
add_filter( 'woocommerce_get_settings_pages', 'wgac_add_settings', 15 );

endif;
?>