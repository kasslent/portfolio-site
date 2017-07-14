<?php


/**
 * Hide or show widgets conditionally.
 */

class Jetpack_Widget_Conditions {
	static $passed_template_redirect = false;

	public static function init() {
		if ( is_admin() ) {
			add_action( 'sidebar_admin_setup', array( __CLASS__, 'widget_admin_setup' ) );
			add_filter( 'widget_update_callback', array( __CLASS__, 'widget_update' ), 10, 3 );
			add_action( 'in_widget_form', array( __CLASS__, 'widget_conditions_admin' ), 10, 3 );
			add_action( 'wp_ajax_widget_conditions_options', array( __CLASS__, 'widget_conditions_options' ) );
			add_action( 'wp_ajax_widget_conditions_has_children', array( __CLASS__, 'widget_conditions_has_children' ) );
		} else if ( ! in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) ) {
			add_filter( 'widget_display_callback', array( __CLASS__, 'filter_widget' ) );
			add_filter( 'sidebars_widgets', array( __CLASS__, 'sidebars_widgets' ) );
			add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ) );
		}
	}

	public static function widget_admin_setup() {
		if( is_rtl() ) {
			wp_enqueue_style( 'widget-conditions', plugins_url( 'widget-conditions/rtl/widget-conditions-rtl.css', __FILE__ ) );
		} else {
			wp_enqueue_style( 'widget-conditions', plugins_url( 'widget-conditions/widget-conditions.css', __FILE__ ) );
		}
		wp_enqueue_style( 'widget-conditions', plugins_url( 'widget-conditions/widget-conditions.css', __FILE__ ) );
		wp_enqueue_script( 'widget-conditions', plugins_url( 'widget-conditions/widget-conditions.js', __FILE__ ), array( 'jquery', 'jquery-ui-core' ), 20140721, true );
	}

	/**
	 * Provided a second level of granularity for widget conditions.
	 */
	public static function widget_conditions_options_echo( $major = '', $minor = '' ) {
		if ( in_array( $major,  array( 'category', 'tag' ) ) && is_numeric( $minor ) ) {
			$minor = self::maybe_get_split_term( $minor, $major );
		}

		switch ( $major ) {
			case 'category':
				?>
				<option value=""><?php _e( 'All category pages', 'jetpack' ); ?></option>
				<?php

				$categories = get_categories( array( 'number' => 1000, 'orderby' => 'count', 'order' => 'DESC' ) );
				usort( $categories, array( __CLASS__, 'strcasecmp_name' ) );

				foreach ( $categories as $category ) {
					?>
					<option value="<?php echo esc_attr( $category->term_id ); ?>" <?php selected( $category->term_id, $minor ); ?>><?php echo esc_html( $category->name ); ?></option>
					<?php
				}
			break;
			case 'loggedin':
				?>
				<option value="loggedin" <?php selected( 'loggedin', $minor ); ?>><?php _e( 'Logged In', 'jetpack' ); ?></option>
				<option value="loggedout" <?php selected( 'loggedout', $minor ); ?>><?php _e( 'Logged Out', 'jetpack' ); ?></option>
				<?php
			break;
			case 'author':
				?>
				<option value=""><?php _e( 'All author pages', 'jetpack' ); ?></option>
				<?php

				foreach ( get_users( array( 'orderby' => 'name', 'exclude_admin' => true ) ) as $author ) {
					?>
					<option value="<?php echo esc_attr( $author->ID ); ?>" <?php selected( $author->ID, $minor ); ?>><?php echo esc_html( $author->display_name ); ?></option>
					<?php
				}
			break;
			case 'role':
				global $wp_roles;

				foreach ( $wp_roles->roles as $role_key => $role ) {
					?>
					<option value="<?php echo esc_attr( $role_key ); ?>" <?php selected( $role_key, $minor ); ?> ><?php echo esc_html( $role['name'] ); ?></option>
					<?php
				}
			break;
			case 'tag':
				?>
				<option value=""><?php _e( 'All tag pages', 'jetpack' ); ?></option>
				<?php

				$tags = get_tags( array( 'number' => 1000, 'orderby' => 'count', 'order' => 'DESC' ) );
				usort( $tags, array( __CLASS__, 'strcasecmp_name' ) );

				foreach ( $tags as $tag ) {
					?>
					<option value="<?php echo esc_attr($tag->term_id ); ?>" <?php selected( $tag->term_id, $minor ); ?>><?php echo esc_html( $tag->name ); ?></option>
					<?php
				}
			break;
			case 'date':
				?>
				<option value="" <?php selected( '', $minor ); ?>><?php _e( 'All date archives', 'jetpack' ); ?></option>
				<option value="day"<?php selected( 'day', $minor ); ?>><?php _e( 'Daily archives', 'jetpack' ); ?></option>
				<option value="month"<?php selected( 'month', $minor ); ?>><?php _e( 'Monthly archives', 'jetpack' ); ?></option>
				<option value="year"<?php selected( 'year', $minor ); ?>><?php _e( 'Yearly archives', 'jetpack' ); ?></option>
				<?php
			break;
			case 'page':
				// Previously hardcoded post type options.
				if ( ! $minor )
					$minor = 'post_type-page';
				else if ( 'post' == $minor )
					$minor = 'post_type-post';

				?>
				<option value="front" <?php selected( 'front', $minor ); ?>><?php _e( 'Front page', 'jetpack' ); ?></option>
				<option value="posts" <?php selected( 'posts', $minor ); ?>><?php _e( 'Posts page', 'jetpack' ); ?></option>
				<option value="archive" <?php selected( 'archive', $minor ); ?>><?php _e( 'Archive page', 'jetpack' ); ?></option>
				<option value="404" <?php selected( '404', $minor ); ?>><?php _e( '404 error page', 'jetpack' ); ?></option>
				<option value="search" <?php selected( 'search', $minor ); ?>><?php _e( 'Search results', 'jetpack' ); ?></option>
				<optgroup label="<?php esc_attr_e( 'Post type:', 'jetpack' ); ?>">
					<?php

					$post_types = get_post_types( array( 'public' => true ), 'objects' );

					foreach ( $post_types as $post_type ) {
						?>
						<option value="<?php echo esc_attr( 'post_type-' . $post_type->name ); ?>" <?php selected( 'post_type-' . $post_type->name, $minor ); ?>><?php echo esc_html( $post_type->labels->singular_name ); ?></option>
						<?php
					}

					?>
				</optgroup>
				<optgroup label="<?php esc_attr_e( 'Static page:', 'jetpack' ); ?>">
					<?php

					echo str_replace( ' value="' . esc_attr( $minor ) . '"', ' value="' . esc_attr( $minor ) . '" selected="selected"', preg_replace( '/<\/?select[^>]*?>/i', '', wp_dropdown_pages( array( 'echo' => false ) ) ) );

					?>
				</optgroup>
				<?php
			break;
			case 'taxonomy':
				?>
				<option value=""><?php _e( 'All taxonomy pages', 'jetpack' ); ?></option>
				<?php

				$taxonomies = get_taxonomies( array( '_builtin' => false ), 'objects' );
				usort( $taxonomies, array( __CLASS__, 'strcasecmp_name' ) );

				$parts = explode( '_tax_', $minor );

				if ( 2 === count( $parts ) ) {
					$minor_id = self::maybe_get_split_term( $parts[1], $parts[0] );
					$minor = $parts[0] . '_tax_' . $minor_id;
				}

				foreach ( $taxonomies as $taxonomy ) {
					?>
					<optgroup label="<?php esc_attr_e( $taxonomy->labels->name . ':', 'jetpack' ); ?>">
						<option value="<?php echo esc_attr( $taxonomy->name ); ?>" <?php selected( $taxonomy->name, $minor ); ?>>
							<?php _e( 'All pages', 'jetpack' ); ?>
						</option>
					<?php

					$terms = get_terms( array( $taxonomy->name ), array( 'number' => 250, 'hide_empty' => false ) );
					foreach ( $terms as $term ) {
						?>
						<option value="<?php echo esc_attr( $taxonomy->name . '_tax_' . $term->term_id ); ?>" <?php selected( $taxonomy->name . '_tax_' . $term->term_id, $minor ); ?>><?php echo esc_html( $term->name ); ?></option>
						<?php
					}

					?>
				</optgroup>
				<?php
				}
			break;
		}
	}

	/**
	 * This is the AJAX endpoint for the second level of conditions.
	 */
	public static function widget_conditions_options() {
		self::widget_conditions_options_echo( $_REQUEST['major'], isset( $_REQUEST['minor'] ) ? $_REQUEST['minor'] : '' );
		die;
	}

	/**
	 * Provide an option to include children of pages.
	 */
	public static function widget_conditions_has_children_echo( $major = '', $minor = '', $has_children = false ) {
		if ( ! $major || 'page' !== $major || ! $minor ) {
			return null;
		}

		if ( 'front' == $minor ) {
			$minor = get_option( 'page_on_front' );
		}

		if ( ! is_numeric( $minor ) ) {
			return null;
		}

		$page_children = get_pages( array( 'child_of' => (int) $minor ) );

		if ( $page_children ) {
		?>
			<label>
				<input type="checkbox" id="include_children" name="conditions[page_children][]" value="has" <?php checked( $has_children, true ); ?> />
				<?php echo esc_html_x( "Include children", 'Checkbox on Widget Visibility if choosen page has children.', 'jetpack' ); ?>
			</label>
		<?php
		}
	}

	/**
	 * This is the AJAX endpoint for the has_children input.
	 */
	public static function widget_conditions_has_children() {
		self::widget_conditions_has_children_echo( $_REQUEST['major'], isset( $_REQUEST['minor'] ) ? $_REQUEST['minor'] : '', isset( $_REQUEST['has_children'] ) ? $_REQUEST['has_children'] : false );
		die;
	}

	/**
	 * Add the widget conditions to each widget in the admin.
	 *
	 * @param $widget unused.
	 * @param $return unused.
	 * @param array $instance The widget settings.
	 */
	public static function widget_conditions_admin( $widget, $return, $instance ) {
		$conditions = array();

		if ( isset( $instance['conditions'] ) )
			$conditions = $instance['conditions'];

		if ( ! isset( $conditions['action'] ) )
			$conditions['action'] = 'show';

		if ( empty( $conditions['rules'] ) )
			$conditions['rules'][] = array( 'major' => '', 'minor' => '', 'has_children' => '' );

		?>
		<div class="widget-conditional <?php if ( empty( $_POST['widget-conditions-visible'] ) || $_POST['widget-conditions-visible'] == '0' ) { ?>widget-conditional-hide<?php } ?>">
			<input type="hidden" name="widget-conditions-visible" value="<?php if ( isset( $_POST['widget-conditions-visible'] ) ) { echo esc_attr( $_POST['widget-conditions-visible'] ); } else { ?>0<?php } ?>" />
			<?php if ( ! isset( $_POST['widget-conditions-visible'] ) ) { ?><a href="#" class="button display-options"><?php _e( 'Visibility', 'jetpack' ); ?></a><?php } ?>
			<div class="widget-conditional-inner">
				<div class="condition-top">
					<?php printf( _x( '%s if:', 'placeholder: dropdown menu to select widget visibility; hide if or show if', 'jetpack' ), '<select name="conditions[action]"><option value="show" ' . selected( $conditions['action'], 'show', false ) . '>' . esc_html_x( 'Show', 'Used in the "%s if:" translation for the widget visibility dropdown', 'jetpack' ) . '</option><option value="hide" ' . selected( $conditions['action'], 'hide', false ) . '>' . esc_html_x( 'Hide', 'Used in the "%s if:" translation for the widget visibility dropdown', 'jetpack' ) . '</option></select>' ); ?>
				</div><!-- .condition-top -->

				<div class="conditions">
					<?php

					foreach ( $conditions['rules'] as $rule ) {
						$rule = wp_parse_args( $rule, array( 'major' => '', 'minor' => '', 'has_children' => '' ) );
						?>
						<div class="condition">
							<div class="selection alignleft">
								<select class="conditions-rule-major" name="conditions[rules_major][]">
									<option value="" <?php selected( "", $rule['major'] ); ?>><?php echo esc_html_x( '-- Select --', 'Used as the default option in a dropdown list', 'jetpack' ); ?></option>
									<option value="category" <?php selected( "category", $rule['major'] ); ?>><?php esc_html_e( 'Category', 'jetpack' ); ?></option>
									<option value="author" <?php selected( "author", $rule['major'] ); ?>><?php echo esc_html_x( 'Author', 'Noun, as in: "The author of this post is..."', 'jetpack' ); ?></option>

									<?php if( ! ( defined( 'IS_WPCOM' ) && IS_WPCOM ) ) { // this doesn't work on .com because of caching ?>
										<option value="loggedin" <?php selected( "loggedin", $rule['major'] ); ?>><?php echo esc_html_x( 'User', 'Noun', 'jetpack' ); ?></option>
										<option value="role" <?php selected( "role", $rule['major'] ); ?>><?php echo esc_html_x( 'Role', 'Noun, as in: "The user role of that can access this widget is..."', 'jetpack' ); ?></option>
									<?php } ?>

									<option value="tag" <?php selected( "tag", $rule['major'] ); ?>><?php echo esc_html_x( 'Tag', 'Noun, as in: "This post has one tag."', 'jetpack' ); ?></option>
									<option value="date" <?php selected( "date", $rule['major'] ); ?>><?php echo esc_html_x( 'Date', 'Noun, as in: "This page is a date archive."', 'jetpack' ); ?></option>
									<option value="page" <?php selected( "page", $rule['major'] ); ?>><?php echo esc_html_x( 'Page', 'Example: The user is looking at a page, not a post.', 'jetpack' ); ?></option>

									<?php if ( get_taxonomies( array( '_builtin' => false ) ) ) : ?>
										<option value="taxonomy" <?php selected( "taxonomy", $rule['major'] ); ?>><?php echo esc_html_x( 'Taxonomy', 'Noun, as in: "This post has one taxonomy."', 'jetpack' ); ?></option>
									<?php endif; ?>
								</select>

								<?php _ex( 'is', 'Widget Visibility: {Rule Major [Page]} is {Rule Minor [Search results]}', 'jetpack' ); ?>

								<select class="conditions-rule-minor" name="conditions[rules_minor][]" <?php if ( ! $rule['major'] ) { ?> disabled="disabled"<?php } ?> data-loading-text="<?php esc_attr_e( 'Loading...', 'jetpack' ); ?>">
									<?php self::widget_conditions_options_echo( $rule['major'], $rule['minor'] ); ?>
								</select>

								<span class="conditions-rule-has-children">
									<?php self::widget_conditions_has_children_echo( $rule['major'], $rule['minor'], $rule['has_children'] ); ?>
								</span>
							</div>

							<div class="condition-control">
								<span class="condition-conjunction"><?php echo esc_html_x( 'or', 'Shown between widget visibility conditions.', 'jetpack' ); ?></span>
								<div class="actions alignright">
									<a href="#" class="delete-condition"><?php esc_html_e( 'Delete', 'jetpack' ); ?></a> | <a href="#" class="add-condition"><?php esc_html_e( 'Add', 'jetpack' ); ?></a>
								</div>
							</div>

						</div><!-- .condition -->
						<?php
					}

					?>
				</div><!-- .conditions -->
			</div><!-- .widget-conditional-inner -->
		</div><!-- .widget-conditional -->
		<?php
	}

	/**
	 * On an AJAX update of the widget settings, process the display conditions.
	 *
	 * @param array $new_instance New settings for this instance as input by the user.
	 * @param array $old_instance Old settings for this instance.
	 * @return array Modified settings.
	 */
	public static function widget_update( $instance, $new_instance, $old_instance ) {
		if ( empty( $_POST['conditions'] ) ) {
			return $instance;
		}

		$conditions = array();
		$conditions['action'] = $_POST['conditions']['action'];
		$conditions['rules'] = array();

		foreach ( $_POST['conditions']['rules_major'] as $index => $major_rule ) {
			if ( ! $major_rule )
				continue;

			$conditions['rules'][] = array(
				'major' => $major_rule,
				'minor' => isset( $_POST['conditions']['rules_minor'][$index] ) ? $_POST['conditions']['rules_minor'][$index] : '',
				'has_children' => isset( $_POST['conditions']['page_children'][$index] ) ? true : false,
			);
		}

		if ( ! empty( $conditions['rules'] ) )
			$instance['conditions'] = $conditions;
		else
			unset( $instance['conditions'] );

		if (
				( isset( $instance['conditions'] ) && ! isset( $old_instance['conditions'] ) )
				||
				(
					isset( $instance['conditions'], $old_instance['conditions'] )
					&&
					serialize( $instance['conditions'] ) != serialize( $old_instance['conditions'] )
				)
			) {

			/**
			 * Fires after the widget visibility conditions are saved.
			 *
			 * @module widget-visibility
			 *
			 * @since 2.4.0
			 */
			do_action( 'widget_conditions_save' );
		}
		else if ( ! isset( $instance['conditions'] ) && isset( $old_instance['conditions'] ) ) {

			/**
			 * Fires after the widget visibility conditions are deleted.
			 *
			 * @module widget-visibility
			 *
			 * @since 2.4.0
			 */
			do_action( 'widget_conditions_delete' );
		}

		return $instance;
	}

	/**
	 * Filter the list of widgets for a sidebar so that active sidebars work as expected.
	 *
	 * @param array $widget_areas An array of widget areas and their widgets.
	 * @return array The modified $widget_area array.
	 */
	public static function sidebars_widgets( $widget_areas ) {
		$settings = array();

		foreach ( $widget_areas as $widget_area => $widgets ) {
			if ( empty( $widgets ) )
				continue;

			if ( ! is_array( $widgets ) )
				continue;

			if ( 'wp_inactive_widgets' == $widget_area )
				continue;

			foreach ( $widgets as $position => $widget_id ) {
				// Find the conditions for this widget.
				if ( preg_match( '/^(.+?)-(\d+)$/', $widget_id, $matches ) ) {
					$id_base = $matches[1];
					$widget_number = intval( $matches[2] );
				}
				else {
					$id_base = $widget_id;
					$widget_number = null;
				}

				if ( ! isset( $settings[$id_base] ) ) {
					$settings[$id_base] = get_option( 'widget_' . $id_base );
				}

				// New multi widget (WP_Widget)
				if ( ! is_null( $widget_number ) ) {
					if ( isset( $settings[$id_base][$widget_number] ) && false === self::filter_widget( $settings[$id_base][$widget_number] ) ) {
						unset( $widget_areas[$widget_area][$position] );
					}
				}

				// Old single widget
				else if ( ! empty( $settings[ $id_base ] ) && false === self::filter_widget( $settings[$id_base] ) ) {
					unset( $widget_areas[$widget_area][$position] );
				}
			}
		}

		return $widget_areas;
	}

	public static function template_redirect() {
		self::$passed_template_redirect = true;
	}

	/**
	 * Generates a condition key based on the rule array
	 *
	 * @param array $rule
	 * @return string key used to retrieve the condition.
	 */
	static function generate_condition_key( $rule ) {
		if ( isset( $rule['has_children'] ) ) {
			return $rule['major'] . ":" . $rule['minor'] . ":" . $rule['has_children'];
		}
		return $rule['major'] . ":" . $rule['minor'];
	}

	/**
	 * Determine whether the widget should be displayed based on conditions set by the user.
	 *
	 * @param array $instance The widget settings.
	 * @return array Settings to display or bool false to hide.
	 */
	public static function filter_widget( $instance ) {
		global $wp_query;

		if ( empty( $instance['conditions'] ) || empty( $instance['conditions']['rules'] ) )
			return $instance;

		// Store the results of all in-page condition lookups so that multiple widgets with
		// the same visibility conditions don't result in duplicate DB queries.
		static $condition_result_cache = array();

		$condition_result = false;

		foreach ( $instance['conditions']['rules'] as $rule ) {
			$condition_key = self::generate_condition_key( $rule );

			if ( isset( $condition_result_cache[ $condition_key ] ) ) {
				$condition_result = $condition_result_cache[ $condition_key ];
			}
			else {
				switch ( $rule['major'] ) {
					case 'date':
						switch ( $rule['minor'] ) {
							case '':
								$condition_result = is_date();
							break;
							case 'month':
								$condition_result = is_month();
							break;
							case 'day':
								$condition_result = is_day();
							break;
							case 'year':
								$condition_result = is_year();
							break;
						}
					break;
					case 'page':
						// Previously hardcoded post type options.
						if ( 'post' == $rule['minor'] )
							$rule['minor'] = 'post_type-post';
						else if ( ! $rule['minor'] )
							$rule['minor'] = 'post_type-page';

						switch ( $rule['minor'] ) {
							case '404':
								$condition_result = is_404();
							break;
							case 'search':
								$condition_result = is_search();
							break;
							case 'archive':
								$condition_result = is_archive();
							break;
							case 'posts':
								$condition_result = $wp_query->is_posts_page;
							break;
							case 'home':
								$condition_result = is_home();
							break;
							case 'front':
								if ( current_theme_supports( 'infinite-scroll' ) )
									$condition_result = is_front_page();
								else {
									$condition_result = is_front_page() && !is_paged();
								}
							break;
							default:
								if ( substr( $rule['minor'], 0, 10 ) == 'post_type-' ) {
									$condition_result = is_singular( substr( $rule['minor'], 10 ) );
								} elseif ( $rule['minor'] == get_option( 'page_for_posts' ) ) {
									// If $rule['minor'] is a page ID which is also the posts page
									$condition_result = $wp_query->is_posts_page;
								} else {
									// $rule['minor'] is a page ID
									$condition_result = is_page() && ( $rule['minor'] == get_the_ID() );
									
									// Check if $rule['minor'] is parent of page ID
									if ( ! $condition_result && isset( $rule['has_children'] ) && $rule['has_children'] )
										$condition_result = wp_get_post_parent_id( get_the_ID() ) == $rule['minor'];
								}
							break;
						}
					break;
					case 'tag':
						if ( ! $rule['minor'] && is_tag() ) {
							$condition_result = true;
						} else {
							$rule['minor'] = self::maybe_get_split_term( $rule['minor'], $rule['major'] );
							if ( is_singular() && $rule['minor'] && has_tag( $rule['minor'] ) ) {
								$condition_result = true;
							} else {
								$tag = get_tag( $rule['minor'] );
								if ( $tag && ! is_wp_error( $tag ) && is_tag( $tag->slug ) ) {
									$condition_result = true;
								}
							}
						}
					break;
					case 'category':
						if ( ! $rule['minor'] && is_category() ) {
							$condition_result = true;
						} else {
							$rule['minor'] = self::maybe_get_split_term( $rule['minor'], $rule['major'] );
							if ( is_category( $rule['minor'] ) ) {
								$condition_result = true;
							} else if ( is_singular() && $rule['minor'] && in_array( 'category', get_post_taxonomies() ) &&  has_category( $rule['minor'] ) )
								$condition_result = true;
						}
					break;
					case 'loggedin':
						$condition_result = is_user_logged_in();
						if ( 'loggedin' !== $rule['minor'] ) {
						    $condition_result = ! $condition_result;
						}
					break;
					case 'author':
						$post = get_post();
						if ( ! $rule['minor'] && is_author() )
							$condition_result = true;
						else if ( $rule['minor'] && is_author( $rule['minor'] ) )
							$condition_result = true;
						else if ( is_singular() && $rule['minor'] && $rule['minor'] == $post->post_author )
							$condition_result = true;
					break;
					case 'role':
						if( is_user_logged_in() ) {
							$current_user = wp_get_current_user();

							$user_roles = $current_user->roles;

							if( in_array( $rule['minor'], $user_roles ) ) {
								$condition_result = true;
							} else {
								$condition_result = false;
							}

						} else {
							$condition_result = false;
						}
					break;
					case 'taxonomy':
						$term = explode( '_tax_', $rule['minor'] ); // $term[0] = taxonomy name; $term[1] = term id
						if ( isset( $term[0] ) && isset( $term[1] ) ) {
							$term[1] = self::maybe_get_split_term( $term[1], $term[0] );
						}
						if ( isset( $term[1] ) && is_tax( $term[0], $term[1] ) )
							$condition_result = true;
						else if ( isset( $term[1] ) && is_singular() && $term[1] && has_term( $term[1], $term[0] ) )
							$condition_result = true;
						else if ( is_singular() && $post_id = get_the_ID() ){
							$terms = get_the_terms( $post_id, $rule['minor'] ); // Does post have terms in taxonomy?
							if( $terms && ! is_wp_error( $terms ) ) {
								$condition_result = true;
							}
						}
					break;
				}

				if ( $condition_result || self::$passed_template_redirect ) {
					// Some of the conditions will return false when checked before the template_redirect
					// action has been called, like is_page(). Only store positive lookup results, which
					// won't be false positives, before template_redirect, and everything after.
					$condition_result_cache[ $condition_key ] = $condition_result;
				}
			}

			if ( $condition_result )
				break;
		}

		if ( ( 'show' == $instance['conditions']['action'] && ! $condition_result ) || ( 'hide' == $instance['conditions']['action'] && $condition_result ) )
			return false;

		return $instance;
	}

	public static function strcasecmp_name( $a, $b ) {
		return strcasecmp( $a->name, $b->name );
	}

	public static function maybe_get_split_term( $old_term_id = '', $taxonomy = '' ) {
		$term_id = $old_term_id;

		if ( 'tag' == $taxonomy ) {
			$taxonomy = 'post_tag';
		}

		if ( function_exists( 'wp_get_split_term' ) && $new_term_id = wp_get_split_term( $old_term_id, $taxonomy ) ) {
			$term_id = $new_term_id;
		}

		return $term_id;
	}
}

add_action( 'init', array( 'Jetpack_Widget_Conditions', 'init' ) );
