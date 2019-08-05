<?php
/**
 * Plugin Name: SoftwareSeni WP Training 8
 * Description: Understand how REST API Works and how to use it
 * Version: 1.0
 * Author: Bismoko Widyatno
 *
 * @package ss-wp-8
 */

/**
 * --------------------------------------------------------------------------
 * Main class for this plugin. This class will handle most of the
 * plugin logic
 * --------------------------------------------------------------------------
 **/
class SS_WP_8_Main {
	/**
	 * Class constructor
	 */
	public function __construct() {
		/**
		* Execute this when plugin activated and have been loaded
		* 1. register shortcodes
		*/
		add_action( 'plugins_loaded', array( $this, 'ss_wp8_plugins_loaded_handlers' ) );
	}

	/**
	 * Function to display latest posts from WP API
	 *
	 * @param int $ss_shortcode_atts max amount posts to show.
	 */
	public function ss_wp8_crt_shd_latest_post( $ss_shortcode_atts = array() ) {
		ob_start();

		// -- add shortcode attribute
		$ss_shortcode_atts = array_change_key_case( (array) $ss_shortcode_atts, CASE_LOWER );

		// -- override default shortcode parameters
		$ss_wp_8_roles_atts = shortcode_atts(
			[
				'max_posts' => 20,
			],
			$ss_shortcode_atts
		);

		// -- show latest posts
		$this->ss_wp8_get_posts( $ss_wp_8_roles_atts ['max_posts'], false );

		return ob_get_clean();
	}
	// -- end function display latest post

	/**
	 * Function to display a post using WP API
	 *
	 * @param int     $ss_max_post Post amount per page.
	 * @param boolean $ss_show_up_del Show update and delete link.
	 */
	public function ss_wp8_get_posts( $ss_max_post, $ss_show_up_del ) {
		$ss_posts_response = wp_remote_get( get_site_url() . '/wp-json/wp/v2/posts?per_page=' . $ss_max_post );

		// -- exit if request error
		if ( is_wp_error( $ss_posts_response ) ) {
			return;
		} else {
			?>

		<!-- post results container -->
		<div class="ajax-post-results-container">
			<?php
				// -- get the results
				$ss_posts_result = json_decode( wp_remote_retrieve_body( $ss_posts_response ) );

			if ( ! empty( $ss_posts_result ) ) {
				foreach ( $ss_posts_result as $ss_post ) {
					?>

				<h5 class="post-<?php echo esc_attr( $ss_post->id ); ?>">
					<a href="<?php echo esc_url( get_permalink( $ss_post->id ) ); ?>">
						<?php echo esc_html( $ss_post->title->rendered ); ?>
					</a>

					<!-- if show update and delete -->
						<?php
						if ( $ss_show_up_del && is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
							?>

						<div>
							<a href="#" class="api-delete-post" data-post-id="<?php echo esc_attr( $ss_post->id ); ?>" style="color: #262626;">delete</a>
							<a href="#" class="api-update-post" data-post-id="<?php echo esc_attr( $ss_post->id ); ?>" style="color: #262626;">update</a>
						</div>

							<?php
						}
						?>
				</h5>

					<?php
				}
			}
			?>
		</div>
		<!-- end ajax post results container -->

		<!-- pagination container -->
		<div class="ajax-pagination-container">
			<?php
				// -- get max posts and max pages
				$ss_max_page = $ss_posts_response['headers']['x-wp-totalpages'];

				// -- set next page
				$ss_next_page = 0;

			if ( $ss_max_page > 1 ) {
				$ss_next_page = 2;
			} else {
				$ss_next_page = 1;
			}
			?>

			<div class="page-number">
				<span class="current-page">1</span>
				<span>of</span>
				<span class="max-page"><?php echo esc_html( $ss_max_page ); ?></span>
			</div>

			<div class="ui large buttons" data-max-page="<?php echo esc_attr( $ss_max_page ); ?>" data-current-page="1" data-post-perpage="<?php echo esc_attr( $ss_max_post ); ?>">
				<button class="ui button left labeled icon button-ajax-pagination prev-page" data-page="1">
					<i class="left arrow icon"></i> Previous Page
				</button>
				<button class="ui button right labeled icon button-ajax-pagination next-page" data-page="<?php echo esc_attr( $ss_next_page ); ?>">
					Next Page <i class="right arrow icon"></i>
				</button>
			</div>
		</div>
		<!-- end pagination container -->

			<?php
		}
	}

	/**
	 * Function to submit a post using WP API ( create shortcode )
	 */
	public function ss_wp8_crt_shd_submit_post() {
		ob_start();

		// -- only show the form to the user that has access and have been logged in
		if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
			?>

		<form class="ss-api-form-submit-post ui form">
			<div class="field">
				<label for="ss-input-post-title">Post Title</label>
				<input required type="text" name="ss-input-post-title" id="ss-input-post-title" class="ss-input-text" value="" style="width:100%;" />
			</div>

			<div class="field">
				<label for="ss-input-post-title">Post Excerpt</label>
				<input required type="text" name="ss-input-post-excerpt" id="ss-input-post-excerpt" class="ss-input-text" value="" style="width:100%;" />
			</div>

			<div class="field">
				<label for="ss-input-post-content">Post Content</label>
				<textarea required name="ss-input-post-content" id="ss-input-post-content" class="ss-input-textarea" style="width:100%;"></textarea>
			</div>

			<button type="submit" name="ss-post-submit" class="ss-button ui button" style="margin-top:10px;">Submit</button>
		</form>

			<?php
		}

		return ob_get_clean();
	}

	/**
	 * Function to update or delete a post using WP API ( create shortcode )
	 */
	public function ss_wp8_crt_shd_del_up_post() {
		ob_start();
		?>

		<div class="post-container">
			<?php
				$this->ss_wp8_get_posts( 20, true );
			?>
		</div>

		<?php
			// -- only show the form to the user that has access and have been logged in
		if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
			?>


		<form class="ss-api-form-update-post ui form" data-post-id="" style="display: none;">
			<h4>Edit Post</h4>

			<div class="field">
				<label for="ss-input-post-title">Post Title</label>
				<input required type="text" name="ss-input-post-title" id="ss-input-post-title" class="ss-input-text" value="" style="width:100%;" />
			</div>

			<div class="field">
				<label for="ss-input-post-title">Post Excerpt</label>
				<input required type="text" name="ss-input-post-excerpt" id="ss-input-post-excerpt" class="ss-input-text" value="" style="width:100%;" />
			</div>

			<div class="field">
				<label for="ss-input-post-content">Post Content</label>
				<textarea required name="ss-input-post-content" id="ss-input-post-content" class="ss-input-textarea" style="width:100%;"></textarea>
			</div>

			<button type="submit" name="ss-post-submit-update" class="ss-button ui button" style="margin-top:10px;">Submit</button>
		</form>

			<?php
		}
		?>

		<?php
		return ob_get_clean();
	}


	/**
	 * Function for importing js script, required for submitting, deleting, and updating posts
	 */
	public function ss_wp8_enqueue_js() {
		// -- js file to submit the post ( insert, update, and delete )
		wp_enqueue_script( 'ss-api-post-submit', plugin_dir_url( __FILE__ ) . '/js/ss-api-post-submit.js', array( 'jquery' ), 'v1.0', true );

		// -- localize the script for ajax call ( insert, update, delete )
		wp_localize_script(
			'ss-api-post-submit',
			'ss_api_post_submit_action',
			array(
				'root'            => esc_url_raw( rest_url() ),
				'nonce'           => wp_create_nonce( 'wp_rest' ),
				'success'         => __( 'Data processed successfully', 'ss-wp8' ),
				'failure'         => __( 'Error.', 'ss-wp8' ),
				'current_user_id' => get_current_user_id(),
			)
		);

		// -- js file to handle pagination
		wp_enqueue_script( 'ss-api-post-pagination', plugin_dir_url( __FILE__ ) . '/js/ss-api-post-pagination.js', array( 'jquery' ), 'v1.0', true );

		// -- localize the pagination script for ajax call
		wp_localize_script(
			'ss-api-post-pagination',
			'ss_api_post_pagination',
			array(
				'root'            => esc_url_raw( rest_url() ),
				'nonce'           => wp_create_nonce( 'wp_rest' ),
				'success'         => __( 'Data processed successfully', 'ss-wp8' ),
				'failure'         => __( 'Error.', 'ss-wp8' ),
				'current_user_id' => get_current_user_id(),
			)
		);
	}

	/**
	 * Function for executing some task when plugins loaded
	 */
	public function ss_wp8_plugins_loaded_handlers() {
		// -- register wp 8 shortcode to get latest posts
		add_shortcode( 'wp8_api_latest_post', array( $this, 'ss_wp8_crt_shd_latest_post' ) );

		// -- register wp 8 shortcode to submit a post
		add_shortcode( 'wp8_api_submit_post', array( $this, 'ss_wp8_crt_shd_submit_post' ) );

		// -- register wp 8 shortcode to edit or delete a post
		add_shortcode( 'wp8_api_update_delete_post', array( $this, 'ss_wp8_crt_shd_del_up_post' ) );

		// -- enqueue scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'ss_wp8_enqueue_js' ) );
	}

}

// -- run the main class
$ss_wp_8_main_class = new SS_WP_8_Main();


