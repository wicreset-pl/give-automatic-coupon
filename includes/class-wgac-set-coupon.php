<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if ( ! class_exists( 'WCGAC_Set_Coupon' ) ) :
class WCGAC_Set_Coupon {
	
	private static $settings = array();
	
	public function __construct() {	

		if ( empty( self::$settings ) ) {
			$settings = array();
			$settings['wgac_coupon_enabled'] = get_option('wgac_coupon_enabled');
			$settings['discount_percentage'] = get_option('wgac_discount_percentage') ? get_option('wgac_discount_percentage') : 5;
			$settings['expiration_date'] = get_option('wgac_expiration_date') ? get_option('wgac_expiration_date') : 7;
			$settings['coupon_description'] = get_option('wgac_coupon_description') ? get_option('wgac_coupon_description') : 'Discount coupon to be used within 7 days';
			$settings['subject_email'] = get_option('wgac_subject_email') ? get_option('wgac_subject_email') : 'A gift for you';
			$settings['content_email'] = get_option('wgac_content_email') ? get_option('wgac_content_email') : '[couponcode]';
			$settings['email_header_image'] = get_option('woocommerce_email_header_image');
			$settings['email_footer'] = get_option('woocommerce_email_footer_text');
			$settings['email_base_color'] = get_option('woocommerce_email_base_color');
			
			self::$settings = $settings;
		}
				
	}
	
	public static function check_coupon_enabled() {
		if(self::$settings['wgac_coupon_enabled'] == 'yes'){
			return true;
		}else{
			return false;	
		}
	}	
	

	public static function add_coupon_to_myaccount(){
		if(empty($current_user)){
			$current_user = wp_get_current_user();		
			$args = array(
				'posts_per_page'   => -1,
				'orderby'          => 'title',
				'order'            => 'asc',
				'post_type'        => 'shop_coupon',
				'post_status'      => 'publish',
			);
			$coupons = get_posts( $args );
			foreach( $coupons as $coupon ){	
				$_coupon = new WC_Coupon(wc_get_coupon_code_by_id($coupon->ID));
				foreach( $_coupon->customer_email as $email ){	
					if( $email ==  $current_user->user_email){
						print (
						"<div style='display: inline-block; background: #f9f9f9; border: 1px dashed #999999; padding: 20px 50px;'>
						<h3 style='text-transform: uppercase;'>".$_coupon->code. "</h3>"
						."<p>".$coupon->post_excerpt."</p>"
						."<p>". __( 'Expiry Date: ', 'give-automatic-coupon').$_coupon->expiry_date."</p>"
						."</div>"
						);
					}
				}
			}	
		}
	}

		public static function create_coupon( $order_id, $old_status, $new_status ) {
		if( $new_status == "completed" ) {			
			$order = wc_get_order( $order_id );
			$_couponName = "wgac-".(time());
			$_customer_email= $order->get_billing_email();
			$_newCouponID = 0;
			$isCreatedBefore = false;
			$args = array(
				'posts_per_page'   => -1,
				'orderby'          => 'title',
				'order'            => 'asc',
				'post_type'        => 'shop_coupon',
				'post_status'      => 'publish',
			);
			$coupons = get_posts( $args );
			foreach( $coupons as $coupon ){
				$_coupon = wc_get_coupon_code_by_id($coupon->ID);
				
				if($_coupon == $_couponName){
					$isCreatedBefore = true;
					$_newCouponID = $coupon->ID;
				}
			}	
			$expiration = self::$settings['expiration_date']; // Here you must indicate the validity of the coupon in days
			$date = date( 'Y-m-d' );
			$expiry_date = strtotime( $date .'+ '. $expiration .' days' );
			$expiry_date = date( 'Y-m-d', $expiry_date );		
			if(!$isCreatedBefore){
				$_couponAmount = self::$settings['discount_percentage']; //discount value
				$_couponType = "percent"; 
				//create new coupon
				$_newCoupon = array(
					'post_title' => $_couponName,
					'post_content' => '',
					'post_excerpt' => self::$settings['coupon_description'], 
					'post_status' => 'publish',
					'post_author' => 1,
					'post_type'     => 'shop_coupon',			
					);
				$_newCouponID = wp_insert_post( $_newCoupon );
				update_post_meta( $_newCouponID, 'discount_type', $_couponType );
				//update_post_meta( $_newCouponID, 'free_shipping', 'no' );	
				update_post_meta( $_newCouponID, 'usage_limit', '1' );			
				update_post_meta( $_newCouponID, 'coupon_amount', $_couponAmount );
				update_post_meta( $_newCouponID, 'usage_limit_per_user', '1' );
				update_post_meta( $_newCouponID, 'individual_use', 'yes' );
				update_post_meta( $_newCouponID, 'customer_email', $_customer_email );
				update_post_meta( $_newCouponID, 'expiry_date', $expiry_date );
				wp_update_post($_newCouponID);				
				self::send_email($_customer_email, self::content_email($_couponName));
			}		
		}
	}
	
	public static function print_coupon($_couponName){
		return "<div style='display: inline-block; background: #f9f9f9; border: 1px dashed #999999; padding: 20px 50px;'>
		<strong>".$_couponName."</strong></div>";	
	}
	
	

	public static function content_email($_couponName){
		$coupon_Code = self::print_coupon($_couponName);
		$content_mail = str_replace('[couponcode]', $coupon_Code ,self::$settings['content_email']);
		return '
		<p style="font-size: 16px !important;">
			'.$content_mail.'
		</p>
		';	
	}	
	
	public static function send_email($to, $coupon){
		$subject = self::$settings['subject_email'];
		$email_header_image = '<img src="'.self::$settings['email_header_image'].'" />';
		$footer_mail = '<hr><br>'.self::$settings['email_footer'];
		$body = $email_header_image.'<br><br>'.$coupon.'<br><br>'.$footer_mail;
		$headers = array('Content-Type: text/html; charset=UTF-8');		 
		wp_mail( $to, $subject, $body, $headers );		
	}
	
}

return new WCGAC_Set_Coupon();

endif;
?>
