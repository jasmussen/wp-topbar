import { displayShortcut } from '@wordpress/keycodes';
import { Icon, update, comment, help } from '@wordpress/icons';
import WpLogo from './WpLogo';

function openCommandCenter() {
	window.wp?.data?.dispatch( 'core/commands' )?.open();
}

function OmnibarItem( { href, icon, label, count, onClick, className = '' } ) {
	const Tag = href ? 'a' : 'button';
	const hasText = !! label;
	return (
		<Tag href={ href } onClick={ onClick } className={ `wp-omnibar__item ${ hasText ? 'wp-omnibar__item--text' : '' } ${ className }`.trim() }>
			{ icon && <Icon icon={ icon } size={ 24 } /> }
			{ label && <span className="wp-omnibar__item-label">{ label }</span> }
			{ count !== undefined && <span className="wp-omnibar__item-count">{ count }</span> }
		</Tag>
	);
}

export default function Omnibar( { data } ) {
	const { siteTitle, siteUrl, adminUrl, updateCount, commentCount, contextualLinks = [], /* pluginNodes, */ user } = data;

	return (
		<div className="wp-omnibar">

			{ /* Left */ }
			<a href={ adminUrl } className="wp-omnibar__wp-logo" aria-label="About WordPress">
				<WpLogo />
			</a>

			<OmnibarItem href={ siteUrl } label={ siteTitle } />

			<OmnibarItem label={ displayShortcut.primary( 'k' ) } onClick={ openCommandCenter } />

			<OmnibarItem href={ `${ adminUrl }post-new.php` } label="New" />

			{ /* Contextual edit links — frontend only (Edit Page, Edit Site, etc.) */ }
			{ contextualLinks.map( ( link ) => (
				<OmnibarItem key={ link.id } href={ link.href } label={ link.title } />
			) ) }

			{ /*
			 * Plugin nodes — items registered by third-party plugins via $wp_admin_bar->add_node().
			 * Collected in PHP (see wordpress-omnibar.php: collect_plugin_nodes), passed as pluginNodes[].
			 * Each node: { id, title (may contain HTML), href, class }.
			 * Rendered after the spacer so they appear between our left items and the right-side icons.
			 * Kept here for reference while we decide how to handle third-party slots.
			 *
			 * NOTE: ⌘K (core/commands command palette) also surfaces through this slot when
			 * Gutenberg is active — worth considering whether to handle it specially.
			 */ }
			<div className="wp-omnibar__spacer" />
			{ /* pluginNodes.map( ( node ) => {
				const Tag = node.href ? 'a' : 'span';
				return (
					<Tag
						key={ node.id }
						href={ node.href }
						className={ `wp-omnibar__item wp-omnibar__item--text wp-omnibar__plugin-node ${ node.class }`.trim() }
						dangerouslySetInnerHTML={ { __html: node.title } }
					/>
				);
			} ) */ }

			{ /* Right */ }
			<OmnibarItem href={ `${ adminUrl }update-core.php` } icon={ update } count={ updateCount } />

			<OmnibarItem href={ `${ adminUrl }edit-comments.php` } icon={ comment } count={ commentCount } />

			<OmnibarItem icon={ help } />

			<a href={ user.profileUrl } className="wp-omnibar__item wp-omnibar__user">
				<div className="wp-omnibar__avatar-wrap">
					<img
						className="wp-omnibar__avatar"
						src={ user.avatarUrl }
						alt={ user.displayName }
						width={ 20 }
						height={ 20 }
					/>
				</div>
			</a>

		</div>
	);
}
