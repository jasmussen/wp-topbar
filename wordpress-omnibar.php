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

	/**
	 * Top-level nodes added by plugins, collected after the admin bar is built.
	 */
	private array $plugin_nodes = [];

	/**
	 * Frontend contextual edit links (Edit Page, Edit Site, etc.),
	 * collected from $wp_admin_bar after WordPress computes them.
	 */
	private array $contextual_links = [];

	/**
	 * Core node IDs that we handle ourselves in React — exclude from plugin nodes.
	 */
	private const CORE_NODES = [
		'wp-logo', 'about', 'wporg', 'documentation', 'support-forums', 'feedback',
		'site-name', 'view-site', 'edit-site', 'dashboard',
		'updates',
		'comments',
		'new-content', 'add-post', 'add-page', 'add-media', 'add-user', 'add-link',
		'top-secondary', 'search',
		'my-account', 'user-actions', 'user-info', 'edit-profile', 'logout',
		'menu-toggle',
	];

	public function __construct() {
		// Collect third-party plugin nodes — disabled while we decide how to handle plugin slots.
		// add_action( 'wp_before_admin_bar_render', [ $this, 'collect_plugin_nodes' ], PHP_INT_MAX );

		// Frontend-only: collect contextual edit links (Edit Page, Edit Site).
		if ( ! is_admin() ) {
			add_action( 'wp_before_admin_bar_render', [ $this, 'collect_contextual_links' ], PHP_INT_MAX );
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
		add_action( 'admin_footer',          [ $this, 'output_data' ], 1 );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
		add_action( 'wp_footer',          [ $this, 'output_data' ], 1 );
	}

	/**
	 * Collect frontend contextual edit links from the nodes WordPress itself adds.
	 * We read 'edit' (Edit Page / Edit Post / Edit Term) and 'site-editor' (Edit Site)
	 * directly from $wp_admin_bar so we inherit all of WordPress's capability and
	 * context checks for free, without reimplementing them.
	 */
	public function collect_contextual_links() {
		global $wp_admin_bar;

		if ( ! $wp_admin_bar ) {
			return;
		}

		// Node IDs WordPress adds for frontend contextual editing.
		$contextual_ids = [ 'edit', 'site-editor' ];

		foreach ( $contextual_ids as $id ) {
			$node = $wp_admin_bar->get_node( $id );
			if ( ! $node || empty( $node->href ) ) {
				continue;
			}
			$this->contextual_links[] = [
				'id'    => $id,
				'title' => wp_strip_all_tags( $node->title ),
				'href'  => $node->href,
			];
		}
	}

	/**
	 * After all plugins have added their nodes, collect the non-core top-level ones.
	 */
	public function collect_plugin_nodes() {
		global $wp_admin_bar;

		if ( ! $wp_admin_bar ) {
			return;
		}

		foreach ( $wp_admin_bar->get_nodes() as $id => $node ) {
			// Top-level only (no parent, or parent is root).
			if ( ! empty( $node->parent ) ) {
				continue;
			}
			if ( in_array( $id, self::CORE_NODES, true ) ) {
				continue;
			}

			$this->plugin_nodes[] = [
				'id'    => $id,
				'title' => $node->title,
				'href'  => $node->href ?: null,
				'class' => $node->meta['class'] ?? '',
			];
		}
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
			'contextualLinks' => $this->contextual_links,
			// 'pluginNodes'  => $this->plugin_nodes, // disabled — see collect_plugin_nodes()
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
		echo '<style>@media screen and (max-width:782px){html{--wp-admin--admin-bar--height:32px}html #wpadminbar{height:32px!important;min-height:32px!important}#wpadminbar *{font-size:13px!important;line-height:1!important}#wpadminbar .wp-omnibar__search{position:static;transform:none;width:auto;background:none;padding:0 4px;order:90}#wpadminbar .wp-omnibar__search svg{width:24px!important;height:24px!important;opacity:1!important}#wpadminbar .wp-omnibar__search-label,#wpadminbar .wp-omnibar__search-shortcut{display:none!important}}</style>' . "\n";
	}
}

new WP_Omnibar();
