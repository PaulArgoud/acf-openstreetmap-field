/**
 * Drift check between the plugin's etc/leaflet-providers.json and the upstream
 * `leaflet-providers` package.
 *
 * It is REPORT-ONLY on purpose: it never rewrites the JSON. Tile URLs are
 * coupled to their options/placeholders (e.g. a URL may reference `{apikey}`
 * while the plugin stores `{key}`) and the plugin adds its own keys such as
 * `isOverlay`, so a blind auto-refresh could silently break a provider. Instead
 * this surfaces what changed so a maintainer can apply the relevant updates by
 * hand — the goal being to catch upstream drift early (a discontinued provider,
 * a moved tile URL, …) and avoid the next "Stamen".
 *
 * Run: `npm run providers`
 */

// Leaflet touches browser globals at load time; minimal stubs let us read the
// providers data headlessly (the data itself is plain config objects).
global.window = global;
global.self = global;
global.window.devicePixelRatio = 1;
global.window.screen = { deviceXDPI: 1, logicalXDPI: 1 };
global.navigator = { userAgent: '', platform: '', maxTouchPoints: 0 };
global.document = {
	documentElement: { style: {} },
	createElement: () => ( { style: {}, setAttribute() {}, appendChild() {}, getContext() { return null; } } ),
	createElementNS: () => ( { style: {} } ),
	addEventListener() {}, removeEventListener() {},
};

const fs = require( 'node:fs' );
const path = require( 'node:path' );

const L = require( 'leaflet' );
require( 'leaflet-providers' );
const upstream = L.TileLayer.Provider.providers;

const jsonPath = path.join( __dirname, '..', '..', 'etc', 'leaflet-providers.json' );
const current = JSON.parse( fs.readFileSync( jsonPath, 'utf8' ) );

const urlChanges = [];
const compareUrl = ( target, source, label ) => {
	if ( source && typeof source.url === 'string' && typeof target.url === 'string' && target.url !== source.url ) {
		urlChanges.push( { label, from: target.url, to: source.url } );
	}
};

for ( const key of Object.keys( current ) ) {
	const up = upstream[ key ];
	if ( ! up ) {
		continue;
	}
	compareUrl( current[ key ], up, key );
	if ( current[ key ].variants ) {
		for ( const vk of Object.keys( current[ key ].variants ) ) {
			const variant = current[ key ].variants[ vk ];
			const upVariant = up.variants && up.variants[ vk ];
			if ( variant && typeof variant === 'object' && upVariant && typeof upVariant === 'object' ) {
				compareUrl( variant, upVariant, `${key}.${vk}` );
			}
		}
	}
}

const removedUpstream = Object.keys( current ).filter( ( k ) => ! upstream[ k ] );
const addedUpstream = Object.keys( upstream ).filter( ( k ) => ! current[ k ] );

const heading = ( s ) => console.log( `\n${s}` );

console.log( 'leaflet-providers drift report (etc/leaflet-providers.json vs upstream)\n' );

heading( `Tile URL changed upstream (${urlChanges.length}) — apply by hand if the options still match:` );
if ( urlChanges.length ) {
	urlChanges.forEach( ( c ) => console.log( `  • ${c.label}\n      - ${c.from}\n      + ${c.to}` ) );
} else {
	console.log( '  none' );
}

heading( `Removed upstream (${removedUpstream.length}) — candidates to drop / migrate:` );
console.log( '  ' + ( removedUpstream.join( ', ' ) || 'none' ) );

heading( `New upstream (${addedUpstream.length}) — candidates to add:` );
console.log( '  ' + ( addedUpstream.join( ', ' ) || 'none' ) );

console.log( '\nThis report does not modify any file.' );
