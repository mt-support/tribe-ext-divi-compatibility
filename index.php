<?php
/**
 * Plugin Name:       The Events Calendar Extension: Divi Theme Compatibility
 * Plugin URI:        https://theeventscalendar.com/extensions/elegant-themes-divi-theme-compatibility/
 * GitHub Plugin URI: https://github.com/mt-support/tribe-ext-divi-theme-compatibility
 * Description:       Makes The Events Calendar compatible with Elegant Themes' Divi theme and Divi-based themes (e.g. Extra theme). The posts_per_page / pagination fix should also work for all their themes, even if not Divi-based.
 * Version:           1.2.0
 * Extension Class:   Tribe__Extension__Divi_Theme_Compatibility
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
	class Tribe__Extension__Divi_Theme_Compatibility extends Tribe__Extension {

		/**
		 * Setup the Extension's properties.
		 *
		 * This always executes even if the required plugins are not present.
		 */
		public function construct() {
			$this->add_required_plugin( 'Tribe__Events__Main' );
			$this->set_url( 'https://theeventscalendar.com/extensions/elegant-themes-divi-theme-compatibility/' );
			$this->set_version( '1.1.0' );
		}

		/**
		 * Extension initialization and hooks.
		 */
		public function init() {
			if ( function_exists( 'et_custom_posts_per_page' ) && ! is_admin() ) {
				add_filter( 'parse_query', array( $this, 'remove_et_custom_posts_per_page' ), 100 );
			}

			add_action( 'wp_head', array( $this, 'fix_et_sidebar_style' ) );
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
		 *  1. Fixing the cell padding of the mini calendar grid
		 *  2. Increasing the width of the day / date box in the list to keep day name in one line
		 *  3. Adjusting the width of the event info box in the list to keep day name in one line
		 *  4. Setting today's date to white to make it visible (only effective if today has an event)
		 *  5. Adjusting the margin and padding of event title in list
		 *  6. Adjusting the padding of the day name in the list
		 *  7. Adjusting the line-height of event duration
		 */
		public function fix_et_sidebar_style() {
			?>
			<style type="text/css" id="tribe_ext_fix_et_sidebar_style">
				#main-content .tribe_mini_calendar_widget th,
				#main-content .tribe_mini_calendar_widget td {
					padding: 2px 0;
				}

				.et_pb_widget.tribe_mini_calendar_widget .list-date {
					width: 22%;
				}

				.et_pb_widget.tribe_mini_calendar_widget .list-info {
					width: 73%;
				}

				.et_pb_widget_area .et_pb_widget .tribe-events-present a {
					color: #fff;
				}

				#main-content .tribe-mini-calendar-event .list-info h2,
				#main-footer .tribe-mini-calendar-event .list-info h2 {
					padding-bottom: 0;
					margin-bottom: 5px;
				}

				.et_pb_widget.tribe_mini_calendar_widget .list-dayname {
					padding-top: 0;
					padding-bottom: 0;
				}

				#main-content .et_pb_widget.tribe_mini_calendar_widget .tribe-events-duration,
				#main-footer .et_pb_widget.tribe_mini_calendar_widget .tribe-events-duration {
					line-height: 1.2;
				}
			</style>
			<?php
		}

	} // end class
} // end if class_exists check