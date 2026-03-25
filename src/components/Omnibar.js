import { useDispatch } from '@wordpress/data';
import { displayShortcut } from '@wordpress/keycodes';
import { store as commandsStore } from '@wordpress/commands';
import { Icon, update, comment, help } from '@wordpress/icons';
import WpLogo from './WpLogo';

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
	const { siteTitle, adminUrl, updateCount, commentCount, user } = data;
	const { open: openCommandCenter } = useDispatch( commandsStore );

	return (
		<div className="wp-omnibar">

			{ /* Left */ }
			<a href={ adminUrl } className="wp-omnibar__wp-logo" aria-label="About WordPress">
				<WpLogo />
			</a>

			<OmnibarItem href={ adminUrl } label={ siteTitle } />

			<OmnibarItem label={ displayShortcut.primary( 'k' ) } onClick={ openCommandCenter } />

			<OmnibarItem href={ `${ adminUrl }post-new.php` } label="New" />

			{ /* Spacer */ }
			<div className="wp-omnibar__spacer" />

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
