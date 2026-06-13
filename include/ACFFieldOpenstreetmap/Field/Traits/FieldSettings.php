<?php

namespace ACFFieldOpenstreetmap\Field\Traits;

use ACFFieldOpenstreetmap\Core;

/**
 *	Field-editor settings rendering for the OpenStreetMap field.
 *
 *	Extracted from the field class to keep the data layer (value handling,
 *	ACF hooks) separate from the (verbose) settings UI. Mirrors the way the
 *	Settings page is split into ProviderSettings / GeocoderSettings / UIElements.
 */
trait FieldSettings {

	/*
	 *  render_field_settings()
	 *
	 *  Create extra settings for your field. These are visible when editing a field
	 *
	 *  @type	action
	 *  @since	3.6
	 *  @date	23/01/13
	 *
	 *  @param	$field (array) the $field being edited
	 *  @return	n/a
	 */

	function render_field_settings( $field ) {

		$templates = Core\Templates::instance();

		$field = $this->sanitize_field( $field );

		$return_choices = $templates->get_templates();
		$return_choices = array_map( function( $template ) {
			return $template['name'];
		}, $return_choices );

		$is_legacy = version_compare( acf()->version, '6.0.0', '<' );


		// return_format
		acf_render_field_setting( $field, [
			'label'			=> __('Return Format','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'choices'		=> [
				'raw'			=> __("Raw Data",'acf-openstreetmap-field'),
			] + $return_choices,
			'layout'	=>	'horizontal',
		]);

		if ( $is_legacy ) {
			$this->render_field_presentation_settings( $field );
			$this->render_field_validation_settings( $field );
		}
	}

	/**
	 * Renders the field settings used in the "Validation" tab.
	 *
	 * @since 6.0
	 *
	 * @param array $field The field settings array.
	 * @return void
	 */
	function render_field_validation_settings( $field ) {

		// allow_layer selection
		acf_render_field_setting( $field, [
			'label'			=> __( 'Max. number of Markers', 'acf-openstreetmap-field' ),
			'instructions'	=> __( 'Leave empty for infinite markers', 'acf-openstreetmap-field' ),
			'name'			=> 'max_markers',
			'type'			=> 'number',
			'ui'			=> 1,
			'min'			=> 0,
			'step'			=> 1,
		]);

		// markers via search only (disable manual placement + dragging)
		acf_render_field_setting( $field, [
			'label'			=> __( 'Add markers via search only', 'acf-openstreetmap-field' ),
			'instructions'	=> __( 'Editors can only place markers using the address search. Manual placement (double-click / tap-and-hold) and dragging are disabled.', 'acf-openstreetmap-field' ),
			'name'			=> 'markers_search_only',
			'type'			=> 'true_false',
			'ui'			=> 1,
		]);
	}

	/**
	 * Renders the field settings used in the "Presentation" tab.
	 *
	 * @since 6.0
	 *
	 * @param array $field The field settings array.
	 * @return void
	 */
	function render_field_presentation_settings( $field ) {

		$leafletProviders = Core\LeafletProviders::instance();
		$osmProviders     = Core\OSMProviders::instance();

		acf_render_field_setting( $field, [
			'label'				=> __( 'Map Appearance', 'acf-openstreetmap-field' ),
			'instructions'		=> __( 'Set zoom, center and select layers being displayed.', 'acf-openstreetmap-field' ),
			'type'				=> 'open_street_map',
			'name'				=> 'leaflet_map',

			'return_format'		=> 'admin',
			'attr'				=> [
				'data-editor-config'	=> [
					'allow_providers'		=> true,
					'restrict_providers'	=> false,
					'max_markers'			=> 0, // no markers
					'name_prefix'			=> $field['prefix'],
				],
				'data-map-layers'		=> $field['layers'],
			],
			'value'	=> [
				'lat'				=> $field['center_lat'],
				'lng'				=> $field['center_lng'],
				'zoom'				=> $field['zoom'],
				'layers'			=> $field['layers'],
				'markers'			=> [],
			],
			'wrapper'      => [
				'data-name' => 'wrapper',
				'class'     => 'acf-field-setting-wrapper',
			],
		] );

		// lat
		acf_render_field_setting( $field, [
			'label'			=> __('Map Position','acf-openstreetmap-field'),
			'instructions'	=> __('Center the initial map','acf-openstreetmap-field'),
			'type'			=> 'number',
			'name'			=> 'center_lat',
			'prepend'		=> __('lat','acf-openstreetmap-field'),
			'placeholder'	=> $this->default_values['lat'],
			// 'step'			=> 0.1,
		]);

		// lng
		acf_render_field_setting( $field, [
			'label'			=> __( 'Center', 'acf-openstreetmap-field' ),
			'instructions'	=> __( 'Center the initial map', 'acf-openstreetmap-field' ),
			'type'			=> 'number',
			'name'			=> 'center_lng',
			'prepend'		=> __('lng','acf-openstreetmap-field'),
			'placeholder'	=> $this->default_values['lng'],
			'_append' 		=> 'center_lat',
			// 'step'			=> 0.1,
		]);

		// zoom
		acf_render_field_setting( $field, [
			'label'			=> __( 'Zoom', 'acf-openstreetmap-field' ),
			'instructions'	=> __( 'Set the initial zoom level', 'acf-openstreetmap-field' ),
			'type'			=> 'number',
			'name'			=> 'zoom',
			'min'			=> 1,
			'max'			=> 22,
			'prepend'		=> __('zoom','acf-openstreetmap-field'),
			'placeholder'	=> $this->default_values['zoom'],
			'_append' 		=> 'center_lat',
		]);

		// allow_layer selection
		acf_render_field_setting( $field, [
			'label'			=> __( 'Allow layer selection', 'acf-openstreetmap-field' ),
			'instructions'	=> '',
			'name'			=> 'allow_map_layers',
			'type'			=> 'true_false',
			'ui'			=> 1,
		]);

		// fit markers in view (frontend)
		acf_render_field_setting( $field, [
			'label'			=> __( 'Fit markers in view', 'acf-openstreetmap-field' ),
			'instructions'	=> __( 'Automatically zoom and center the frontend map to fit all markers. Overrides the map position above when the map has markers.', 'acf-openstreetmap-field' ),
			'name'			=> 'fit_bounds',
			'type'			=> 'true_false',
			'ui'			=> 1,
		]);

		// gesture handling (frontend, touch devices)
		acf_render_field_setting( $field, [
			'label'			=> __( 'Gesture handling', 'acf-openstreetmap-field' ),
			'instructions'	=> __( 'Require ctrl/⌘ + scroll to zoom and two fingers to move the frontend map, so it does not trap page scrolling on touch devices.', 'acf-openstreetmap-field' ),
			'name'			=> 'gesture_handling',
			'type'			=> 'true_false',
			'ui'			=> 1,
		]);

		// custom marker icon (frontend)
		acf_render_field_setting( $field, [
			'label'			=> __( 'Custom marker icon URL', 'acf-openstreetmap-field' ),
			'instructions'	=> __( 'Optional image URL used for markers on the frontend. For full control (size, anchor, retina) use the acf_osm_marker_icon filter instead.', 'acf-openstreetmap-field' ),
			'name'			=> 'marker_icon_url',
			'type'			=> 'text',
			'placeholder'	=> 'https://…',
		]);

		// Leaflet layers
		acf_render_field_setting( $field, [
			'label'			=> __( 'Leaflet Layers', 'acf-openstreetmap-field' ),
			'instructions'	=> '',
			'name'			=> 'layers',
			'type'			=> 'select',
			'multiple'		=> 1,
			'choices'		=> $leafletProviders->get_layers(),
			'wrapper'		=> [
				'class'	=> 'acf-hidden',
			],
		]);

		// map height
		acf_render_field_setting( $field, [
			'label'			=> __('Height','acf'),
			'instructions'	=> __('Customize the map height','acf-openstreetmap-field'),
			'type'			=> 'text',
			'name'			=> 'height',
			'append'		=> 'px',
		]);
	}
}
