<?php
/*
Plugin Name: PMPro Customizations
Plugin URI: https://www.paidmembershipspro.com/wp/pmpro-customizations/
Description: Customizations for my Paid Memberships Pro Setup
Version: .1
Author: Paid Memberships Pro
Author URI: https://www.paidmembershipspro.com
*/
 
//Now start placing your customization code below this line

global $pmprorh_options;
//$pmprorh_options["register_redirect_url"] = home_url("/tools/rq/");
$pmprorh_options["use_email_for_login"] = true;
$pmprorh_options["directory_page"] = "/directory/";
$pmprorh_options["profile_page"] = "/profile/";

function show_user_fields()
{?> <script>
	jQuery(document).ready(function() {
		jQuery('#pmpro_user_fields').show();
		jQuery('#pmpro_user_fields_show').hide();
	}
		   
	);</script><?php 
}
add_action('pmpro_checkout_before_submit_button', 'show_user_fields');

// Prevent file from being loaded directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Extra Theme
 *
 * functions.php
 *
 * Load & setup theme files/functions
 */

define( 'EXTRA_LAYOUT_POST_TYPE', 'layout' );
define( 'EXTRA_PROJECT_POST_TYPE', 'project' );
define( 'EXTRA_PROJECT_CATEGORY_TAX', 'project_category' );
define( 'EXTRA_PROJECT_TAG_TAX', 'project_tag' );
define( 'EXTRA_RATING_COMMENT_TYPE', 'rating' );

$et_template_directory = get_template_directory();

// Load Framework
require $et_template_directory . '/framework/functions.php';

// Load theme core functions
require $et_template_directory . '/includes/core.php';
require $et_template_directory . '/includes/plugins-woocommerce-support.php';
require $et_template_directory . '/includes/plugins-seo-support.php';
require $et_template_directory . '/includes/activation.php';
require $et_template_directory . '/includes/customizer.php';
require $et_template_directory . '/includes/builder-integrations.php';
require $et_template_directory . '/includes/layouts.php';
require $et_template_directory . '/includes/template-tags.php';
require $et_template_directory . '/includes/ratings.php';
require $et_template_directory . '/includes/projects.php';
require $et_template_directory . '/includes/widgets.php';
require $et_template_directory . '/includes/et-social-share.php';

// Load admin only resources
if ( is_admin() ) {
	require $et_template_directory . '/includes/admin/admin.php';
	require $et_template_directory . '/includes/admin/category.php';
}

// removes the `profile.php` admin color scheme options
// remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );

function remove_personal_options(){
    echo '<script type="text/javascript">jQuery(document).ready(function($) {
  
$(\'form#your-profile > h2:first\').remove(); // remove the "Personal Options" title
  
$(\'form#your-profile tr.user-rich-editing-wrap\').remove(); // remove the "Visual Editor" field
  
$(\'form#your-profile tr.user-admin-color-wrap\').remove(); // remove the "Admin Color Scheme" field
  
$(\'form#your-profile tr.user-comment-shortcuts-wrap\').remove(); // remove the "Keyboard Shortcuts" field
  
// $(\'form#your-profile tr.user-admin-bar-front-wrap\').remove(); // remove the "Toolbar" field
  
$(\'form#your-profile tr.user-language-wrap\').remove(); // remove the "Language" field
  
$(\'form#your-profile tr.user-first-name-wrap\').remove(); // remove the "First Name" field
  
$(\'form#your-profile tr.user-last-name-wrap\').remove(); // remove the "Last Name" field
  
$(\'form#your-profile tr.user-nickname-wrap\').hide(); // Hide the "nickname" field
  
// $(\'table.form-table tr.user-display-name-wrap\').remove(); // remove the “Display name publicly as” field
  
$(\'table.form-table tr.user-url-wrap\').remove();// remove the "Website" field in the "Contact Info" section
  
// $(\'h2:contains("About Yourself"), h2:contains("About the user")\').remove(); // remove the "About Yourself" and "About the user" titles
  
// $(\'form#your-profile tr.user-description-wrap\').remove(); // remove the "Biographical Info" field
  
// $(\'form#your-profile tr.user-profile-picture\').remove(); // remove the "Profile Picture" field
 
});</script>';
  
}
  
add_action('admin_head','remove_personal_options');



/*
	When users cancel (are changed to membership level 0) we give them another "cancelled" level. Can be used to downgrade someone to a free level when they cancel.
*/
function pmpro_after_change_membership_level_default_level($level_id, $user_id)
{
	//if we see this global set, then another gist is planning to give the user their level back
	global $pmpro_next_payment_timestamp;
	if(!empty($pmpro_next_payment_timestamp))
		return;
	
	if($level_id == 0) {
		//cancelling, give them level 4 instead
		pmpro_changeMembershipLevel(4, $user_id);
	}
}
add_action("pmpro_after_change_membership_level", "pmpro_after_change_membership_level_default_level", 10, 2);

function hide_discount_code_field_for_free_levels($show)
{
  global $pmpro_level;
  
  if(function_exists('pmpro_isLevelFree') && pmpro_isLevelFree($pmpro_level))
    $show = false;

  return $show;
}
add_filter('pmpro_show_discount_code', 'hide_discount_code_field_for_free_levels');

/*
  Show Members Reports on the WordPress Admin Dashboard.
  Update the my_pmpro_dashboard_report() function to remove or add core or custom reports.
*/
//Create a Dashboard Reports widget for Paid Memberships Pro
function add_my_report_dashboard() {
	if( ! defined( 'PMPRO_DIR' )  || ! current_user_can( 'manage_options' ) )
	{
		return;
	}
	wp_add_dashboard_widget(
		'pmpro_membership_dashboard',
		__( 'Paid Membership Pro Reports' , 'pmpro' ),
		'my_pmpro_dashboard_report'
	);
}
add_action( 'wp_dashboard_setup', 'add_my_report_dashboard' );
//Callback function for the widget
function my_pmpro_dashboard_report() {
	//included report pages
	require_once( PMPRO_DIR . '/adminpages/reports/login.php' );
	require_once( PMPRO_DIR . '/adminpages/reports/memberships.php' );
	require_once( PMPRO_DIR . '/adminpages/reports/sales.php' );
	//show Visits/Views/Logins report
	echo '<h3>' . __( 'Visit, Views and Logins', 'pmpro' ) . '</h3>';
	pmpro_report_login_widget();
	//show Membership report
	echo '<br /><h3>' . __( 'Membership Stats', 'pmpro' ) . '</h3>';
	pmpro_report_memberships_widget();
	//show Sales and Revenue report
	echo '<br /><h3>' . __( 'Sales and Revenue', 'pmpro' ) . '</h3>';
	pmpro_report_sales_widget();
	//show link to all PMPro reports
	echo '<p style="text-align: center;"><a class="button-primary" href="' . admin_url( 'admin.php?page=pmpro-reports' ) . '">' . __( 'View All Reports', 'pmpro' ) . '</a></p>';
}

//Add the new "Refund Rate" report widget and page
global $pmpro_reports;
$pmpro_reports['refunds'] = __('Refund Rate', 'pmpro-reports-refunds');
global $wpdb;
//$refundsThisMonth = "SELECT COUNT(*) FROM wp_pmpro_membership_orders WHERE membership_id IN(6,20) AND total > 0 AND timestamp > '2016-01-01' AND status = 'refunded' ";
//$refundRate = $refunds / $sales;
//Show the report widget on the Memberships > Reports dashboard
function pmpro_report_refunds_widget()
{	
	global $wpdb;
	?>
	<table class="wp-list-table widefat fixed striped">
	<thead>
		<tr>
			<th scope="col">&nbsp;</th>
			<th scope="col"><?php _e('Sales', 'pmpro'); ?></th>
			<th scope="col"><?php _e('Refunds', 'pmpro'); ?></th>
			<th scope="col"><?php _e('Refund Rate', 'pmpro'); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th><?php _e('This Month', 'pmpro'); ?></th>
			<td><?php echo number_format_i18n(pmpro_getSales("this month")); ?></td>
			<td><?php echo number_format_i18n(pmpro_getRefunds("this month")); ?></td>
			<td>
			<?php 
				if(pmpro_getSales('this month') > 0)
					echo sprintf("%.2f%%", (pmpro_getRefunds("this month") / pmpro_getSales("this month")) * 100); 
				else
					echo __('N/A', 'pmpro');
			?>
			</td>
		</tr>
		<tr>
			<th><?php _e('This Year', 'pmpro'); ?></th>
			<td><?php echo number_format_i18n(pmpro_getSales("this year")); ?></td>
			<td><?php echo number_format_i18n(pmpro_getRefunds("this year")); ?></td>
			<td>
			<?php 
				if(pmpro_getSales('this year') > 0)
					echo sprintf("%.2f%%", (pmpro_getRefunds("this year") / pmpro_getSales("this year")) * 100);
				else
					echo __('N/A', 'pmpro');
			?>	
			</td>
		</tr>
			<th><?php _e('All Time', 'pmpro'); ?></th>
			<td><?php echo number_format_i18n(pmpro_getSales("all time")); ?></td>
			<td><?php echo number_format_i18n(pmpro_getRefunds("all time")); ?></td>
			<td>
			<?php 
				if(pmpro_getSales('all time') > 0)
					echo sprintf("%.2f%%", (pmpro_getRefunds("all time") / pmpro_getSales("all time")) * 100);
				else
					echo __('N/A', 'pmpro');
			?>
			</td>
		</tr>
	</tbody>
	</table>
	<?php
}
//Show the report on the Memberships > Reports > Refund Rate page
function pmpro_report_refunds_page()
{
	global $wpdb;
	?>
	<div class="metabox-holder">
		<h1><?php _e('Refund Rate', 'pmpro'); ?></h1>
		<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col">&nbsp;</th>
				<th scope="col"><?php _e('Sales', 'pmpro'); ?></th>
				<th scope="col"><?php _e('Refunds', 'pmpro'); ?></th>
				<th scope="col"><?php _e('Refund Rate', 'pmpro'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th><?php _e('This Month', 'pmpro'); ?></th>
				<td><?php echo number_format_i18n(pmpro_getSales("this month")); ?></td>
				<td><?php echo number_format_i18n(pmpro_getRefunds("this month")); ?></td>
				<td><?php echo sprintf("%.2f%%", (pmpro_getRefunds("this month") / pmpro_getSales("this month")) * 100); ?></td>
			</tr>
			<tr>
				<th><?php _e('This Year', 'pmpro'); ?></th>
				<td><?php echo number_format_i18n(pmpro_getSales("this year")); ?></td>
				<td><?php echo number_format_i18n(pmpro_getRefunds("this year")); ?></td>
				<td><?php echo sprintf("%.2f%%", (pmpro_getRefunds("this year") / pmpro_getSales("this year")) * 100); ?></td>
			</tr>
				<th><?php _e('All Time', 'pmpro'); ?></th>
				<td><?php echo number_format_i18n(pmpro_getSales("all time")); ?></td>
				<td><?php echo number_format_i18n(pmpro_getRefunds("all time")); ?></td>
				<td><?php echo sprintf("%.2f%%", (pmpro_getRefunds("all time") / pmpro_getSales("all time")) * 100); ?></td>
			</tr>
		</tbody>
		</table>	
	</div>
	<?php
}
//get refunds
if(!function_exists('pmpro_getRefunds')) {
	function pmpro_getRefunds($period, $levels = NULL)
	{
		//check for a transient
		$cache = get_transient("pmpro_report_refunds");
		if(!empty($cache) && !empty($cache[$period]) && !empty($cache[$period][$levels]))
			return $cache[$period][$levels];
			
		//a refund is an order with status = 'refunded' with a total > 0
		if($period == "today")
			$startdate = date("Y-m-d", current_time('timestamp'));
		elseif($period == "this month")
			$startdate = date("Y-m", current_time('timestamp')) . "-01";
		elseif($period == "this year")
			$startdate = date("Y", current_time('timestamp')) . "-01-01";
		else
			$startdate = "";
		
		$gateway_environment = pmpro_getOption("gateway_environment");
		
		//build query
		global $wpdb;
		$sqlQuery = "SELECT COUNT(*) FROM $wpdb->pmpro_membership_orders WHERE total > 0 AND status = 'refunded' AND timestamp >= '" . $startdate . "' AND gateway_environment = '" . esc_sql($gateway_environment) . "' ";
		
		//restrict by level
		if(!empty($levels))
			$sqlQuery .= "AND membership_id IN(" . $levels . ") ";
		
		$refunds = $wpdb->get_var($sqlQuery);
		
		//save in cache
		if(!empty($cache) && !empty($cache[$period]))
			$cache[$period][$levels] = $refunds;
		elseif(!empty($cache))
			$cache[$period] = array($levels => $refunds);
		else
			$cache = array($period => array($levels => $refunds));
		
		set_transient("pmpro_report_refunds", $cache, 3600*24);
		
		return $refunds;
	}
}
//delete transients when an order is updated
function pmpro_report_refunds_delete_transient()
{
	delete_transient("pmpro_report_refunds");
}
add_action("pmpro_updated_order", "pmpro_report_refunds_delete_transient");


//HasPaid Shortcode
/*
	Function and shortcode to detect users with or without paid invoices.
	
	Add this code to a custom plugin.
*/
//check if a user ever paid
function my_hasPaid($user_id = NULL, $level_id = NULL) {
	global $current_user, $wpdb;
	
	//make sure PMPro is active
	if(!isset($wpdb->pmpro_membership_orders))
		return false;
	
	//no user passed? default to current user
	if(empty($user_id))
		$user_id = $current_user->ID;
	
	//no user?
	if(empty($user_id))
		return false;
	
	//figure out if we are in a live or test gateway_environment
	$environment = pmpro_getOption('gateway_environment');
	
	//query to check
	$sqlQuery = "SELECT COUNT(*) FROM $wpdb->pmpro_membership_orders WHERE user_id = '" . esc_sql($user_id) . "' AND gateway_environment = '" . esc_sql($environment) . "' AND total > 0 AND status NOT IN('error', 'refund', 'token', 'review') ";
	if(!empty($level_id))
		$sqlQuery .= "AND membership_id = '" . esc_sql($level_id) . "' LIMIT 1";
		
	//get val
	$paid = $wpdb->get_var($sqlQuery);
	
	//force true/false
	return (bool)$paid;
}

/*
	Shortcode using the my_hasPaid function.
	[haspaid]This will show up if the user has paid for any level.[/haspaid]
	[haspaid paid='0']This will show up if the user has NOT paid for any level.[/haspaid]
	[haspaid paid='1' level='1']This will show up if the user has paid for level 1 specifically.[/haspaid]
	[haspaid paid='0' level='1']This will show up if the user has not paid for level 1 specifically.[/haspaid]
*/
function my_haspaid_shortcode($atts, $content=null, $code="")
{
	// $atts    ::= array of attributes
	// $content ::= text within enclosing form of shortcode element
	// $code    ::= the shortcode found, when == callback name
	// examples: [haspaid level="3"]...[/haspaid]
	extract(shortcode_atts(array(
		'paid' => true,
		'level' => NULL,
	), $atts));
		
	//convert paid attribute to bool
	if($paid === '0' || $paid === 'false')
		$paid = false;
	else
		$paid = true;
	
	global $current_user;
	
	//to show or not to show
	if(my_hasPaid($current_user->ID, $level)) {
		//return content if paid
		if($paid)
			return do_shortcode($content);	//show content
		else
			return false;
	} else {
		//return content if NOT paid
		if(!$paid)
			return do_shortcode($content);	//show content
		else
			return "";	//just hide it
	}
}
add_shortcode("haspaid", "my_haspaid_shortcode");



?>
