<div align="center">

<img src=".wporg/banner-1544x500.png" alt="ACF OpenStreetMap Field" width="100%">

# ACF OpenStreetMap Field

**Configurable OpenStreetMap / [Leaflet](https://leafletjs.com/) map field for [Advanced Custom Fields](https://www.advancedcustomfields.com/).**

[![Version](https://img.shields.io/github/v/tag/PaulArgoud/acf-openstreetmap-field?sort=semver&label=version&color=blue)](https://github.com/PaulArgoud/acf-openstreetmap-field/tags)
[![Tested up to](https://img.shields.io/badge/WordPress-up%20to%207.0-21759b?logo=wordpress&logoColor=white)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/php-8.0--8.5-777BB4?logo=php&logoColor=white)](composer.json)
[![ACF](https://img.shields.io/badge/ACF-5.7%2B-00a0d2)](https://www.advancedcustomfields.com/)
[![License: GPL v3](https://img.shields.io/badge/license-GPLv3-blue.svg)](LICENSE.txt)

</div>

---

Pick a tile provider, set the view, drop one or many markers — then output a ready-to-use interactive map, an OpenStreetMap.org iframe, or the raw coordinates, anywhere in your theme. No Google Maps API key, no billing account, no tracking by default.

## Table of contents
- [Features](#features)
- [Screenshots](#screenshots)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Return formats](#return-formats)
- [Customization](#customization)
- [Integrations](#integrations)
- [Development](#development)
- [Testing](#testing)
- [Changelog](#changelog)
- [License](#license)

## Features
- 🗺️ **Dozens of tile providers & overlays** via [Leaflet Providers](https://github.com/leaflet-extras/leaflet-providers) — choose base maps and overlays per field.
- 📍 **Single or multiple markers** — editable labels, geocoding (address search), numeric lat/lng/zoom inputs, and an optional *search-only* mode.
- 🧩 **Three return formats** — interactive Leaflet map, OpenStreetMap.org iframe, or raw data.
- 🎯 **Frontend UX** — optionally auto-fit markers in view and enable *gesture handling* so the map doesn't trap page scrolling on touch devices.
- 🎨 **Custom markers** — a no-code marker icon URL, plus full control through WordPress filters and JavaScript events.
- 🔒 **Map Proxy** — serve tiles from your own server to hide API keys and comply with privacy regulations such as the GDPR.
- 🔌 **Integrations** — WPGraphQL, WPML, Polylang and the ACF REST API.
- 🧱 **Block editor, widgets & frontend forms** ready, with overridable theme templates.

## Screenshots
<details>
<summary>Show screenshots</summary>

| ACF Field Group Editor | Editing the Field Value |
| :---: | :---: |
| ![Field group editor](.wporg/screenshot-1.png) | ![Editing the field value](.wporg/screenshot-2.png) |

| Display in the Frontend | Settings page |
| :---: | :---: |
| ![Frontend display](.wporg/screenshot-3.png) | ![Settings page](.wporg/screenshot-4.png) |

</details>

## Requirements
- WordPress 4.8+
- PHP 8.0 – 8.5
- [Advanced Custom Fields](https://www.advancedcustomfields.com/) 5.7+

## Installation

**In WP Admin** — search for *ACF OpenStreetMap Field* and follow the [automatic plugin installation](https://wordpress.org/support/article/managing-plugins/#automatic-plugin-installation).

**WP-CLI**
```shell
wp plugin install --activate acf-openstreetmap-field
```

**Composer**
```shell
composer require mcguffin/acf-openstreetmap-field
```

## Usage
Add an **OpenStreetMap** field to a field group, then output it in your theme.

Leaflet and iframe return formats print a ready-made map — just echo the field:

```php
<?php the_field( 'my_map' ); ?>
```

With the **Raw data** return format you get the structured value:

```php
<?php
$map = get_field( 'my_map' );

printf( 'Center: %F, %F (zoom %d)', $map['lat'], $map['lng'], $map['zoom'] );

foreach ( $map['markers'] as $marker ) {
    printf( '%s — %F, %F', esc_html( $marker['label'] ), $marker['lat'], $marker['lng'] );
}
```

More developer-centric documentation lives in the [wiki](../../wiki).

## Return formats
| Format | `get_field()` returns | Notes |
| --- | --- | --- |
| **Leaflet JS** | Interactive map markup | Many map styles, multiple markers, overlays |
| **iFrame (OpenStreetMap.org)** | `<iframe>` markup | Four styles, one marker |
| **Raw data** | `array` | `lat`, `lng`, `zoom`, `layers`, `markers`, `address` |

## Customization
**Custom marker icon (PHP)** — return some HTML to render a `divIcon`:

```php
add_filter( 'acf_osm_marker_html', function () {
    return '<span class="my-marker"></span>';
} );
```

**Per-marker tweaks (JavaScript)** — adjust each marker as it is created:

```js
document.addEventListener( 'acf-osm-map-marker-create', ( e ) => {
    const { markerOptions, L } = e.detail;
    markerOptions.icon = L.icon( { iconUrl: '/path/to/icon.png', iconSize: [ 32, 32 ] } );
} );
```

See the [HTML Marker Icon](../../wiki/HTML-Marker-Icon) wiki page for the full list of filters and JS events.

**Map Proxy** — load tiles through your own server to hide credentials and avoid sending visitor data to third-party tile servers. See [The Map Proxy](../../wiki/The-Map-Proxy).

## Integrations
- **WPGraphQL** — exposes a structured `AcfOpenStreetMap` type (requires [WPGraphQL](https://www.wpgraphql.com/) and [WPGraphQL for ACF](https://acf.wpgraphql.com/)).
- **WPML / Polylang** — map values are copied / synced across translations instead of being treated as translatable strings.
- **ACF REST API** — field values are available through the WordPress REST API.

## Development
```shell
git clone git@github.com:mcguffin/acf-openstreetmap-field.git
cd acf-openstreetmap-field
npm install
npm run dev
```

Useful npm scripts:

| Script | Description |
| --- | --- |
| `npm run dev` | Watch and rebuild CSS & JS sources |
| `npm run build` | Build CSS & JS for production |
| `npm run dev-test` | Create test fields in wp-admin and watch sources |
| `npm run uitest` | Create test fields in wp-admin |
| `npm run audit` | Run the phpcs audit |
| `npm run providers` | Report tile-provider drift vs upstream `leaflet-providers` |
| `npm run i18n` | Generate the `.pot` file |
| `npm run test` | Run unit tests against PHP 8.0 and 8.5 |

## Testing
**In WP-Admin** — add the field to several places for manual testing:
```shell
npm run dev-test
```

**Unit tests** run in [@wordpress/env](https://www.npmjs.com/package/@wordpress/env) (a Docker container, so [Docker Desktop](https://docs.docker.com/desktop/) is required) against PHP 8.0 (legacy) and 8.5 (edge):
```shell
npm run test            # both
npm run test:edge       # PHP 8.5 only
npm run test:legacy     # PHP 8.0 only
```

Help is welcome with unit tests covering all PHP code and with unit-testing the JS.

## Changelog
See [CHANGELOG.md](CHANGELOG.md) for the full release history.

## License
[GPLv3 or later](LICENSE.txt) &copy; the plugin contributors.