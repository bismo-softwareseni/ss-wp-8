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
	 * Function to create shortcode for manipulating WP API
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
		$ss_posts_response = wp_remote_get( get_site_url() . '/wp-json/wp/v2/posts?per_page=' . $ss_shortcode_atts ['max_posts'] );

		// -- exit if request error
		if ( is_wp_error( $ss_posts_response ) ) {
			return;
		} else {
			// -- get the results
			$ss_posts_result = json_decode( wp_remote_retrieve_body( $ss_posts_response ) );

			if ( ! empty( $ss_posts_result ) ) {
				foreach ( $ss_posts_result as $ss_post ) {
					?>

			<h5>
				<a href="<?php echo esc_url( get_permalink( $ss_post->id ) ); ?>">
					<?php echo esc_html( $ss_post->title->rendered ); ?>
				</a>
			</h5>

					<?php
				}
			}
		}

		return ob_get_clean();
	}

	/**
	 * Function for executing some task when plugins loaded
	 */
	public function ss_wp8_plugins_loaded_handlers() {
		// -- register wp 8 shortcode to get latest posts
		add_shortcode( 'wp8_api_latest_post', array( $this, 'ss_wp8_crt_shd_latest_post' ) );
	}

}

// -- run the main class
$ss_wp_8_main_class = new SS_WP_8_Main();


