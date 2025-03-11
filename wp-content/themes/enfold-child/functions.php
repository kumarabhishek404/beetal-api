<?php

/*
 * Add your own functions here. You can also copy some of the theme functions into this file.
 * Wordpress will use those functions instead of the original functions then.
 */

function enfoldchild_theme_enqueue_styles() {

	$parent_style = 'parent-style';
	wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
	wp_enqueue_style('child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array($parent_style)
	);
}
add_action('wp_enqueue_scripts', 'enfoldchild_theme_enqueue_styles');

add_action('init', 'wpas_user_custom_fields');
function wpas_user_custom_fields() {
	global $wpdb;
	if (function_exists('wpas_add_custom_field')) {
		$get_current_user_id = get_current_user_id();
		$customer_orders = get_posts(array(
			'meta_key' => '_customer_user',
			'meta_value' => $get_current_user_id,
			'post_type' => 'shop_order',
			'post_status' => array_keys(wc_get_order_statuses()),
			'numberposts' => -1,
		));
		foreach ($customer_orders as $key => $orderItem) {
			//$userOrder[$orderItem->ID] = '#' . $orderItem->ID;
			$order = wc_get_order($orderItem->ID);
			$items = $order->get_items();
			$product_name = array();
			foreach ($items as $item) {
				$product_name[$item->get_name()] = $item->get_name();
			}
		}

		//wpas_add_custom_field($body_args['name'], $body_args['args']);
		//Plugins
		$PluginsValues = array('0' => 'Select');
		$body_args = array(
			'name' => 'orders-items',
			'args' => array(
				'required' => true,
				'field_type' => 'select',
				'label' => __('Order Items', 'awesome-support'),
				'options' => $product_name,
				'order' => '0',
				'log' => true,
				'pre_render_action_hook_fe' => 'wpas_submission_form_inside_before_description',
				'post_render_action_hook_fe' => 'wpas_submission_form_inside_after_description',
			),
		);
		wpas_add_custom_field($body_args['name'], $body_args['args']);
	}
}
//Expire support plan
add_filter('wpas_can_submit_ticket', 'wpas_can_submit_tickets_has_subscription');
function wpas_can_submit_tickets_has_subscription() {
	$get_current_user_id = get_current_user_id();
	$subscriptions = wcs_get_subscriptions(
		array(
			'customer_id' => $get_current_user_id,
			'subscription_status' => 'wc-active',
			'subscriptions_per_page' => -1,
		)
	);
	if (count($subscriptions) > 0) {
		foreach ($subscriptions as $subscriptionss) {
			$status = $subscriptionss->get_status();
			if ($status == 'active') {
				$support = true;
			} else {
				$support = false;
			}
		}
	} else {
		$support = false;
	}

	return $support;
// Do your logics here
}
//Check reply
add_filter('wpas_can_also_reply_ticket', 'wpas_can_submit_reply_ticket');
function wpas_can_submit_reply_ticket() {

	$get_current_user_id = get_current_user_id();
	$subscriptions = wcs_get_subscriptions(
		array(
			'customer_id' => $get_current_user_id,
			'subscription_status' => 'wc-active',
			'subscriptions_per_page' => -1,
		)
	);
	if (count($subscriptions) > 0) {
		foreach ($subscriptions as $subscriptionss) {
			$status = $subscriptionss->get_status();
			if ($status == 'active') {

				$support = true;
			} else {

				$support = false;
			}
		}
	} else {
		$support = false;
	}
	return $support;
}
//Start my account page hook
add_filter('woocommerce_account_menu_items', 'hireexperts_remove_my_account_links');
function hireexperts_remove_my_account_links($menu_links) {

	unset($menu_links['edit-address']); // Addresses
	$menu_links['subscriptions'] = 'Support plan';
	$menu_links['submit-ticket'] = 'Create ticket';
	return $menu_links;

}
function userPlan() {
	$get_current_user_id = get_current_user_id();
	$subscriptions = wcs_get_subscriptions(
		array(
			'customer_id' => $get_current_user_id,
			'subscription_status' => 'wc-active',
			'subscriptions_per_page' => -1,
		)
	);
	$subscriptions = count($subscriptions);
	return $subscriptions;
}
function hireexperts_custom_endpoint_content_message() {
	$userPlan = userPlan();
	$html = '';
	$html = '<div class="woocommerce-message support">Your support has been expired. Please extend your support period here -<a href="/extend-support">Extend support plan</a></div>';
	$html .= '<p>Every customer is given free 1 month support afterwards Hire-Experts plugin(s) purchase. As soon as this period expires you would not be able to open tickets or leave messages. Support queries related to Hire-Experts plugins, installation and upgrade of our plugins.</p><p>Please note, it does not cover customization requests or upgrade of customly made modifications.</p>';
	$html .= do_shortcode('[products limit="3" columns="3" category="support-plan"]');
	if ($userPlan == 0) {
		echo $html;
	}
}
add_action('woocommerce_account_subscriptions_endpoint', 'hireexperts_custom_endpoint_content_message');

add_filter('wpas_notification_wrapper', 'enfold_wpas_notification_wrapper');
function enfold_wpas_notification_wrapper() {
	$userPlan = userPlan();
	$html = '<div class="wpas-alert wpas-alert-danger">You are not allowed to submit a tickets because your support has been expired. Please extend your support period here or <a class="woocommerce-Button enfold" href="' . get_permalink(get_option('woocommerce_myaccount_page_id')) . '">Dashboard</a>.</div>';
	$html .= do_shortcode('[products limit="3" columns="3" category="support-plan"]');
	if ($userPlan == 0) {
		echo $html;
	} else {
		echo '<p>You have not submitted a ticket yet. <a href="' . wpas_get_submission_page_url() . '">Click here to submit your first ticket</a></p>';
	}

}
function enfold_get_posts_query($q) {

	$tax_query = (array) $q->get('tax_query');

	$tax_query[] = array(
		'taxonomy' => 'product_cat',
		'field' => 'slug',
		'terms' => array('packages', 'support-plan', 'free-support-plan', 'gold-plan'), // Don't display products in the clothing category on the shop page.
		'operator' => 'NOT IN',
	);

	$q->set('tax_query', $tax_query);

}
add_action('woocommerce_product_query', 'enfold_get_posts_query');
// add_action('woocommerce_checkout_order_processed', 'enfold_free_support_plan', 10, 1);
// function enfold_free_support_plan($order_id) {
// 	$order = wc_get_order($order_id);
// 	$order->add_product(get_product('33893'), 1);
// }
function enfold_add_free_product_to_cart() {
	$free_product_id = 33893;
	$supportarray = array('33961','33865','33863');
	$cartId = WC()->cart->generate_cart_id($free_product_id);
	$cartItemKey = WC()->cart->find_product_in_cart($cartId);
	$found = false;
	global $woocommerce;
	$carttotal = $woocommerce->cart->cart_contents_total;
	if (sizeof(WC()->cart->get_cart()) > 0) {
		foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
			$_product = $values['data'];
			if ($_product->get_id() == $free_product_id) {
				$found = true;
			}
			if(in_array($_product->get_id(), $supportarray)){
				$found = true;
			}
		}
		if (!$found && is_checkout() && $carttotal > 0) {
			add_action('woocommerce_before_checkout_form', 'enfold_add_free_notice', 10);
			WC()->cart->add_to_cart($free_product_id);
		} else {
			WC()->cart->remove_cart_item($cartItemKey);
		}
	} else {
		if (function_exists('is_checkout') && is_checkout() && $carttotal > 0) {
			WC()->cart->add_to_cart($free_product_id);
		} else {
			WC()->cart->remove_cart_item($cartItemKey);
		}
	}
}
add_action('template_redirect', 'enfold_add_free_product_to_cart', 10, 2);

//add_action('woocommerce_before_checkout_form', 'enfold_add_free_notice', 10);
function enfold_add_free_notice() {
	global $woocommerce;
	$carttotal = $woocommerce->cart->cart_contents_total;
	if ($carttotal > 0) {
		wc_print_notice(sprintf(
			__(__("<strong>Congratulation:</strong> You can get one month free plugin support if you can complete this order.", "woocommerce"))
		), 'success');
	}
}

add_action('woocommerce_single_product_summary', 'enfold_remove_product_description_add_cart_button', 1);
function enfold_remove_product_description_add_cart_button() {
	$categories = array('free-support-plan', 'gold-plan');
	if (has_term($categories, 'product_cat', get_the_id())) {
		remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
	}
}
//Add custom field on my account page
add_action('woocommerce_edit_account_form', 'enfold_add_field_to_edit_account_form');
function enfold_add_field_to_edit_account_form() {
	$user = wp_get_current_user();
	$userMeta = get_user_meta($user->ID);
	?>
	<fieldset>
		<legend>SE Admin details</legend>
	    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
	        <textarea name="se_admin_details" class="woocommerce-Input woocommerce-Input--text input-text"><?php
echo $userMeta['se_admin_details'][0] ? $userMeta['se_admin_details'][0] : ''; ?></textarea>
	    </p>
	</fieldset>
	<fieldset>
		<legend>FTP / SFTP</legend>
	    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
	        <textarea name="ftp_sftp" class="woocommerce-Input woocommerce-Input--text input-text"><?php
echo $userMeta['ftp_sftp'][0] ? $userMeta['ftp_sftp'][0] : ''; ?></textarea>
	    </p>
	</fieldset>
	<fieldset>
		<legend>CPanel / PhpMyAdmin</legend>
	    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">

	        <textarea name="cpanel_phpmyadmin" class="woocommerce-Input woocommerce-Input--text input-text"><?php
echo $userMeta['cpanel_phpmyadmin'][0] ? $userMeta['cpanel_phpmyadmin'][0] : ''; ?></textarea>
	    </p>
	</fieldset>
    <?php
}

// Save the custom field 'favorite_color'
add_action('woocommerce_save_account_details', 'enfold_save_custom_account_details', 12, 1);
function enfold_save_custom_account_details($user_id) {
	if (isset($_POST['se_admin_details'])) {
		update_user_meta($user_id, 'se_admin_details', sanitize_textarea_field($_POST['se_admin_details']));
	}
	if (isset($_POST['ftp_sftp'])) {
		update_user_meta($user_id, 'ftp_sftp', sanitize_textarea_field($_POST['ftp_sftp']));
	}
	if (isset($_POST['cpanel_phpmyadmin'])) {
		update_user_meta($user_id, 'cpanel_phpmyadmin', sanitize_textarea_field($_POST['cpanel_phpmyadmin']));
	}

}

add_filter('woocommerce_product_tabs', 'enfold_remove_reviews_tab', 98);
function enfold_remove_reviews_tab($tabs) {
	$freeProduct = array('33893', '33961', '33865', '33863', '34091');
	global $product;
	// $id = $product->get_id();
	// if (in_array($id, $freeProduct)) {
	unset($tabs['reviews']);
	return $tabs;
	// }
}
