=== ACF OpenStreetMap Field ===
Contributors: podpirate
Donate link: https://donate.openstreetmap.org/
Tags: map acf openstreetmap leaflet
Requires at least: 5.5
Requires PHP: 8.0
Tested up to: 7.0
Stable tag: 1.7.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Configurable OpenStreetMap / Leaflet map field for ACF — markers, many tile providers, address geocoding and a privacy-friendly map proxy.

== Description ==

Hazzle free OpenStreetMap with [ACF](https://www.advancedcustomfields.com/).

## Usage

#### In the Fieldgroup editor:

**Return Format:**

 - *Raw data* will return an array holding the field configuration.

 - *Leaflet JS* will return a fully functional leaflet map. Just include `<?php the_field('my_field_name'); ?>` in your Theme.
You can choose from a long list of map styles and it supports multiple markers.

 - *iFrame (OpenStreetMap.org)* Will return an iFrame HTML. Only four map styles are supported
– the ones you find on [OpenStreetMap](https://www.openstreetmap.org/) – and not more than one marker.

**Map Appearance:** Pan and zoom on the map and select from the Map layers to set the initial map position and style in the editor.

**Map Position:** If you're more like a numbers person here you can enter numeric values for the map position.

**Allow layer selection:** Allow the editors to select which map layers to show up in the frontend.

**Height:** Map height in the frontend and editor.

**Max. number of Markers**
 - *No value:* infinite markers
 - *0:* No markers
 - *Any other value:* Maximum number of markers. If the return format is *iFrame* there can ony be one marker.

## Map Proxy
The plugin comes with a proxy mechanism for map tiles. If enabled the Browser will load the tiles from your server rather than directly from the tile provider.

Use the proxy to hide sensitive credentials in the tile URL or for compliance with local privacy regulations like the European GDPR.

Find more Details in the [GitHub wiki](https://github.com/mcguffin/acf-openstreetmap-field/wiki/The-Map-Proxy).

## Development

Please head over to the source code [on Github](https://github.com/mcguffin/acf-openstreetmap-field).

## Credits
- [ACF](https://www.advancedcustomfields.com/) for sure!
- The [OpenStreetMap](https://www.openstreetmap.org/) project
- [The Leaflet Project](https://leafletjs.com/)
- The maintainers and [contributors](https://github.com/leaflet-extras/leaflet-providers/graphs/contributors) of [Leaflet providers](https://github.com/leaflet-extras/leaflet-providers)
- The [very same](https://github.com/perliedman/leaflet-control-geocoder/graphs/contributors) for [Leaflet Control Geocode](https://github.com/perliedman/leaflet-control-geocoder)
- [Dominik Moritz](https://www.domoritz.de/) who delighted us with [Leaflet locate control](https://github.com/domoritz/leaflet-locatecontrol)
- Numerous individuals and organizations who provide wonderful Map related services free of charge. (You are credited in the map, I hope)
- The proxy feature was inspired by an article by Klaus Meffert, Dr. DSGVO Blog, [Link (German)](https://dr-dsgvo.de/datenschutzfreundliches-karten-plugin-fur-webseiten-statt-google-maps-neue-moglichkeiten)

== Installation ==

Follow the standard [WordPress plugin installation procedere](http://codex.wordpress.org/Managing_Plugins).


== Frequently asked questions ==

= I found a bug. Where should I post it? =

Please use the issues section in the [GitHub-Repository](https://github.com/mcguffin/acf-openstreetmap-field/issues).

I will most likely not maintain the forum support forum on wordpress.org. Anyway, other users might have an answer for you, so it's worth a shot.

= I'd like to suggest a feature. Where should I post it? =

Please post an issue in the [GitHub-Repository](https://github.com/mcguffin/acf-openstreetmap-field/issues)

= I am a map tile provider. Please don't include our service in your plugin. =

The provisers list is taken from [Leaflet providers](https://github.com/leaflet-extras/leaflet-providers), so requests for an unlisting should go there first.

If you want your service to remain in Leaflet Providers, you can Post an issue in the plugin's [GitHub-Repository](https://github.com/mcguffin/acf-openstreetmap-field/issues).
Please provide me some way for me to verify, that you are acting on behalf of the Tile service provider your want to exclude.
(E.g. the providers website has a link to your github account.)

= Im getting these "Insecure Content" Warnings =

Some providers do not support https. If these warnings bother you, choose a different one or use the proxy feature.

= Why isn't the map loading? =

There is very likely an issue with the map tiles provider you've choosen. Some of them might have gone offline or have suspended their service. Choose another one.

= I need to do some fancy JS magic with my map. =

Check out the [GitHub wiki](https://github.com/mcguffin/acf-openstreetmap-field/wiki). Some of the js events might come in handy for you.
For Documentation of the map object, please refer to [LeafletJS](https://leafletjs.com).

= Will you anwser support requests via email? =

No.


== Screenshots ==

1. ACF Field Group Editor
2. Editing the Field Value
3. Display in the Frontend
4. Settings page. Configure API access keys and disable specific tile layers.

== Upgrade Notice ==

= 1.7.1 =
If you use the map proxy, its configuration is migrated automatically from a generated PHP file to JSON on upgrade — no action required.

= 1.7.0 =
This release requires PHP 8.0+ and WordPress 5.5+ (support for older versions has been dropped). The WP-CLI proxy commands moved from `wp map-proxy ...` to `wp acf-osm map-proxy ...`.

= 1.5.0 =
**Attention:** Version 1.5.0 may involve some breaking changes.

The global Leaflet object is no longer available.


== Changelog ==

The changelog has moved to [CHANGELOG.md](https://github.com/mcguffin/acf-openstreetmap-field/blob/master/CHANGELOG.md).
