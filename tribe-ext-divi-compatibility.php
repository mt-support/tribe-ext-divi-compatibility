<?php
/**
 * Plugin Name:       The Events Calendar Extension: Divi Compatibility
 * Plugin URI:        https://theeventscalendar.com/extensions/elegant-themes-divi-theme-compatibility/
 * GitHub Plugin URI: https://github.com/mt-support/tribe-ext-divi-compatibility
 * Description:       Makes The Events Calendar compatible with Elegant Themes' Divi theme and builder plugin and Divi-based themes (e.g. Extra theme). The posts_per_page / pagination fix should also work for all their themes, even if not Divi-based.
 * Version:           1.2.0
 * Extension Class:   Tribe__Extension__Divi_Compatibility
 * Author:            Modern Tribe, Inc.
 * Author URI:        http://m.tri.be/1971
 * License:           GPL version 3 or any later version
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 *
 *     This plugin is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     any later version.
 *
 *     This plugin is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *     GNU General Public License for more details.
 */

// Do not load unless Tribe Common is fully loaded and our class does not yet exist.
if (
	class_exists( 'Tribe__Extension' )
	&& ! class_exists( 'Tribe__Extension__Example' )
) {
	/**
	 * Extension main class, class begins loading on init() function.
	 */
	class Tribe__Extension__Divi_Compatibility extends Tribe__Extension {
		/**
		 * Setup the Extension's properties.
		 *
		 * This always executes even if the required plugins are not present.
		 */
		public function construct() {
			$this->add_required_plugin( 'Tribe__Events__Main' );
		}

		/**
		 * Extension initialization and hooks.
		 */
		public function init() {
			// Load plugin textdomain
			load_plugin_textdomain( 'tribe-ext-divi-compatibility', false, basename( dirname( __FILE__ ) ) . '/languages/' );

			/**
			 * All extensions require PHP 5.6+, following along with https://theeventscalendar.com/knowledgebase/php-version-requirement-changes/
			 */
			$php_required_version = '5.6';

			if ( version_compare( PHP_VERSION, $php_required_version, '<' ) ) {
				if (
					is_admin()
					&& current_user_can( 'activate_plugins' )
				) {
					$message = '<p>';
					$message .= sprintf( __( '%s requires PHP version %s or newer to work. Please contact your website host and inquire about updating PHP.', 'tribe-ext-divi-compatibility' ), $this->get_name(), $php_required_version );
					$message .= sprintf( ' <a href="%1$s">%1$s</a>', 'https://wordpress.org/about/requirements/' );
					$message .= '</p>';
					tribe_notice( $this->get_name(), $message, 'type=error' );
				}

				return;
			}

			if (
				! is_admin()
				&& function_exists( 'et_custom_posts_per_page' )
			) {
				add_filter( 'parse_query', array( $this, 'remove_et_custom_posts_per_page' ), 100 );
			}

			// Checking if Events Calendar PRO is active
			if ( Tribe__Dependency::instance()->is_plugin_active( 'Tribe__Events__Pro__Main' ) ) {
				// If Divi is the active / parent theme load the appropriate styles
				if ( 'Divi' == get_template() ) {
					add_action( 'wp_head', array( $this, 'fix_divi_widget_styles' ) );
				}

				// Needed for is_plugin_active()
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

				// If Divi Builder Plugin is active load the appropriate styles
				if ( is_plugin_active( 'divi-builder/divi-builder.php' ) ) {
					add_action( 'wp_head', array( $this, 'fix_db_widget_styles' ) );
				}
			} // Checking if Events Calendar PRO is active
		}

		/**
		 * Remove Elegant Themes' custom posts per page.
		 *
		 * Applies to ALL themes by Elegant Themes, not just Divi and Divi-based themes
		 *
		 * @see et_custom_posts_per_page()
		 *
		 * @param WP_Query $query
		 */
		public function remove_et_custom_posts_per_page( $query ) {
			if ( $query->tribe_is_event_query ) {
				remove_action( 'pre_get_posts', 'et_custom_posts_per_page' );
			}
		}

		/**
		 * Fix the styling of the Calendar widget and the Advanced List widget
		 *
		 * Applies to the Divi theme when Events Calendar PRO is active
		 */
		public function fix_divi_widget_styles() {
			?>
			<style type="text/css" id="tribe_ext_fix_et_sidebar_style">
				/* Fixing the cell padding of the mini calendar grid */
				#main-content .tribe_mini_calendar_widget th,
				#main-content .tribe_mini_calendar_widget td {
					padding: 2px 0;
				}

				/* Increasing the width of the day / date box in the list to keep day name in one line */
				.et_pb_widget.tribe_mini_calendar_widget .list-date, /* Mini calendar widget */
				.et_pb_widget.tribe-events-adv-list-widget .list-date /* Advanced list widget */
				{
					width: 22%;
					max-width: 45px;
				}

				/* Adjusting the width of the event info box in the list to keep day name in one line */
				.et_pb_widget.tribe_mini_calendar_widget .list-info, /* Mini calendar widget */
				.et_pb_widget.tribe-events-adv-list-widget .list-info /* Advanced list widget */
				{
					width: 73%;
				}

				/* Setting today's date to white to make it visible (only effective if today has an event) */
				.et_pb_widget_area .et_pb_widget .tribe-events-present a {
					color: #fff;
				}

				/* Adjusting the margin and padding of event title in list */
				#main-content .tribe-mini-calendar-event .list-info h2,
				#main-footer .tribe-mini-calendar-event .list-info h2 {
					padding-bottom: 0;
					margin-bottom: 5px;
				}

				/* Adjusting the padding of the day name in the list */
				.et_pb_widget.tribe_mini_calendar_widget .list-dayname {
					padding-top: 0;
					padding-bottom: 0;
				}

				/* Adjusting the line-height of event duration */
				#main-content .et_pb_widget.tribe_mini_calendar_widget .tribe-events-duration,
				#main-footer .et_pb_widget.tribe_mini_calendar_widget .tribe-events-duration {
					line-height: 1.2;
				}

				/* Fixing datepicker z-index on shortcode page */
				.et_fixed_nav .datepicker-orient-top {
					z-index: 99999 !important;
				}
			</style>
			<?php
		} // public function fix_divi_widget_styles()

		/**
		 * Fix the styling of the Calendar widget and the Advanced List widget
		 * when widgets are on a page in a sidebar module
		 *
		 * Applies to the Divi Builder Plugin when Events Calendar PRO is active
		 */
		public function fix_db_widget_styles() {
			?>
			<style type="text/css" id="tribe_ext_fix_et_pb_style">
				/* Fixing the padding of the nav links in the mini calendar widget */
				.et-db #et-boc .et_pb_module a.tribe-mini-calendar-nav-link {
					padding: 5px;
				}

				/* Hiding the spinner and adjusting its position */
				.et-db #et-boc .et_pb_module img#ajax-loading-mini {
					display: none;
					margin: -8px 0 0 -8px;
				}

				/* Fixing the padding in the grid for day with no events */
				#et-boc span.tribe-mini-calendar-no-event {
					padding: 5px 5px 15px 5px;
				}

				/* Fixing the padding in the grid for day with events */
				.et-db #et-boc .et_pb_module a.tribe-mini-calendar-day-link {
					padding: 5px 0 15px 0;
				}

				/* Fixing the color of today's date in the grid */
				.et-db #et-boc .et_pb_module .tribe-events-present a.tribe-mini-calendar-day-link {
					color: #fff;
				}

				/* Fixing the cell padding of the mini calendar grid */
				.widget .tribe-mini-calendar th,
				.widget .tribe-mini-calendar td {
					padding-right: 0;
					padding-left: 0;
				}

				/**
				 * The Divi Builder overrides a lot of the styling of the widgets.
				 * This section resets the styling of the event list in the Mini Calendar
				 * and in the Advanced Event List widgets
				 */
				#et-boc .et_builder_inner_content .tribe-events-adv-list-widget div.type-tribe_events,
				#et-boc .et_builder_inner_content .tribe-mini-calendar-list-wrapper div.type-tribe_events {
					margin: 0 0 4px;
					margin: 0 0 .25rem;
					padding: 0;
				}

				#et-box .et_builder_inner_content div.tribe-mini-calendar-event.first {
					margin-top: 10px;
				}

				#et-boc .et_builder_inner_content div.tribe-mini-calendar-event {
					padding-bottom: 5px;
					margin-bottom: 5px;
					border-bottom: 1px dotted #2f2f2f;
				}

				#et-boc .et_builder_inner_content .tribe-mini-calendar-event div.list-info {
					display: inline;
					float: left;
					margin: 10px 0;
					width: 80%;
				}

				#et-boc .et_builder_inner_content .tribe-mini-calendar-event .list-info h2 {
					font-size: 14px;
					font-weight: bold;
					line-height: 18px;
					margin-top: 0;
					margin-bottom: 10px;
					padding-bottom: 0px;
				}

				#et-boc .et_builder_inner_content .tribe-mini-calendar-event .list-info h2 a {
					font-weight: bold;
				}

				#et-boc .et_builder_inner_content div.tribe-mini-calendar-event .list-date {
					float: left;
					overflow: hidden;
					font-weight: bold;
					margin: 10px 5% 10px 0;
					padding: 3px;
					width: 15%;
					text-align: center;
					display: inline;
					background: #666;
					box-sizing: border-box;
					-moz-box-sizing: border-box;
					-webkit-box-sizing: border-box;
				}

				#et-boc .et_builder_inner_content .tribe-mini-calendar-event .list-date span.list-dayname {
					background: #fff;
					color: #666;
					display: block;
					font-size: 11px;
					letter-spacing: .5px;
					padding: 3px;
					text-align: center;
					text-transform: uppercase;
				}

				#et-boc .et_builder_inner_content .tribe-mini-calendar-event .list-date span.list-daynumber {
					color: white;
					display: block;
					font-size: 15px;
					line-height: 1.6;
					text-align: center;
					width: 100%;
				}
			</style>
			<?php
		} // public function fix_db_widget_styles()

	} // end class
} // end if class_exists check