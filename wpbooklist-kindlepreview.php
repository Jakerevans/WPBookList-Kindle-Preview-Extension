<?php
/**
 * WordPress Book List Kindle Extension
 *
 * @package     WordPress Book List Kindle Extension
 * @author      Jake Evans
 * @copyright   2018 Jake Evans
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: WPBookList Kindle Extension
 * Plugin URI: https://www.jakerevans.com
 * Description: A WPBookList Extension that adds Kindle Previews to applicable Book Views, Pages, and Posts.
 * Version: 1.0.1
 * Author: Jake Evans
 * Text Domain: wpbooklist
 * Author URI: https://www.jakerevans.com
 */

/*
 * SETUP NOTES:
 *
 * Change all filename instances from kindle to desired plugin name
 *
 * Modify Plugin Name
 *
 * Modify Description
 *
 * Modify Version Number in Block comment and in Constant
 *
 * Find & Replace these 3 strings:
 * kindle
 * Kindle
 * KINDLE
 * ovbj with something also random - db column that holds license.
 *
 *
 * Change the EDD_SL_ITEM_ID_KINDLE contant below.
 *
 * Install Gulp & all Plugins listed in gulpfile.js
 *
 *
 *
 *
 */




// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

/* REQUIRE STATEMENTS */
	require_once 'includes/class-kindlepreview-general-functions.php';
	require_once 'includes/class-kindlepreview-ajax-functions.php';
	require_once 'includes/classes/update/class-wpbooklist-kindlepreview-update.php';
/* END REQUIRE STATEMENTS */

/* CONSTANT DEFINITIONS */

	if ( ! defined('WPBOOKLIST_VERSION_NUM' ) ) {
		define( 'WPBOOKLIST_VERSION_NUM', '6.1.6' );
	}

	// This is the URL our updater / license checker pings. This should be the URL of the site with EDD installed.
	define( 'EDD_SL_STORE_URL_KINDLE', 'https://wpbooklist.com' );

	// The id of your product in EDD.
	define( 'EDD_SL_ITEM_ID_KINDLE', 3475 );

	// This Extension's Version Number.
	define( 'WPBOOKLIST_KINDLE_VERSION_NUM', '1.0.1' );

	// Root plugin folder directory.
	define( 'KINDLE_ROOT_DIR', plugin_dir_path( __FILE__ ) );

	// Root WordPress Plugin Directory. The If is for taking into account the update process - a temp folder gets created when updating, which temporarily replaces the 'wpbooklist-bulkbookupload' folder.
	if ( false !== stripos( plugin_dir_path( __FILE__ ) , '/wpbooklist-kindlepreview' ) ) { 
		define( 'KINDLE_ROOT_WP_PLUGINS_DIR', str_replace( '/wpbooklist-kindlepreview', '', plugin_dir_path( __FILE__ ) ) );
	} else {
		$temp = explode( 'plugins/', plugin_dir_path( __FILE__ ) );
		define( 'KINDLE_ROOT_WP_PLUGINS_DIR', $temp[0] . 'plugins/' );
	}

	// Root WPBL Dir.
	if ( ! defined('KINDLE_ROOT_WPBL_DIR' ) ) {
		define( 'KINDLE_ROOT_WPBL_DIR', KINDLE_ROOT_WP_PLUGINS_DIR . 'wpbooklist/' );
	}

		// Root WPBL Dir.
	if ( ! defined( 'ROOT_WPBL_DIR' ) ) {
		define( 'ROOT_WPBL_DIR', KINDLE_ROOT_WP_PLUGINS_DIR . 'wpbooklist/' );
	}

	// Root WPBL Url.
	if ( ! defined('ROOT_WPBL_URL' ) ) {
		define( 'ROOT_WPBL_URL', plugins_url() . '/wpbooklist/' );
	}


	// Root WPBL Classes Dir.
	if ( ! defined('ROOT_WPBL_CLASSES_DIR' ) ) {
		define( 'ROOT_WPBL_CLASSES_DIR', ROOT_WPBL_DIR . 'includes/classes/' );
	}

	// Root WPBL Transients Dir.
	if ( ! defined('ROOT_WPBL_TRANSIENTS_DIR' ) ) {
		define( 'ROOT_WPBL_TRANSIENTS_DIR', ROOT_WPBL_CLASSES_DIR . 'transients/' );
	}

	// Root WPBL Translations Dir.
	if ( ! defined('ROOT_WPBL_TRANSLATIONS_DIR' ) ) {
		define( 'ROOT_WPBL_TRANSLATIONS_DIR', ROOT_WPBL_CLASSES_DIR . 'translations/' );
	}

	// Root WPBL Root Img Icons Dir.
	if ( ! defined('ROOT_WPBL_IMG_ICONS_URL' ) ) {
		define( 'ROOT_WPBL_IMG_ICONS_URL', ROOT_WPBL_URL . 'assets/img/icons/' );
	}

	// Root WPBL Root Utilities Dir.
	if ( ! defined('ROOT_WPBL_UTILITIES_DIR' ) ) {
		define( 'ROOT_WPBL_UTILITIES_DIR', ROOT_WPBL_CLASSES_DIR . 'utilities/' );
	}

	// Root plugin folder URL .
	define( 'KINDLE_ROOT_URL', plugins_url() . '/wpbooklist-kindlepreview/' );

	// Root Classes Directory.
	define( 'KINDLE_CLASS_DIR', KINDLE_ROOT_DIR . 'includes/classes/' );

	// Root Update Directory.
	define( 'KINDLE_UPDATE_DIR', KINDLE_CLASS_DIR . 'update/' );

	// Root REST Classes Directory.
	define( 'KINDLE_CLASS_REST_DIR', KINDLE_ROOT_DIR . 'includes/classes/rest/' );

	// Root Compatability Classes Directory.
	define( 'KINDLE_CLASS_COMPAT_DIR', KINDLE_ROOT_DIR . 'includes/classes/compat/' );

	// Root Transients Directory.
	define( 'KINDLE_CLASS_TRANSIENTS_DIR', KINDLE_ROOT_DIR . 'includes/classes/transients/' );

	// Root Image URL.
	define( 'KINDLE_ROOT_IMG_URL', KINDLE_ROOT_URL . 'assets/img/' );

	// Root Image Icons URL.
	define( 'KINDLE_ROOT_IMG_ICONS_URL', KINDLE_ROOT_URL . 'assets/img/icons/' );

	// Root CSS URL.
	define( 'KINDLE_CSS_URL', KINDLE_ROOT_URL . 'assets/css/' );

	// Root JS URL.
	define( 'KINDLE_JS_URL', KINDLE_ROOT_URL . 'assets/js/' );

	// Root UI directory.
	define( 'KINDLE_ROOT_INCLUDES_UI', KINDLE_ROOT_DIR . 'includes/ui/' );

	// Root UI Admin directory.
	define( 'KINDLE_ROOT_INCLUDES_UI_ADMIN_DIR', KINDLE_ROOT_DIR . 'includes/ui/admin/' );

	// Define the Uploads base directory.
	$uploads     = wp_upload_dir();
	$upload_path = $uploads['basedir'];
	define( 'KINDLE_UPLOADS_BASE_DIR', $upload_path . '/' );

	// Define the Uploads base URL.
	$upload_url = $uploads['baseurl'];
	define( 'KINDLE_UPLOADS_BASE_URL', $upload_url . '/' );

	// Nonces array.
	define( 'KINDLE_NONCES_ARRAY',
		wp_json_encode(array(
			'adminnonce1' => 'wpbooklist_kindle_save_license_key_action_callback',
		))
	);

/* END OF CONSTANT DEFINITIONS */

/* MISC. INCLUSIONS & DEFINITIONS */

	// Loading textdomain.
	load_plugin_textdomain( 'wpbooklist', false, KINDLE_ROOT_DIR . 'languages' );

/* END MISC. INCLUSIONS & DEFINITIONS */

/* CLASS INSTANTIATIONS */

	// Call the class found in wpbooklist-functions.php.
	$kindle_general_functions = new Kindle_General_Functions();

	// Call the class found in wpbooklist-functions.php.
	$kindle_ajax_functions = new Kindle_Ajax_Functions();

	// Include the Update Class.
	$kindle_update_functions = new WPBookList_Kindle_Update();


/* END CLASS INSTANTIATIONS */


/* FUNCTIONS FOUND IN CLASS-WPBOOKLIST-GENERAL-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */

	// Function that adds in the License Key Submission form on this Extension's entry on the plugins page.
	add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $kindle_general_functions, 'wpbooklist_kindle_pluginspage_nonce_entry' ) );

	// Adding the function that will take our KINDLE_NONCES_ARRAY Constant from above and create actual nonces to be passed to Javascript functions.
	add_action( 'init', array( $kindle_general_functions, 'wpbooklist_kindle_create_nonces' ) );

	// Function to run any code that is needed to modify the plugin between different versions.
	add_action( 'plugins_loaded', array( $kindle_general_functions, 'wpbooklist_kindle_update_upgrade_function' ) );

	// Adding the admin js file.
	add_action( 'admin_enqueue_scripts', array( $kindle_general_functions, 'wpbooklist_kindle_admin_js' ) );

	// Adding the frontend js file.
	add_action( 'wp_enqueue_scripts', array( $kindle_general_functions, 'wpbooklist_kindle_frontend_js' ) );

	// Adding the admin css file for this extension.
	add_action( 'admin_enqueue_scripts', array( $kindle_general_functions, 'wpbooklist_kindle_admin_style' ) );

	// Adding the Front-End css file for this extension.
	add_action( 'wp_enqueue_scripts', array( $kindle_general_functions, 'wpbooklist_kindle_frontend_style' ) );

	// Function to add table names to the global $wpdb.
	add_action( 'admin_footer', array( $kindle_general_functions, 'wpbooklist_kindle_register_table_name' ) );

	// Function taht adds in any possible admin pointers
	add_action( 'admin_footer', array( $kindle_general_functions, 'wpbooklist_kindle_admin_pointers_javascript' ) );

	// Creates tables upon activation.
	register_activation_hook( __FILE__, array( $kindle_general_functions, 'wpbooklist_kindle_create_tables' ) );

	// Runs once upon extension activation and adds it's version number to the 'extensionversions' column in the 'wpbooklist_jre_user_options' table of the core plugin.
	register_activation_hook( __FILE__, array( $kindle_general_functions, 'wpbooklist_kindle_record_extension_version' ) );

	// And in the darkness bind them.
	add_filter( 'admin_footer', array( $kindle_general_functions, 'wpbooklist_kindle_smell_rose' ) );

	// Displays the 'Enter Your License Key' message at the top of the dashboard if the user hasn't done so already.
	add_action( 'admin_notices', array( $kindle_general_functions, 'wpbooklist_kindle_top_dashboard_license_notification' ) );

	/*
		global $wpdb;
		$test_name = $wpdb->prefix . 'wpbooklist_kindle_settings';
		if ( $test_name === $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {
			$extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_kindle_settings' );
			if ( false !== stripos( $extension_settings->ovbj, 'aod' ) ) {
				add_filter( 'wpbooklist_add_tab_settings', array( $kindle_general_functions, 'wpbooklist_affiliate_tab' ) );
			}
		}

	*/

	// Function that adds in the License Key Submission form on this Extension's entry on the plugins page.
	add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $kindle_general_functions, 'wpbooklist_kindle_pluginspage_nonce_entry' ) );

	// Verifies that the core WPBookList plugin is installed and activated - otherwise, the Extension doesn't load and a message is displayed to the user.
	register_activation_hook( __FILE__, array( $kindle_general_functions, 'wpbooklist_kindle_core_plugin_required' ) );

	// Adds the display option to hide the kindle preview.
	add_action( 'wpbooklist_add_to_library_display_options_post_kindle', array( $kindle_general_functions, 'wpbooklist_add_to_library_display_options_post_kindle_func' ) );

	// Adds the display option to hide the kindle preview on the Posts display options page.
	add_action( 'wpbooklist_add_to_library_display_options_page_kindle', array( $kindle_general_functions, 'wpbooklist_add_to_library_display_options_page_kindle_func' ) );

	// Adds the Kindle Preview into Colorbox.
	add_action( 'wpbooklist_add_to_colorbox_kindle', array( $kindle_general_functions, 'wpbooklist_add_to_colorbox_kindle_func' ) );

	// Adds the Kindle Preview into the Pages.
	add_action( 'wpbooklist_add_to_page_kindle', array( $kindle_general_functions, 'wpbooklist_add_to_page_kindle_func' ) );

	// Adds the Kindle Preview into the Posts.
	add_action( 'wpbooklist_add_to_post_kindle', array( $kindle_general_functions, 'wpbooklist_add_to_post_kindle_func' ) );





/* END OF FUNCTIONS FOUND IN CLASS-WPBOOKLIST-GENERAL-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */

/* FUNCTIONS FOUND IN CLASS-WPBOOKLIST-AJAX-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */

	// For receiving user feedback upon deactivation & deletion.
	add_action( 'wp_ajax_kindle_exit_results_action', array( $kindle_ajax_functions, 'kindle_exit_results_action_callback' ) );

	// Callback function for handling the saving of the user's License Key.
	add_action( 'wp_ajax_wpbooklist_kindle_save_license_key_action', array( $kindle_ajax_functions, 'wpbooklist_kindle_save_license_key_action_callback' ) );

/* END OF FUNCTIONS FOUND IN CLASS-WPBOOKLIST-AJAX-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */






















