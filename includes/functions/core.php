<?php
namespace EAMann\Unmask\Core;

use EAMann\Unmask\Logger;
use Expose;

/**
 * Default setup routine
 *
 * @uses add_action()
 * @uses do_action()
 *
 * @return void
 */
function setup() {
	$n = function( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'init',                         $n( 'i18n' )                      );
	add_action( 'init',                         $n( 'init' )                      );
	add_action( 'unmask_init',                  $n( 'register_post_type' )        );
	add_action( 'plugins_loaded',               $n( 'scan_request' )              );
	add_action( 'unmask_expose_request_impact', $n( 'maybe_email_report' ), 10, 2 );

	do_action( 'unmask_loaded' );
}

/**
 * Registers the default textdomain.
 *
 * @uses apply_filters()
 * @uses get_locale()
 * @uses load_textdomain()
 * @uses load_plugin_textdomain()
 * @uses plugin_basename()
 *
 * @return void
 */
function i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'unmask' );
	load_textdomain( 'unmask', WP_LANG_DIR . '/unmask/unmask-' . $locale . '.mo' );
	load_plugin_textdomain( 'unmask', false, plugin_basename( UNMASK_PATH ) . '/languages/' );
}

/**
 * Initializes the plugin and fires an action other plugins can hook into.
 *
 * @uses do_action()
 *
 * @return void
 */
function init() {
	do_action( 'unmask_init' );
}

/**
 * Activate the plugin
 *
 * @uses init()
 * @uses flush_rewrite_rules()
 *
 * @return void
 */
function activate() {
	// First load the init scripts in case any rewrite functionality is being loaded
	init();
	flush_rewrite_rules();
}

/**
 * Deactivate the plugin
 *
 * Uninstall routines should be in uninstall.php
 *
 * @return void
 */
function deactivate() {

}

/**
 * Register the Unmask Log post type.
 */
function register_post_type() {
	$labels = array(
		'name'               => __( 'Unmask Logs', 'unmask' ),
		'singular_name'      => __( 'Unmask Log', 'unmask' ),
		// add_new, add_new_item, edit_item, new_item, view_item
		'search_items'       => __( 'Search Unmask Logs', 'unmask' ),
		'not_found'          => __( 'No Unmask logs, your site looks great!', 'unmask' ),
		'not_found_in_trash' => __( 'No logs in trash.', 'unmask' ),
	);

	$args = array(
		'labels'       => $labels,
		'show_in_menu' => 'tools.php',
		'show_ui'      => true,
		'public'       => false,
		'capabilities' => array(
			'edit_post'          => 'activate_plugins',
			'edit_posts'         => 'activate_plugins',
			'edit_others_posts'  => 'activate_plugins',
			'publish_posts'      => 'do_not_allow',
			'read_post'          => 'activate_plugins',
			'read_private_posts' => 'do_not_allow',
			'delete_post'        => 'activate_plugins',
			'create_posts'       => 'do_not_allow',
		),
		'rewrite'      => false,
		'query_var'    => false,
	);

	\register_post_type( 'unmask_log', $args );
}

/**
 * Scan the request data and populate an array for Expose.
 *
 * @return array
 */
function get_request_data() {
	$data = array(
		'REQUEST' => $_REQUEST,
	);

	/**
	 * Filter the parameters passed by Unmask to Expose to either add or remove data for the scan.
	 *
	 * @param array $data Associative array of data to be scanned.
	 */
	return apply_filters( 'unmask_expose_request_data', $data );
}

/**
 * Fire up the Expose engine and actually scan the request.
 */
function scan_request() {
	$data = get_request_data();

	// Load up our filters
	$filters = new Expose\FilterCollection();
	$filters->load();

	// Get an instance of our logger
	$logger = new Logger();

	$manager = new Expose\Manager( $filters, $logger );
	$manager->run( $data );

	/**
	 * Dispatch any events tied to a specific impact level for the current request.
	 *
	 * @param int            $impact  Impact score
	 * @param Expose\Manager $manager Actual manager instance
	 */
	do_action( 'unmask_expose_request_impact', $manager->getImpact(), $manager );
}

/**
 * Depending on user settings, maybe send an email to the admin.
 *
 * @param int            $impact
 * @param Expose\Manager $manager
 */
function maybe_email_report( $impact, $manager ) {
	/**
	 * Filter the minimum threshold after which the site admin is notified of the issue.
	 *
	 * @param int $threshold Minimum notification threshold (12).
	 */
	$minimum_impact = apply_filters( 'unmask_expose_impact_notify_threshold', 12 );

	// If the impact isn't above our threshold, return
	if ( $impact < $minimum_impact ) {
		return;
	}

	/**
	 * Email address to which we're sending the notifications when an impact is above the speciifed threshold.
	 *
	 * @param string $to_email Administrator responsible for Unmask data (site admin by default)
	 */
	$to = apply_filters( 'unmask_expose_notify_recipient', get_site_option( 'admin_email' ) );

	/**
	 * Email subject line to use when sending notifications.
	 *
	 * @param string $subject Subject line to use
	 */
	$subject = apply_filters( 'unmask_expose_notify_subject', __( 'Unmask Site Alert', 'unmask' ) );

	/**
	 * Filter the default from name for Unmask email notifications.
	 *
	 * @param string $from_name Default sender name
	 */
	$from_name = apply_filters( 'unmask_Expose_notify_from_name', __( 'WordPress Unmask', 'unmask' ) );

	/**
	 * Filter the default from email for Unmask email notifications.
	 *
	 * @param string $from_email Default sender email
	 */
	$from_email = apply_filters( 'unmask_expose_notify_from_email', 'unmask@' . get_option( 'siteurl' ) );

	// Build out the email headers
	$headers = array( sprintf( 'From: "%s" <%s>', $from_name, $from_email ) );

	// Actually send the message
	wp_mail( $to, $subject, $manager->export(), $headers );

	/**
	 * Event to signal that an email notification has been sent
	 */
	do_action( 'umask_expose_impact_notify_email_sent' );
}