const path = require('path');
const fs = require('node:fs');
const webpack = require('webpack');

const entryPoints = Object.fromEntries( fs.globSync('./src/js/**/index.js').map(entry=>[
	entry.replace(/^src\/js\//,'').replace(/\/index.js$/,''),
	`./${entry}`
]) )

module.exports = {
	entry: entryPoints,
	resolve: {
		modules: [
			path.resolve(__dirname, 'node_modules'),
			path.resolve(__dirname, 'src/js/lib'),
		]
	},
	externals: {
		jquery: "jQuery",
		backbone: "Backbone",
		underscore: "Underscore"
	},
	optimization: {
		// Leaflet core is used by all four entry points. Without this it was
		// inlined into each bundle (~140 KB × 4). Pull it into one chunk the
		// browser caches once and reuses across admin + frontend. The chunk has
		// a stable name so it can be enqueued as a script dependency in PHP.
		// Only `leaflet` itself is shared; the per-context plugins
		// (leaflet-providers, -control-geocoder, -gesture-handling, …) stay in
		// the bundle that actually uses them.
		splitChunks: {
			cacheGroups: {
				leaflet: {
					test: /[\\/]node_modules[\\/]leaflet[\\/]/,
					name: 'acf-osm-leaflet',
					chunks: 'all',
					enforce: true,
				},
			},
		},
	},
	plugins: [
		// Feed our bundled Leaflet instance to UMD plugins that expect a global `L`
		// (e.g. leaflet-gesture-handling). Keeps us compatible with L.noConflict()
		// without polluting the page's global scope.
		new webpack.ProvidePlugin({ L: 'leaflet' }),
	],
	devtool: 'source-map'
};
