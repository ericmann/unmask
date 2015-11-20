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

	add_action( 'init',           $n( 'i18n' ) );
	add_action( 'init',           $n( 'init' ) );
	add_action( 'unmask_init',    $n( 'register_post_type' ) );
	add_action( 'plugins_loaded', $n( 'scan_request' ) );

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
		'SERVER'  => $_SERVER,
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
	 * @param int $impact Impact score
	 */
	do_action( 'unmask_expose_request_impact', $manager->getImpact() );
}