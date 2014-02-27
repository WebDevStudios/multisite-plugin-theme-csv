<?php
/*
Plugin Name: Multisite Plugin CSV
Version: 1.1.0
License: GPL version 2 or any later version
Description: Generate a CSV list of all plugins and their activation status on a multisite network
Author: Ryan Duff
Author URI: http://maintainn.com
Plugin URI: http://maintainn.com
Text Domain: multisite-plugin-csv
Domain Path: /languages
*/

add_action( 'plugins_loaded', array ( MultisitePluginCSV::get_instance(), 'plugin_setup' ) );

class MultisitePluginCSV {

	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @type object
	 */
	protected static $instance = NULL;


	/**
	 * URL to this plugin's directory.
	 *
	 * @type string
	 */
	public $plugin_url = '';


	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 */
	public $plugin_path = '';


	/**
	 * Array of all plugins.
	 *
	 * @type string
	 */
	public $all_plugins = '';


	/**
	 * Array of network active plugins.
	 *
	 * @type string
	 */
	public $network_active_plugins = '';


	/**
	 * Array of all themes.
	 *
	 * @type string
	 */
	public $all_themes = '';


	/**
	 * Array of all network active themes.
	 *
	 * @type string
	 */
	public $all_themes_network = '';


	/**
	 * Access this plugin’s working instance
	 *
	 * @since   1.0.0
	 * @return  object of this class
	 */
	public static function get_instance() {

		NULL === self::$instance and self::$instance = new self;

		return self::$instance;

	}


	/**
	 * Used for plugin setup and hooks
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	public function plugin_setup() {

		$this->plugin_url    = plugins_url( '/', __FILE__ );
		$this->plugin_path   = plugin_dir_path( __FILE__ );
		$this->load_language( 'multisite-plugin-csv' );

		$this->network_active_plugins = array_keys( get_site_option( 'active_sitewide_plugins', false, false ) );

		add_action( 'network_admin_menu', array( $this, 'multisite_plugin_csv_menu' ) );
		add_action( 'admin_init', array( $this, 'check_request' ) );

	}


	/**
	 * Constructor. Intentionally left empty and public.
	 *
	 * @see plugin_setup()
	 * @since 1.0.0
	 */
	public function __construct() {
	}


	/**
	 * Loads translation file.
	 *
	 * Accessible to other classes to load different language files (admin and
	 * front-end for example).
	 *
	 * @param   string $domain
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_language( $domain ) {

		load_plugin_textdomain( $domain, false, $this->plugin_path . 'languages' );

	}


	/**
	 * Add 'Multisite Plugin CSV' menu page under Plugins menu
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function multisite_plugin_csv_menu() {

		add_submenu_page( 'plugins.php', __( 'Multisite Plugin CSV', 'multisite-plugin-csv' ), __( 'Multisite Plugin CSV', 'multisite-plugin-csv' ), 'manage-sites', 'multisite-plugin-csv', array( $this, 'multisite_plugin_csv_page' ) );

	}


	/**
	 * Display Multisite Plugin CSV admin page
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function multisite_plugin_csv_page() {

		echo '<div class="wrap">';
			echo '<h2>' . __( 'Multisite Plugin CSV', 'multisite-plugin-csv' ) . '</h2>';
			echo '<div>' . __( 'This process will generate a report of all plugins on the network. It will list which plugins are active on which sites and return a sortable CSV file.', 'multisite-plugin-csv' ) . '</div>';
			echo '<a href="' . wp_nonce_url( 'plugins.php?page=multisite-plugin-csv&action=generate-plugin-csv-plugins', 'multisite-plugin-csv-generate-plugins') . '" class="button" style="margin:20px auto;" />' . __( 'Generate Plugin Report!', 'multisite-plugin-csv' ) . '</a>';
			echo '<div class="clear"></div>';
			echo '<a href="' . wp_nonce_url( 'plugins.php?page=multisite-plugin-csv&action=generate-plugin-csv-themes', 'multisite-plugin-csv-generate-themes') . '" class="button" style="margin:20px auto;" />' . __( 'Generate Theme Report!', 'multisite-plugin-csv' ) . '</a>';
		echo '</div><!-- /.wrap -->';

	}


	/**
	 * Check our request and trigger a plugin or theme CSV if requested
	 *
	 * @since  1.1.0
	 *
	 * @return void
	 */
	public function check_request() {

		$action = empty( $_REQUEST['action'] ) ? '' : $_REQUEST['action'];
		$nonce = empty( $_REQUEST['_wpnonce'] ) ? '' : $_REQUEST['_wpnonce'];

		if ( ( 'generate-plugin-csv-plugins' === $action ) && wp_verify_nonce( $nonce, 'multisite-plugin-csv-generate-plugins' ) ) {

			$this->output_plugin_csv();

		} elseif ( ( 'generate-plugin-csv-themes' === $action ) && wp_verify_nonce( $nonce, 'multisite-plugin-csv-generate-themes' ) ) {

			$this->output_theme_csv();

		}

	}


	/**
	 * Generate the Plugin CSV file
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	protected function output_plugin_csv() {

		// Get main network site domain and sanitize
		global $current_site;
		$network_domain = sanitize_title( $current_site->domain );

		// Generate our filename to use
		$filename = 'multisite-active-plugins_' . $network_domain . '.csv';

		// Setup headers
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: text/csv' ) ;
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Expires: 0' );
		header( 'Pragma: public' );

		$fh = @fopen( 'php://output', 'w' );

			// Get our header row
			$header = $this->generate_csv_header_plugin();

			// Add the header row
			fputcsv( $fh, $header );

			// Generate plugin data for the network
			$site_plugins = $this->generate_plugin_list();

			// Loop through adding a row for each site
			foreach ( $site_plugins as $row ) {

				fputcsv( $fh, $row );

			}

		// Close the file
		fclose ($fh );

		// Exit so nothing else gets sent
		exit();

	}


	/**
	 * Generate the Theme CSV file
	 *
	 * @since  1.1.0
	 *
	 * @return void
	 */
	protected function output_theme_csv() {

		// Get all of our theme data for the site
		$this->all_themes = wp_get_themes();
		$this->all_themes_network = wp_get_themes( array( 'allowed' => 'network' ) );

		// Get main network site domain and sanitize
		global $current_site;
		$network_domain = sanitize_title( $current_site->domain );

		// Generate our filename to use
		$filename = 'multisite-active-themes_' . $network_domain . '.csv';

		// Setup headers
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: text/csv' ) ;
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Expires: 0' );
		header( 'Pragma: public' );

		$fh = @fopen( 'php://output', 'w' );

			// Get our header row
			$header = $this->generate_csv_header_themes();

			// Add the header row
			fputcsv( $fh, $header );

			// Generate theme data for the network
			$site_plugins = $this->generate_theme_list();

			// Loop through adding a row for each site
			foreach ( $site_plugins as $row ) {

				fputcsv( $fh, $row );

			}

		// Close the file
		fclose ($fh );

		// Exit so nothing else gets sent
		exit();

	}


	/**
	 * Gather all the plugin data from every site on the network
	 *
	 * @since  1.0.0
	 *
	 * @return array  An array of sites containing an array of plugin statuses
	 */
	protected function generate_plugin_list() {

		// Get a list of all installed plugins
		$this->all_plugins = get_plugins();

		// Grab our site IDs to loop through
		$site_ids = $this->get_site_ids();

		// An array to hold our plugin data
		$plugin_list = array();

		// Loop through site ids to generate each row of CSV data
		foreach ( $site_ids as $site_id ) {

			$plugin_list[] = $this->process_site_plugins( $site_id );

		}

		return $plugin_list;

	}


	/**
	 * Gather all the theme data from every site on the network
	 *
	 * @since  1.1.0
	 *
	 * @return array  An array of sites containing an array of theme statuses
	 */
	protected function generate_theme_list() {

		// Grab our site IDs to loop through
		$site_ids = $this->get_site_ids();

		// An array to hold our plugin data
		$theme_list = array();

		// Loop through site ids to generate each row of CSV data
		foreach ( $site_ids as $site_id ) {

			$theme_list[] = $this->process_site_themes( $site_id );

		}

		return $theme_list;

	}


	/**
	 * Get the ids of sites active on the network
	 *
	 * @since  1.0.0
	 *
	 * @return array  An array of site IDs
	 */
	protected function get_site_ids(){

		global $wpdb;

		$blogs = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = '{$wpdb->siteid}' AND spam = '0' AND deleted = '0' AND archived = '0' ORDER BY registered ASC" );

		return $blogs;

	}


	/**
	 * Build the header row for the plugin CSV file
	 *
	 * @since  1.0.0
	 *
	 * @return array  An array of columns used in the plugin CSV file
	 */
	protected function generate_csv_header_plugin() {

		// Get all of our plugin data for the site
		$plugins = get_plugins();

		// Create an array to hold our header data
		$header = array();

		// Insert our first column title
		$header[] = __( 'Site URL', 'multisite-plugin-csv' );

		// Add the title and file path for each plugin
		foreach ( $plugins as $plugin => $data ) {

			$header[] = $data['Name'] . ' (' . $plugin . ')';

		}

		return $header;

	}


	/**
	 * Build the header row for the theme CSV file
	 *
	 * @since  1.1.0
	 *
	 * @return array  An array of columns used in the theme CSV file
	 */
	protected function generate_csv_header_themes() {

		// Create an array to hold our header data
		$header = array();

		// Insert our first column title
		$header[] = __( 'Site URL', 'multisite-plugin-csv' );

		// Add the title and file path for each theme
		foreach ( $this->all_themes as $theme => $data ) {

			// $path = $theme->get_stylesheet_directory();

			$header[] = $data->Name . ' (' . $theme . ')';

		}

		return $header;

	}


	/**
	 * Process a site and build a row of plugin data
	 *
	 * @since  1.0.0
	 *
	 * @param  integer $site_id The id of the site we're processing
	 *
	 * @return array           An array of which plugins are active/inactive/network active
	 */
	protected function process_site_plugins( $site_id = 0 ) {

		// Switch to this site so we can gather some data
		switch_to_blog( $site_id );

			$siteurl = site_url();

			$active_plugins = get_option( 'active_plugins' );

		restore_current_blog();

		// An array to hold our plugin data for this site
		$row = array();

		// Add the site url as the first column
		$row[] = $siteurl;

		// Prune this down to just the data we need (the plugin path/file)
		$all_plugins = array_keys( $this->all_plugins );

		// Loop through all installed plugins
		foreach ( $all_plugins as $plugin ) {

			// If it's an active plugin for the site
			if ( in_array( $plugin, $active_plugins ) ) {

				$row[] = __( 'Yes', 'multisite-plugin-csv' );

			// If it's network active
			} elseif ( in_array( $plugin, $this->network_active_plugins ) ) {

				$row[] = __( 'Network Active', 'multisite-plugin-csv' );

			// If we're here, it's not active
			} else {

				$row[] = __( 'No', 'multisite-plugin-csv' );

			}

		}

		return $row;

	}


	/**
	 * Process a site and build a row of theme data
	 *
	 * @since  1.1.0
	 *
	 * @param  integer $site_id The id of the site we're processing
	 *
	 * @return array           An array of active/available themes
	 */
	protected function process_site_themes( $site_id = 0 ) {

		// Switch to this site so we can gather some data
		switch_to_blog( $site_id );

			$siteurl = site_url();

			$current_theme = basename( get_stylesheet_directory() );

			$allowed_themes = wp_get_themes( array( 'allowed' => true ) );

		restore_current_blog();

		// An array to hold our theme data for this site
		$row = array();

		// Add the site url as the first column
		$row[] = $siteurl;

		// Grab the array keys (theme folder slugs)
		$theme_slugs = array_keys( $this->all_themes );

		// Loop through all installed themes
		foreach ( $theme_slugs as $theme ) {

			// If it's the active them for the site
			if (  $theme === $current_theme ) {

				$row[] = __( 'Active', 'multisite-plugin-csv' );

			// If it's network available
			} elseif ( in_array( $theme, array_keys( $this->all_themes_network ) ) ) {

				$row[] = __( 'Available (Network)', 'multisite-plugin-csv' );

			// If it's manually available
			} elseif ( in_array( $theme, array_keys( $allowed_themes ) ) ) {

				$row[] = __( 'Available (Site)', 'multisite-plugin-csv' );

			// If it's not available at all
			} else {

				$row[] = __( 'Not Available', 'multisite-plugin-csv' );

			}

		}

		return $row;

	}

}
