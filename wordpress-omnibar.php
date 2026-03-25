<?php
/**
 * Plugin Name: WordPress Omnibar
 * Plugin URI:  https://github.com/joen/wordpress-omnibar
 * Description: A React-based replacement for the WordPress admin bar.
 * Version:     0.1.0
 * Author:      Joen Asmussen
 * License:     GPL-2.0-or-later
 * Text Domain: wordpress-omnibar
 */

defined( 'ABSPATH' ) || exit;

class WP_Omnibar {

	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
		add_action( 'admin_footer',          [ $this, 'output_data' ], 1 );
	}

	public function enqueue() {
		$plugin_url  = plugin_dir_url( __FILE__ );
		$plugin_path = plugin_dir_path( __FILE__ );

		$asset_file = $plugin_path . 'build/index.asset.php';
		$asset      = file_exists( $asset_file )
			? require $asset_file
			: [ 'dependencies' => [ 'wp-element' ], 'version' => '0.1.0' ];

		wp_enqueue_script(
			'wp-omnibar',
			$plugin_url . 'build/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		$css_path = $plugin_path . 'build/style-index.css';
		if ( file_exists( $css_path ) ) {
			wp_enqueue_style(
				'wp-omnibar',
				$plugin_url . 'build/style-index.css',
				[],
				$asset['version']
			);
		}
	}

	/**
	 * Output site + user data as a global JS variable before footer scripts run.
	 */
	public function output_data() {
		$current_user = wp_get_current_user();

		$update_data   = wp_get_update_data();
		$comment_count = wp_count_comments();

		$data = [
			'siteTitle'    => get_bloginfo( 'name' ),
			'siteUrl'      => get_bloginfo( 'url' ),
			'adminUrl'     => admin_url(),
			'updateCount'  => (int) ( $update_data['counts']['total'] ?? 0 ),
			'commentCount' => (int) ( $comment_count->moderated ?? 0 ),
			'user'         => [
				'id'          => $current_user->ID,
				'firstName'   => $current_user->first_name ?: strtok( $current_user->display_name, ' ' ),
				'displayName' => $current_user->display_name,
				'email'       => $current_user->user_email,
				'avatarUrl'   => get_avatar_url( $current_user->ID, [ 'size' => 32 ] ),
				'profileUrl'  => get_edit_profile_url( $current_user->ID ),
			],
		];

		echo '<script>window.wpOmnibarData = ' . wp_json_encode( $data ) . ';</script>' . "\n";
	}
}

new WP_Omnibar();
