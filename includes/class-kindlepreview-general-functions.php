<?php
/**
 * Class Kindle_General_Functions - class-kindle-general-functions.php
 *
 * @author   Jake Evans
 * @category Admin
 * @package  Includes
 * @version  6.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Kindle_General_Functions', false ) ) :
	/**
	 * Kindle_General_Functions class. Here we'll do things like enqueue scripts/css, set up menus, etc.
	 */
	class Kindle_General_Functions {

		/**
		 * Verifies that the core WPBookList plugin is installed and activated - otherwise, the Extension doesn't load and a message is displayed to the user.
		 */
		public function wpbooklist_kindle_core_plugin_required() {

			// Require core WPBookList Plugin.
			if ( ! is_plugin_active( 'wpbooklist/wpbooklist.php' ) && current_user_can( 'activate_plugins' ) ) {

				// Stop activation redirect and show error.
				wp_die( 'Whoops! This WPBookList Extension requires the Core WPBookList Plugin to be installed and activated! <br><a target="_blank" href="https://wordpress.org/plugins/wpbooklist/">Download WPBookList Here!</a><br><br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>' );
			}
		}

		/**
		 * Verifies the crown of the rose.
		 *
		 * @param  array $plugins List of plugins to activate & load.
		 */
		public function wpbooklist_kindle_smell_rose() {

			global $wpdb;

			// Checking if table exists.
			$test_name = $wpdb->prefix . 'wpbooklist_kindle_settings';
			if ( $test_name === $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {

				// Get license key from plugin options, if it's already been saved. If it has, don't display anything.
				$this->extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_kindle_settings' );

				// If the License Key just hasn't been entered yet...
				if ( null === $this->extension_settings->ovbj || '' === $this->extension_settings->ovbj ) {
					return;
				} else {

					if ( false !== stripos( $this->extension_settings->ovbj, '---' ) ) {

						$temp = explode( '---', $this->extension_settings->ovbj );

						if ( 'aod' === $temp[1] ) {

							// Get the date.
							require_once ROOT_WPBL_UTILITIES_DIR . 'class-wpbooklist-utilities-date.php';
							$utilities_date = new WPBookList_Utilities_Date();
							$this->date     = $utilities_date->wpbooklist_get_date_via_current_time( 'timestamp' );

							if ( 604800 < ( $this->date - (int) $temp[2] ) ) {

								$checker_good_flag = false;

								$san_check = wp_remote_get( 'https://wpbooklist.com/?edd_action=activate_license&item_id=' . EDD_SL_ITEM_ID_KINDLE . '&license=' . $temp[0] . '&url=' . get_site_url() );

								// Check the response code.
								$response_code    = wp_remote_retrieve_response_code( $san_check );
								$response_message = wp_remote_retrieve_response_message( $san_check );

								if ( 200 !== $response_code && ! empty( $response_message ) ) {
									return new WP_Error( $response_code, $response_message );
								} elseif ( 200 !== $response_code ) {
									$this->apireport = $this->apireport . 'Unknown error occurred with wp_remote_get() trying to build Books-a-Million link in the create_buy_links() function ';
									return new WP_Error( $response_code, 'Unknown error occurred with wp_remote_get() trying to build Books-a-Million link in the create_buy_links() function' );
								} else {
									$san_check = wp_remote_retrieve_body( $san_check );
									$san_check = json_decode( $san_check, true );

									if ( 'valid' === $san_check['license'] && $san_check['success'] ) {

										$this->date = $utilities_date->wpbooklist_get_date_via_current_time( 'timestamp' );

										$data         = array(
											'ovbj' => $temp[0] . '---aod---' . $this->date,
										);
										$format       = array( '%s' );
										$where        = array( 'ID' => 1 );
										$where_format = array( '%d' );
										$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_kindle_settings', $data, $where, $format, $where_format );

										$checker_good_flag = true;
									} else {
										$data         = array(
											'ovbj' => '',
										);
										$format       = array( '%s' );
										$where        = array( 'ID' => 1 );
										$where_format = array( '%d' );
										$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_kindle_settings', $data, $where, $format, $where_format );
									}
								}

								if ( ! $checker_good_flag ) {
									deactivate_plugins( KINDLE_ROOT_DIR . 'wpbooklist-kindle.php' );
									return;
								}
							} else {
								return;
							}
						} else {

							$checker_good_flag = false;

							$san_check = wp_remote_get( 'https://wpbooklist.com/?edd_action=activate_license&item_id=' . EDD_SL_ITEM_ID_KINDLE . '&license=' . $this->extension_settings->ovbj . '&url=' . get_site_url() );

							// Check the response code.
							$response_code    = wp_remote_retrieve_response_code( $san_check );
							$response_message = wp_remote_retrieve_response_message( $san_check );

							if ( 200 !== $response_code && ! empty( $response_message ) ) {
								return new WP_Error( $response_code, $response_message );
							} elseif ( 200 !== $response_code ) {
								$this->apireport = $this->apireport . 'Unknown error occurred with wp_remote_get() trying to build Books-a-Million link in the create_buy_links() function ';
								return new WP_Error( $response_code, 'Unknown error occurred with wp_remote_get() trying to build Books-a-Million link in the create_buy_links() function' );
							} else {
								$san_check = wp_remote_retrieve_body( $san_check );
								$san_check = json_decode( $san_check, true );

								if ( 'valid' === $san_check['license'] && $san_check['success'] ) {

									// Get the date.
									require_once ROOT_WPBL_UTILITIES_DIR . 'class-wpbooklist-utilities-date.php';
									$utilities_date = new WPBookList_Utilities_Date();
									$this->date     = $utilities_date->wpbooklist_get_date_via_current_time( 'timestamp' );

									$data         = array(
										'ovbj' => $this->extension_settings->ovbj . '---aod---' . $this->date,
									);
									$format       = array( '%s' );
									$where        = array( 'ID' => 1 );
									$where_format = array( '%d' );
									$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_kindle_settings', $data, $where, $format, $where_format );

									$checker_good_flag = true;

								} else {
									$data         = array(
										'ovbj' => '',
									);
									$format       = array( '%s' );
									$where        = array( 'ID' => 1 );
									$where_format = array( '%d' );
									$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_kindle_settings', $data, $where, $format, $where_format );
								}
							}

							if ( ! $checker_good_flag ) {
								deactivate_plugins( KINDLE_ROOT_DIR . 'wpbooklist-kindle.php' );
								return;
							}
						}
					} else {

						$checker_good_flag = false;

						$san_check = wp_remote_get( 'https://wpbooklist.com/?edd_action=activate_license&item_id=' . EDD_SL_ITEM_ID_KINDLE . '&license=' . $this->extension_settings->ovbj . '&url=' . get_site_url() );

						// Check the response code.
						$response_code    = wp_remote_retrieve_response_code( $san_check );
						$response_message = wp_remote_retrieve_response_message( $san_check );

						if ( 200 !== $response_code && ! empty( $response_message ) ) {
							return new WP_Error( $response_code, $response_message );
						} elseif ( 200 !== $response_code ) {
							$this->apireport = $this->apireport . 'Unknown error occurred with wp_remote_get() trying to build Books-a-Million link in the create_buy_links() function ';
							return new WP_Error( $response_code, 'Unknown error occurred with wp_remote_get() trying to build Books-a-Million link in the create_buy_links() function' );
						} else {
							$san_check = wp_remote_retrieve_body( $san_check );
							$san_check = json_decode( $san_check, true );

							if ( 'valid' === $san_check['license'] && $san_check['success'] ) {

								// Get the date.
								require_once ROOT_WPBL_UTILITIES_DIR . 'class-wpbooklist-utilities-date.php';
								$utilities_date = new WPBookList_Utilities_Date();
								$this->date     = $utilities_date->wpbooklist_get_date_via_current_time( 'timestamp' );

								$data         = array(
									'ovbj' => $this->extension_settings->ovbj . '---aod---' . $this->date,
								);
								$format       = array( '%s' );
								$where        = array( 'ID' => 1 );
								$where_format = array( '%d' );
								$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_kindle_settings', $data, $where, $format, $where_format );

								$checker_good_flag = true;

							} else {
								$data         = array(
									'ovbj' => '',
								);
								$format       = array( '%s' );
								$where        = array( 'ID' => 1 );
								$where_format = array( '%d' );
								$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_kindle_settings', $data, $where, $format, $where_format );
							}
						}

						if ( ! $checker_good_flag ) {
							deactivate_plugins( KINDLE_ROOT_DIR . 'wpbooklist-kindle.php' );

							if ( isset( $_SERVER['REQUEST_URI'] ) ) {
								//header( 'Location: ' . filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_STRING ) );
							}

							return;
						}
					}
				}
			}
		}

		/**
		 * Adds in the 'Enter License Key' text input and submit button.
		 *
		 * @param  array $links List of existing plugin action links.
		 * @return array List of modified plugin action links.
		 */
		public function wpbooklist_kindle_pluginspage_nonce_entry( $links ) {

			global $wpdb;

			require_once ROOT_WPBL_TRANSLATIONS_DIR . 'class-wpbooklist-translations.php';
			$trans = new WPBookList_Translations();

			// Checking if table exists.
			$test_name = $wpdb->prefix . 'wpbooklist_kindle_settings';
			if ( $test_name === $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {

				$this->extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_kindle_settings' );

				if ( null === $this->extension_settings->ovbj || '' === $this->extension_settings->ovbj ) {
					$value = $trans->trans_613;
				} else {

					if ( false !== stripos( $this->extension_settings->ovbj, '---' ) ) {
						$temp  = explode( '---', $this->extension_settings->ovbj );
						$value = $temp[0];
					} else {
						$value = $this->extension_settings->ovbj;
					}
				}

				$form_html = '
					<form>
						<input id="wpbooklist-extension-genreric-key-plugins-page-input-kindle" class="wpbooklist-extension-genreric-key-plugins-page-input" type="text" placeholder="' . $trans->trans_613 . '" value="' . $value . '"></input>
						<button id="wpbooklist-extension-genreric-key-plugins-page-button-kindle" class="wpbooklist-extension-genreric-key-plugins-page-button">' . $trans->trans_614 . '</button>
					</form>';

				array_push( $links, $form_html );

			}

			return $links;

		}

		/**
		 * Displays the 'Enter Your License Key' message at the top of the dashboard if the user hasn't done so already.
		 */
		public function wpbooklist_kindle_top_dashboard_license_notification() {

			global $wpdb;

			// Checking if table exists.
			$test_name = $wpdb->prefix . 'wpbooklist_kindle_settings';
			if ( $test_name === $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {

				// Get license key from plugin options, if it's already been saved. If it has, don't display anything.
				$this->extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_kindle_settings' );

				if ( null === $this->extension_settings->ovbj || '' === $this->extension_settings->ovbj ) {

					require_once ROOT_WPBL_TRANSLATIONS_DIR . 'class-wpbooklist-translations.php';
					$trans = new WPBookList_Translations();

					echo '
					<div class="notice notice-success is-dismissible">
						<form class="wpbooklist-extension-genreric-key-dashboard-form" id="wpbooklist-extension-genreric-key-dashboard-form-kindle">
							<p class="wpbooklist-extension-genreric-key-dashboard-title">' . $trans->trans_625 . '</p>
							<input id="wpbooklist-extension-genreric-key-dashboard-input-kindle" class="wpbooklist-extension-genreric-key-dashboard-input" type="text" placeholder="' . $trans->trans_613 . '" value="' . $trans->trans_613 . '"></input>
							<button data-ext="kindle" id="wpbooklist-extension-genreric-key-dashboard-button-kindle" class="wpbooklist-extension-genreric-key-dashboard-button">' . $trans->trans_616 . '</button>
						</form>
					</div>';
				}

			}
		}

		/** Functions that loads up the menu page entry for this Extension.
		 *
		 *  @param array $submenu_array - The array that contains submenu entries to add to.
		 */
		public function wpbooklist_kindle_submenu( $submenu_array ) {
			$extra_submenu = array(
				'Kindle',
			);

			// Combine the two arrays.
			$submenu_array = array_merge( $submenu_array, $extra_submenu );
			return $submenu_array;
		}

		/**
		 *  Here we take the Constant defined in wpbooklist.php that holds the values that all our nonces will be created from, we create the actual nonces using wp_create_nonce, and the we define our new, final nonces Constant, called WPBOOKLIST_FINAL_NONCES_ARRAY.
		 */
		public function wpbooklist_kindle_create_nonces() {

			$temp_array = array();
			foreach ( json_decode( KINDLE_NONCES_ARRAY ) as $key => $noncetext ) {
				$nonce              = wp_create_nonce( $noncetext );
				$temp_array[ $key ] = $nonce;
			}

			// Defining our final nonce array.
			define( 'KINDLE_FINAL_NONCES_ARRAY', wp_json_encode( $temp_array ) );

		}

		/**
		 *  Runs once upon extension activation and adds it's version number to the 'extensionversions' column in the 'wpbooklist_jre_user_options' table of the core plugin.
		 */
		public function wpbooklist_kindle_record_extension_version() {
			global $wpdb;
			$existing_string = $wpdb->get_row( 'SELECT * from ' . $wpdb->prefix . 'wpbooklist_jre_user_options' );

			// Check to see if Extension is already registered.
			if ( false !== strpos( $existing_string->extensionversions, 'kindle' ) ) {
				$split_string = explode( 'kindle', $existing_string->extensionversions );
				$first_part   = $split_string[0];
				$last_part    = substr( $split_string[1], 5 );
				$new_string   = $first_part . 'kindle' . WPBOOKLIST_KINDLE_VERSION_NUM . $last_part;
			} else {
				$new_string = $existing_string->extensionversions . 'kindle' . WPBOOKLIST_KINDLE_VERSION_NUM;
			}

			$data         = array(
				'extensionversions' => $new_string,
			);
			$format       = array( '%s' );
			$where        = array( 'ID' => 1 );
			$where_format = array( '%d' );
			$wpdb->update( $wpdb->prefix . 'wpbooklist_jre_user_options', $data, $where, $format, $where_format );

		}

		/**
		 *  Function to run the compatability code in the Compat class for upgrades/updates, if stored version number doesn't match the defined global in wpbooklist-kindle.php
		 */
		public function wpbooklist_kindle_update_upgrade_function() {

			// Get current version #.
			global $wpdb;
			$existing_string = $wpdb->get_row( 'SELECT * from ' . $wpdb->prefix . 'wpbooklist_jre_user_options' );

			// Check to see if Extension is already registered and matches this version.
			if ( false !== strpos( $existing_string->extensionversions, 'kindle' ) ) {
				$split_string = explode( 'kindle', $existing_string->extensionversions );
				$version      = substr( $split_string[1], 0, 5 );

				// If version number does not match the current version number found in wpbooklist.php, call the Compat class and run upgrade functions.
				if ( WPBOOKLIST_KINDLE_VERSION_NUM !== $version ) {
					require_once KINDLE_CLASS_COMPAT_DIR . 'class-kindle-compat-functions.php';
					$compat_class = new Kindle_Compat_Functions();
				}
			}
		}

		/**
		 * Adding the admin js file
		 */
		public function wpbooklist_kindle_admin_js() {

			wp_register_script( 'wpbooklist_kindle_adminjs', KINDLE_JS_URL . 'wpbooklist_kindle_admin.min.js', array( 'jquery' ), WPBOOKLIST_VERSION_NUM, true );

			// Next 4-5 lines are required to allow translations of strings that would otherwise live in the wpbooklist-admin-js.js JavaScript File.
			require_once ROOT_WPBL_TRANSLATIONS_DIR . 'class-wpbooklist-translations.php';
			$trans = new WPBookList_Translations();

			// Localize the script with the appropriate translation array from the Translations class.
			$translation_array1 = $trans->trans_strings();

			// Now grab all of our Nonces to pass to the JavaScript for the Ajax functions and merge with the Translations array.
			$final_array_of_php_values = array_merge( $translation_array1, json_decode( KINDLE_FINAL_NONCES_ARRAY, true ) );

			// Adding some other individual values we may need.
			$final_array_of_php_values['KINDLE_ROOT_IMG_ICONS_URL'] = KINDLE_ROOT_IMG_ICONS_URL;
			$final_array_of_php_values['KINDLE_ROOT_IMG_URL']       = KINDLE_ROOT_IMG_URL;
			$final_array_of_php_values['FOR_TAB_HIGHLIGHT']                         = admin_url() . 'admin.php';
			$final_array_of_php_values['SAVED_ATTACHEMENT_ID']                      = get_option( 'media_selector_attachment_id', 0 );

			// Now registering/localizing our JavaScript file, passing all the PHP variables we'll need in our $final_array_of_php_values array, to be accessed from 'wphealthtracker_php_variables' object (like wphealthtracker_php_variables.nameofkey, like any other JavaScript object).
			wp_localize_script( 'wpbooklist_kindle_adminjs', 'wpbooklistKindlePhpVariables', $final_array_of_php_values );

			wp_enqueue_script( 'wpbooklist_kindle_adminjs' );

			return $final_array_of_php_values;

		}

		/**
		 * Adding the frontend js file
		 */
		public function wpbooklist_kindle_frontend_js() {

			wp_register_script( 'wpbooklist_kindle_frontendjs', KINDLE_JS_URL . 'wpbooklist_kindle_frontend.min.js', array( 'jquery' ), WPBOOKLIST_KINDLE_VERSION_NUM, true );

			// Next 4-5 lines are required to allow translations of strings that would otherwise live in the wpbooklist-admin-js.js JavaScript File.
			require_once ROOT_WPBL_TRANSLATIONS_DIR . 'class-wpbooklist-translations.php';
			$trans = new WPBookList_Translations();

			// Localize the script with the appropriate translation array from the Translations class.
			$translation_array1 = $trans->trans_strings();

			// Now grab all of our Nonces to pass to the JavaScript for the Ajax functions and merge with the Translations array.
			$final_array_of_php_values = array_merge( $translation_array1, json_decode( KINDLE_FINAL_NONCES_ARRAY, true ) );

			// Adding some other individual values we may need.
			$final_array_of_php_values['KINDLE_ROOT_IMG_ICONS_URL'] = KINDLE_ROOT_IMG_ICONS_URL;
			$final_array_of_php_values['KINDLE_ROOT_IMG_URL']       = KINDLE_ROOT_IMG_URL;

			// Now registering/localizing our JavaScript file, passing all the PHP variables we'll need in our $final_array_of_php_values array, to be accessed from 'wphealthtracker_php_variables' object (like wphealthtracker_php_variables.nameofkey, like any other JavaScript object).
			wp_localize_script( 'wpbooklist_kindle_frontendjs', 'wpbooklistKindlePhpVariables', $final_array_of_php_values );

			wp_enqueue_script( 'wpbooklist_kindle_frontendjs' );

			return $final_array_of_php_values;

		}

		/**
		 * Adding the admin css file
		 */
		public function wpbooklist_kindle_admin_style() {

			wp_register_style( 'wpbooklist_kindle_adminui', KINDLE_CSS_URL . 'wpbooklist-kindle-main-admin.css', null, WPBOOKLIST_KINDLE_VERSION_NUM );
			wp_enqueue_style( 'wpbooklist_kindle_adminui' );

		}

		/**
		 * Adding the frontend css file
		 */
		public function wpbooklist_kindle_frontend_style() {

			wp_register_style( 'wpbooklist_kindle_frontendui', KINDLE_CSS_URL . 'wpbooklist-kindle-main-frontend.css', null, WPBOOKLIST_KINDLE_VERSION_NUM );
			wp_enqueue_style( 'wpbooklist_kindle_frontendui' );

		}

		/**
		 *  Function to add table names to the global $wpdb.
		 */
		public function wpbooklist_kindle_register_table_name() {
			global $wpdb;
			$wpdb->wpbooklist_kindle_settings = "{$wpdb->prefix}wpbooklist_kindle_settings";
		}

		/**
		 *  Function that calls the Style and Scripts needed for displaying of admin pointer messages.
		 */
		public function wpbooklist_kindle_admin_pointers_javascript() {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
			wp_enqueue_script( 'utils' );
		}

		/**
		 *  Runs once upon plugin activation and creates the table that holds info on WPBookList Pages & Posts.
		 */
		public function wpbooklist_kindle_create_tables() {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			global $wpdb;
			global $charset_collate;

			// Call this manually as we may have missed the init hook.
			$this->wpbooklist_kindle_register_table_name();

			$sql_create_table1 = "CREATE TABLE {$wpdb->wpbooklist_kindle_settings}
			(
				ID bigint(190) auto_increment,
				ovbj varchar(255),
				PRIMARY KEY  (ID),
				KEY ovbj (ovbj)
			) $charset_collate; ";

			// If table doesn't exist, create table and add initial data to it.
			$test_name = $wpdb->prefix . 'wpbooklist_kindle_settings';
			if ( $test_name !== $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {
				dbDelta( $sql_create_table1 );
				$table_name = $wpdb->prefix . 'wpbooklist_kindle_settings';
				$wpdb->insert( $table_name, array( 'ID' => 1, ) );
			}
		}


		/**
		 * Adds the display option to hide the kindle preview
		 *
		 * @param  string $string saved library display option.
		 */
		public function wpbooklist_add_to_library_display_options_kindle_func( $string ) {

			$string1 = '<tr>
            <td><label>Hide Kindle Preview</label></td>
            <td class="wpbooklist-margin-right-td"><input type="checkbox" name="hide-frontend-kindle-preview"';

			$string2 = '';
			if ( null !== $string && 0 !== $string ) {
				$string2 = esc_attr( 'checked="checked"' );
			}

			$string3 = '></input></td>
			</tr>';

			return $string1 . $string2 . $string3;
		}

		/**
		 * Adds the display option to hide the kindle preview on the Posts display options page.
		 *
		 * @param  string $string saved library display option.
		 */
		public function wpbooklist_add_to_library_display_options_post_kindle_func( $string ) {

			$string1 = '<tr>
            <td><label>Hide Kindle Preview</label></td>
            <td class="wpbooklist-margin-right-td"><input type="checkbox" name="hide-frontend-kindle-preview"';

			$string2 = '';
			if ( null !== $string && 0 !== $string ) {
				$string2 = esc_attr( 'checked="checked"' );
			}

			$string3 = '></input></td>
			</tr>';

			return $string1 . $string2 . $string3;
		}

		/**
		 * Adds the display option to hide the kindle preview on the Pages display options page.
		 *
		 * @param  string $string saved library display option.
		 */
		public function wpbooklist_add_to_library_display_options_page_kindle_func( $string ) {

			$string1 = '<tr>
			<td><label>Hide Kindle Preview</label></td>
			<td class="wpbooklist-margin-right-td"><input type="checkbox" name="hide-frontend-kindle-preview"';

			$string2 = '';
			if ( null !== $string && 0 !== $string ) {
				$string2 = esc_attr( 'checked="checked"' );
			}

			$string3 = '></input></td>
			</tr>';

			return $string1 . $string2 . $string3;
		}

		/**
		 * Adds the Kindle Preview into Colorbox.
		 *
		 * @param  string $string saved library display option.
		 */
		public function wpbooklist_add_to_colorbox_kindle_func( $string ) {

			$string1 = '<p class="wpbooklist_description_p" id="wpbooklist-kindle-title-id">Kindle Preview:</p><div class="wpbooklist_kindle_p_class">';
			$string2 = '<iframe class="kp-bookcard-inline-reader" width="100%" height="100%" allowfullscreen="true" type="text/html" src="https://read.amazon.com/kp/embed?asin=' . $string[0] . '&linkCode=kpe&tag=' . $string[1] . '&preview=inline&from=Bookcard" ></iframe></div>';
			return $string1 . $string2;
		}

		/**
		 * Adds the Kindle Preview into Pages.
		 *
		 * @param  string $string saved library display option.
		 */
		public function wpbooklist_add_to_page_kindle_func( $string ) {
			$string1 = '<p style="font-weight:bold; font-size:18px; margin-bottom:5px;" class="wpbl-pagetd-share-text">Kindle Preview</p><div class="wpbooklist_kindle_page_post_class">';
			$string2 = '<iframe type="text/html" src="https://read.amazon.com/kp/card?asin=' . $string[0] . '&linkCode=kpe&ref_=cm_sw_r_kb_dp_9WxVzbWFMP2KQ&tag=' . $string[1] . '" ></iframe></div>';
			return $string1 . $string2;
		}

		/**
		 * Adds the Kindle Preview into Posts.
		 *
		 * @param  string $string saved library display option.
		 */
		public function wpbooklist_add_to_post_kindle_func( $string ) {
			$string1 = '<p style="font-weight:bold; font-size:18px; margin-bottom:5px;" class="wpbl-posttd-share-text">Kindle Preview</p><div class="wpbooklist_kindle_page_post_class">';
			$string2 = '<iframe type="text/html" src="https://read.amazon.com/kp/card?asin=' . $string[0] . '&linkCode=kpe&ref_=cm_sw_r_kb_dp_9WxVzbWFMP2KQ&tag=' . $string[1] . '" ></iframe></div>';
			return $string1 . $string2;
		}



	}
endif;
