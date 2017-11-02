<?php


/*
 * Plugin Name: WooCommerce Dynamic Pricing User Specific Conditions
 * Plugin URI: https://woocommerce.com/products/dynamic-pricing/
 * Description: Adds user specific options to Advanced Product Rules.
 * Version: 1.0.0
 * Author: Lucas Stark
 * Author URI: http://lucasstark.com
 * Requires at least: 3.3
 * Tested up to: 4.8.3
 * Text Domain: woocommerce-dynamic-pricing
 * Domain Path: /i18n/languages/
 * Copyright: © 2009-2017 Lucas Stark.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * WC requires at least: 3.0.0
 * WC tested up to: 3.2.2
 */


class WC_Dynamic_Pricing_User_Specific_Conditions {

	private static $instance;

	public static function register() {
		if ( self::$instance == null ) {
			self::$instance = new WC_Dynamic_Pricing_User_Specific_Conditions();
		}
	}


	public function __construct() {
		add_action( 'woocommerce_dynamic_pricing_applies_to_options', array(
			$this,
			'add_user_specific_option'
		), 10, 4 );


		add_action( 'woocommerce_dynamic_pricing_applies_to_selectors', array(
			$this,
			'woocommerce_dynamic_pricing_applies_to_selectors'
		), 10, 4 );

		add_action( 'woocommerce_dynamic_pricing_metabox_js', array( $this, 'add_user_specific_js' ) );


		add_filter( 'woocommerce_dynamic_pricing_is_rule_set_valid_for_user', array(
			$this,
			'check_ruleset_against_users'
		), 10, 3 );

	}

	/** Front End Specific Functions */

	public function check_ruleset_against_users( $is_valid, $condition, $adjustment_set ) {

		if ( isset( $condition['type'] ) && $condition['type'] == 'apply_to' ) {

			if ( $condition['args']['applies_to'] == 'users' && isset( $condition['args']['users'] ) && !empty( $condition['args']['users'] ) ) {

				$current_user = wp_get_current_user();
				$users = explode(',', $condition['args']['users']);
				$users = array_map('trim', $users);

				$is_valid = in_array($current_user->user_login, $users);

			}

		}

		return $is_valid;
	}


	/** Admin Specific Functions */

	public function add_user_specific_option( $rule_type, $condition, $name, $condition_index ) {
		if ( $rule_type == 'advanced_product' ) {
			?>
			<option <?php selected( 'users', $condition['args']['applies_to'] ); ?> value="users"><?php _e( 'Specific Users', 'woocommerce-dynamic-pricing' ); ?></option>
			<?php
		}
	}

	public function woocommerce_dynamic_pricing_applies_to_selectors( $rule_type, $condition, $name, $condition_index ) {
		if ( $rule_type == 'advanced_product' ) {

			$div_style = ( $condition['args']['applies_to'] != 'users' ) ? 'display:none;' : '';

			?>
			<div class="user-selector" style="margin-top:5px;<?php echo $div_style; ?>">
				<label for="pricing_rules[<?php echo $name; ?>][conditions][<?php echo $condition_index; ?>][args][users]"><?php _e( 'Users:', 'woocommerce-dynamic-pricing' ); ?></label>
				<input type="text" name="pricing_rules[<?php echo $name; ?>][conditions][<?php echo $condition_index; ?>][args][users]" value="<?php echo isset( $condition['args']['users'] ) ? esc_attr( $condition['args']['users'] ) : '' ?>"/>
				<p class="description"><?php _e('Enter the users login name, seperate multiple users with a comma', 'woocommerce-dynamic-pricing') ?></p>
			</div>
			<?php
		}
	}

	public function add_user_specific_js() {

		?>
		$('#woocommerce-pricing-rules-wrap').delegate('.pricing_rule_apply_to', 'change', function (event) {
		var value = $(this).val();
		if (value != 'users' && $('.user-selector', $(this).parent()).is(':visible')) {
		$('.user-selector', $(this).parent()).fadeOut();
		}

		if (value == 'users') {
		$('.user-selector', $(this).parent()).fadeIn();
		}
		});

		<?php

	}


}

WC_Dynamic_Pricing_User_Specific_Conditions::register();