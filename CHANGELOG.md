# Changelog

All notable changes to the [ACF OpenStreetMap Field](https://wordpress.org/plugins/acf-openstreetmap-field/) plugin are documented in this file.

## Unreleased
 - Fix: The map proxy now uses a request timeout (no more hanging on a slow tile server) and no longer forwards the visitor's `Referer` header to the upstream tile server (privacy).
 - Dev: Added GitHub Actions CI (phpcs + PHPUnit on PHP 8.0 & 8.5 + an asset-build check), expanded the PHPUnit suite (field value layer + OpenStreetMap iframe/link URLs), and pinned Composer's `platform.php` to 8.0 so dev dependencies resolve for the minimum supported PHP version.
 - Dev: New `npm run providers` drift report comparing `etc/leaflet-providers.json` against the upstream `leaflet-providers` package (catches discontinued providers and moved tile URLs early).

## 1.7.0
 - Feature: WPGraphQL support. The field is exposed as a structured `AcfOpenStreetMap` GraphQL type (center, zoom, layers and markers) when [WPGraphQL](https://www.wpgraphql.com/) and [WPGraphQL for ACF](https://acf.wpgraphql.com/) are active ([#137](https://github.com/mcguffin/acf-openstreetmap-field/issues/137), thanks to [@agenceKanvas](https://github.com/agenceKanvas)).
 - Feature: New "Fit markers in view" field setting. When enabled, the frontend map automatically zooms and centers to fit all of its markers ([#135](https://github.com/mcguffin/acf-openstreetmap-field/issues/135)).
 - Feature: New "Custom marker icon URL" field setting to use a per-field marker image on the frontend, without code ([#135](https://github.com/mcguffin/acf-openstreetmap-field/issues/135), idea from [@nexiumbiz-debug](https://github.com/nexiumbiz-debug) — [#139](https://github.com/mcguffin/acf-openstreetmap-field/pull/139)).
 - Feature: New `acf_osm_address_format` filter to override the address formats used for marker labels (street / city / country) ([#128](https://github.com/mcguffin/acf-openstreetmap-field/issues/128), thanks to [@Cyrille37](https://github.com/Cyrille37) — [#129](https://github.com/mcguffin/acf-openstreetmap-field/pull/129)).
 - Feature: New "Add markers via search only" field setting that disables manual marker placement (double-click / tap-and-hold) and dragging, leaving the address search as the only way to add markers ([#91](https://github.com/mcguffin/acf-openstreetmap-field/issues/91)).
 - Feature: New "Gesture handling" field setting (opt-in) that requires ctrl/⌘ + scroll to zoom and two fingers to move the frontend map, so it no longer traps page scrolling on touch devices ([#70](https://github.com/mcguffin/acf-openstreetmap-field/issues/70)).
 - Feature: Numeric latitude / longitude / zoom inputs in the field editor, for the map position and for each marker — kept in sync with the map two ways ([#29](https://github.com/mcguffin/acf-openstreetmap-field/issues/29)).
 - Changed: WP-CLI commands are now namespaced under `wp acf-osm map-proxy …` (was `wp map-proxy …`) and gained a `status` subcommand (with `--format`). `uninstall` now asks for confirmation (`--yes` to skip). Fixed `configure` always reporting success even when saving the config failed.
 - Changed: Raised the minimum PHP version to 8.0. The plugin now targets PHP 8.0 – 8.5; support for PHP 7.x has been dropped.
 - Changed: Raised the minimum WordPress version to 5.5 and removed the obsolete pre-5.5 rendering fallbacks. All map output now goes through the overridable templates, which also removes the duplicated markup (and a latent bug in the dead editor fallback).
 - Changed: Internal cleanup — de-duplicated the recursive array filter, removed the unused ESLint/Babel build config and dependency, removed a stray debug `console.log`, and made the class autoloader leaner (see above).
 - Changed: Removed dead editor code (an empty `getDefaultProviders()` stub and the unused `layer_is_overlay()` method with its stale Stamen patterns), declared a PSR-4 `autoload` in `composer.json`, and added unit tests for the autoloader, `LeafletProviders::get_providers()` (#133) and `MapHelper`.
 - Changed: Tested up to WordPress 7.0. Verified compatibility with the bundled jQuery 4.0 and Backbone 1.6.1 (the admin scripts use no jQuery APIs removed in 4.0).
 - Changed: Verified compatibility with Advanced Custom Fields 6.8.4 (and Secure Custom Fields). The field uses no ACF AJAX handlers, so it is unaffected by the 6.8.4 per-field-type AJAX nonce change.
 - Changed: Verified compatibility with Leaflet 1.9.4 (already bundled). Removed the obsolete map `tap` option, which Leaflet dropped together with its Tap handler in 1.8.
 - Fix: Performance — the class autoloader now skips classes outside the plugin's namespace with an in-memory string check instead of an `is_dir()` filesystem stat. It no longer stats `include/<Vendor>/` for every foreign class on the SPL autoload stack (ElasticPress, MailPoet, Cloudflare, …), which previously caused repeated failed stats on each request. It also returns quietly for a missing class instead of throwing, so `class_exists()` checks resolve to `false` rather than fataling.
 - Fix: PHP warnings/deprecations when the geocoder settings (`acf_osm_geocoder` option) have not been saved yet — undefined `scale`/`engine` keys and a null array offset — are gone; the defaults are used on a fresh install.
 - Fix: Disabling tile providers in the global settings no longer breaks fields that already use them. The selected layer of a field now keeps rendering even if its provider was disabled afterwards; the enable/disable settings only govern the layer picker. Fixes blank maps that previously required enabling every provider ([#109](https://github.com/mcguffin/acf-openstreetmap-field/issues/109), [#113](https://github.com/mcguffin/acf-openstreetmap-field/issues/113)).
 - Fix: Legacy credentials stored for a removed tile provider (e.g. the old `HERE` `app_id`/`app_key`) were injected as malformed providers and broke map rendering in the backend. Such entries are now ignored ([#133](https://github.com/mcguffin/acf-openstreetmap-field/issues/133)).
 - Fix: Leaflet JS maps not loading when a configured tile provider has been discontinued (e.g. Stamen, Thunderforest). Invalid or removed providers are now skipped gracefully instead of throwing `Invalid provider`, and the map falls back to the default OpenStreetMap layer rather than rendering a blank grey map ([#134](https://github.com/mcguffin/acf-openstreetmap-field/issues/134)).
 - Fix: Fatal error (`strlen(): Argument #1 ($string) must be of type string, array given`) when translating an ACF block containing a map field with WPML. The field is now flagged as non-translatable so WPML no longer tries to register its array value as a translatable string ([#136](https://github.com/mcguffin/acf-openstreetmap-field/issues/136)).

## 1.6.2
 - Support proxy on multisite
 - Introduce proxy WP-CLI commands
 - Fix: Content-Type HTTP-Header for some providers

## 1.6.1
 - Fix PHP fatal during upgrade

## 1.6.0
 - Introduce Map Proxy
 - Slightly improve settings page
 - Update map providers
 - Fix: _load_textdomain_just_in_time notice
 - Fix: Add marker pointer events
 - Fix: Maps in WP Admin not inited

## 1.5.7
 - Fix: Backend Map broken
 - Fix: Geocoded result not stored in raw data

## 1.5.6
 - Fix: PHP notice version_compare

## 1.5.5
 - JS: use IntersectionObserver to detect whether a map has become visible
 - Fix: ACF field not inited in Flexible Content and repeaters
 - Fix: JS recursion
 - Fix: fit bounds not working
 - Fix: marker drag not triggered
 - Fix: marker unique-IDs not always created
 - Fix: Block editor issues

## 1.5.4
 - Fix: JS ReferenceError on move marker with max markers = 1

## 1.5.3
 - Fix: Disable provider settings not displaying

## 1.5.2
 - Fix: JS Error if some providers are disabled

## 1.5.1
 - Backend UI: Attribution below map
 - ACF Field: Introduce conditional logic
 - Fix: Some map controls not visible in Blockeditor sidebar
 - Fix: Marker instructions display
 - Providers: [Migrate Stamen to Stadia Maps](https://maps.stamen.com/stadia-partnership/)
 - Providers: Update Esri Ocean base map, OpenAIP, Opensnowmap, OpenWeathermap, OpenFireMap, NLS, OpenRailwayMap, Jawg, MapTiler, MtbMap, nlmaps
 - Providers: Remove HERE (Legacy), Hydda (service down)
 - JS: Rewritten ACF integration

## 1.5.0
 - Use Leaflet noConflict
 - Refactor JS
 - Geocoder: Address detail level is now controlled by map zoom
 - Geocoder: Provide filters for configuration overides
 - Fix: Make JS event `acf-osm-map-marker-created` bubbling
 - Fix: JS Crashes in ACF Blocks
 - Fix: Weird coordinates (worldCopyJump)

## 1.4.3
 - Fix: JS – acf hook `acf-osm/create-marker` undefined argument + not firing on geocode

## 1.4.2
 - Fix: JS Error on append repeater

## 1.4.1
 - JS: remove console.log
 - Fix: admin js broken after jquery removal

## 1.4.0
 - UI: Adapt to ACF 6 field group admin
 - JS API: do acf actions on marker events
 - JS Frontend: remove jQuery dependency
 - Data: add geocode results to raw data
 - Fix: search submit button did not submit
 - Fix: print template script only if input element is present
 - Fix: value sanitation. Shold now work with Frontend Admin for ACF

## 1.3.5
 - Fix: Admin Marker styling broken
 - Fix: PHP Fatal with suki theme
 - Fix: include leaflet control geocode assets

## 1.3.4
 - Fix: locate control API

## 1.3.3
 - Upgrade leafletjs, leaflet-control-geocoder, leaflet-providers, leaflet, leaflet.locatecontrol to latest releases
 - Remove HikeBike map provider
 - Support ACF Rest API integration (since ACF 5.11)
 - Fix: PHP 8 compatibility
 - Fix: iframes in block preview not editable
 - Fix: quote missing on html attribute in osm template
 - Test with WP 6.0

## 1.3.2
 - Fix: No such variant of OpenStreetMap (Mapnik)
 - Fix: Popups not opening in Safari
 - Quick and dirty Fix: invalid (localized) lat/lng object.

## 1.3.1
 - Fix: JS Event acf-osm-map-marker-create not applying marker options

## 1.3.0
 - Theme Overrides: Override map output in your theme
 - Breaking Change: Use native JS Events
 - Breaking Change: `osm_map_iframe_template` filter gone in WP 5.5
 - Fix: jQuery 3.x (WP 5.6) compatibility
 - Fix: Map not showing on login form
 - Fix: Providers not loaded if webroot owner is not www-user
 - Upgrade: Leaflet 1.7.1
 - Upgrade: Leaflet Providers 1.11.0
 - Upgrade: Leaflet Control Geocoder 2.1.0

## 1.2.2
 - Fix: Duplicated Row (ACF 5.9+)

## 1.2.1
 - Upgrade FreeMapSK, CyclOSM

## 1.2.0
 - Feature: Settings page allowing you to disable specific map tile providersw
 - Feature: Fit markers in view (backend)
 - Upgrade: leaflet-providers, leaflet-control-geocoder, leaflet.locatecontrol

## 1.1.9
 - UI: Add Settings link on plugins list table
 - Fix: hide map provider with unconfigured api key from layer selection
 - Upgrade: leaflet-control-geocoder, leaflet.locatecontrol, leaflet-providers
 - Security hardening

## 1.1.8
 - Feature: make marker address formats localizable.
 - JS: pass map init object along with acf-os-map-create event
 - UI: hide add marker at my location button if markers cant be added

## 1.1.7
 - Feature: Add locate me button to backend
 - Fix: Geocoder search result still visible after marker added to map.
 - Fix: Required field and max_markers = 0 never saved
 - Fix: HERE app code not included in api requests

## 1.1.6
 - Feature: Observe DOM for newly added maps
 - Feature: allow manipulation of layer config in JS
 - Fix: JS event 'acf-osm-map-marker-create' not triggered

## 1.1.5
 - JS: added event Listener for ajax-loaded maps. Use `$(my_map_div).trigger('acf-osm-map-added');` on each newly added map.
 - Upgrade LeafletJS to 1.6.0

## 1.1.4
 - Upgrade Leaflet Providers to 1.9.0
 - Upgrade Leaflet Control Geocode to 1.10.0
 - Fix: Redraw maps when they become visible

## 1.1.3
 - UI: Better formatting for automatic marker labels
 - Fix: Map controls zindex in Block-Editor
 - Fix: Adding markers not working on mobile devices

## 1.1.2
 - Fix: PHP Strict Standards message

## 1.1.1
 - Fix: Required Field behaviour – "required" means now "must hava a marker"

## 1.1.0
 - UI: Usability Improvements
 - Tested: Verfied Compatibility with Widgets, Block-Editor, Frontend Form
 - Stored data pretty much like google map field
 - Code: Refactored JS

## 1.0.1
 - Convert Values from ACF Googlemaps-Field

## 1.0.0
 - Initial Release
