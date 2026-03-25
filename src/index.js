import { createRoot } from 'react-dom/client';
import Omnibar from './components/Omnibar';
import './style.css';

function init() {
	const bar = document.getElementById( 'wpadminbar' );
	if ( ! bar || ! window.wpOmnibarData ) {
		return;
	}

	bar.innerHTML = '';
	createRoot( bar ).render( <Omnibar data={ window.wpOmnibarData } /> );
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', init );
} else {
	init();
}
