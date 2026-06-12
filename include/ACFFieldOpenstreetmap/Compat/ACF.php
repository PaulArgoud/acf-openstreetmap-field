<?php

namespace ACFFieldOpenstreetmap\Compat;

use ACFFieldOpenstreetmap\Core;
use ACFFieldOpenstreetmap\Field;

class ACF extends Core\Singleton {

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {
		add_action('acf/include_field_types', [ $this, 'include_field_types' ] ); // v5

		// Compat with https://github.com/mcguffin/polylang-sync
		add_filter( 'polylang_acf_sync_supported_fields', [ $this, 'add_pll_sync_field_type'] );

		// Compat with WPML / ACF Multilingual: don't translate the map field.
		add_filter( 'acfml_should_translate_acf_entity', [ $this, 'wpml_should_translate_entity' ], 10, 3 );

		add_action( 'acf/input/admin_enqueue_scripts', [ $this, 'acf_admin_enqueue_scripts' ] );
	}

	/**
	 *	Tell WPML / ACF Multilingual not to translate the OpenStreetMap field.
	 *
	 *	The field value is a structured array (coordinates, zoom, layers,
	 *	markers) – not a string. When WPML registers it for string translation
	 *	(e.g. while translating an ACF block) it eventually calls strlen() on
	 *	the value, which fatals with "Argument #1 ($string) must be of type
	 *	string, array given".
	 *
	 *	@see https://github.com/mcguffin/acf-openstreetmap-field/issues/136
	 *
	 *	@filter acfml_should_translate_acf_entity
	 *
	 *	@param boolean $should_translate Whether the entity should be translated.
	 *	@param array   $entity           ACF field or field group definition.
	 *	@param string  $entity_type      Either 'field' or 'group'.
	 *	@return boolean
	 */
	public function wpml_should_translate_entity( $should_translate, $entity, $entity_type ) {
		if ( 'field' === $entity_type && isset( $entity['type'] ) && 'open_street_map' === $entity['type'] ) {
			return false;
		}
		return $should_translate;
	}

	/**
	 *	@action acf/input/admin_enqueue_scripts
	 */
	public function acf_admin_enqueue_scripts() {
		wp_enqueue_media();
	}

	/**
	 *	@filter polylang_acf_sync_supported_fields
	 */
	public function add_pll_sync_field_type($fields) {
		$fields[] = 'open_street_map';
		return $fields;
	}

	/**
	 *	@action acf/render_field/type=leaflet_map
	 */
	public function render_map_input( $field ) {

		$inp_field = [
			'return_format'	=> $field['return_format'],
			'value'	=> $field['value'],
			'height'		=> 400,
			'attr'	=> $field['attr'],
		];

		if ( isset( $field['attr'] ) ) {
			$inp_field['attr'] = $field['attr'];
		}

		$map_field = acf_get_field_type('open_street_map');

		// format_value() returns sanitized HTML
		echo $map_field->format_value( $field['value'], null, $inp_field ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 *  @action acf/include_field_types
	 *
	 *  This function will include the field type class
	 *
	 *  @type	function
	 *  @date	17/02/2016
	 *  @since	1.0.0
	 *
	 *  @param	$version (int) major ACF version. Defaults to false
	 *  @return	n/a
	 */
	public function include_field_types( $version = false ) {

		if ( version_compare( acf_get_setting('version'), '5.7', '>=' ) ) {
			acf_register_field_type( new Field\OpenStreetMap() );
		}
	}
}
