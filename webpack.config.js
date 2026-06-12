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
		undersore: "Underscore"
	},
	plugins: [
		// Feed our bundled Leaflet instance to UMD plugins that expect a global `L`
		// (e.g. leaflet-gesture-handling). Keeps us compatible with L.noConflict()
		// without polluting the page's global scope.
		new webpack.ProvidePlugin({ L: 'leaflet' }),
	],
	devtool: 'source-map'
};
