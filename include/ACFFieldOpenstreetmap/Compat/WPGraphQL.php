<?php

namespace ACFFieldOpenstreetmap\Compat;

use ACFFieldOpenstreetmap\Core;

/**
 *	Compatibility with WPGraphQL + WPGraphQL for ACF.
 *
 *	Exposes the OpenStreetMap field as a structured GraphQL type instead of
 *	letting WPGraphQL stumble over the field's array value.
 *
 *	@see https://github.com/mcguffin/acf-openstreetmap-field/issues/137
 *
 *	Both callbacks are bound to hooks that only fire when the respective
 *	plugin is active, so no `function_exists()`/`class_exists()` guards are
 *	required.
 */
class WPGraphQL extends Core\Singleton {

	const FIELD_TYPE  = 'AcfOpenStreetMap';
	const MARKER_TYPE = 'AcfOpenStreetMapMarker';

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		// Register the GraphQL object types (fires only when WPGraphQL is active).
		add_action( 'graphql_register_types', [ $this, 'register_types' ] );

		// Map the ACF field type to its GraphQL type (fires only when
		// WPGraphQL for ACF is active).
		add_action( 'wpgraphql/acf/register_field_types', [ $this, 'register_acf_field_type' ] );
	}

	/**
	 *	Tell WPGraphQL for ACF which GraphQL type represents the field.
	 *
	 *	The field value is not formatted by WPGraphQL for ACF (the field type
	 *	is not in its `should_format_field_value()` list), so the resolvers
	 *	below receive the raw, structured field value as `$root`.
	 *
	 *	@action wpgraphql/acf/register_field_types
	 *
	 *	@param \WPGraphQL\Acf\FieldTypeRegistry $registry
	 *	@return void
	 */
	public function register_acf_field_type( $registry ) {
		$registry->register_field_type( 'open_street_map', [
			'graphql_type' => self::FIELD_TYPE,
		] );
	}

	/**
	 *	Register the GraphQL object types for the field and its markers.
	 *
	 *	@action graphql_register_types
	 *	@return void
	 */
	public function register_types() {

		register_graphql_object_type( self::MARKER_TYPE, [
			'description' => __( 'A marker on an OpenStreetMap field.', 'acf-openstreetmap-field' ),
			'fields'      => [
				'lat' => [
					'type'        => 'Float',
					'description' => __( 'Latitude coordinate of the marker.', 'acf-openstreetmap-field' ),
					'resolve'     => function( $root ) {
						return isset( $root['lat'] ) ? (float) $root['lat'] : null;
					},
				],
				'lng' => [
					'type'        => 'Float',
					'description' => __( 'Longitude coordinate of the marker.', 'acf-openstreetmap-field' ),
					'resolve'     => function( $root ) {
						return isset( $root['lng'] ) ? (float) $root['lng'] : null;
					},
				],
				'label' => [
					'type'        => 'String',
					'description' => __( 'Label of the marker.', 'acf-openstreetmap-field' ),
					'resolve'     => function( $root ) {
						return isset( $root['label'] ) ? (string) $root['label'] : null;
					},
				],
				'defaultLabel' => [
					'type'        => 'String',
					'description' => __( 'Default (auto generated) label of the marker.', 'acf-openstreetmap-field' ),
					'resolve'     => function( $root ) {
						return isset( $root['default_label'] ) ? (string) $root['default_label'] : null;
					},
				],
			],
		] );

		register_graphql_object_type( self::FIELD_TYPE, [
			'description' => __( 'An OpenStreetMap field.', 'acf-openstreetmap-field' ),
			'fields'      => [
				'lat' => [
					'type'        => 'Float',
					'description' => __( 'Latitude coordinate of the map center.', 'acf-openstreetmap-field' ),
					'resolve'     => function( $root ) {
						return isset( $root['lat'] ) ? (float) $root['lat'] : null;
					},
				],
				'lng' => [
					'type'        => 'Float',
					'description' => __( 'Longitude coordinate of the map center.', 'acf-openstreetmap-field' ),
					'resolve'     => function( $root ) {
						return isset( $root['lng'] ) ? (float) $root['lng'] : null;
					},
				],
				'zoom' => [
					'type'        => 'Int',
					'description' => __( 'Zoom level of the map.', 'acf-openstreetmap-field' ),
					'resolve'     => function( $root ) {
						return isset( $root['zoom'] ) ? (int) $root['zoom'] : null;
					},
				],
				'address' => [
					'type'        => 'String',
					'description' => __( 'Address of the first marker, if any.', 'acf-openstreetmap-field' ),
					'resolve'     => function( $root ) {
						return isset( $root['address'] ) ? (string) $root['address'] : null;
					},
				],
				'layers' => [
					'type'        => [ 'list_of' => 'String' ],
					'description' => __( 'Map tile layers enabled for this field.', 'acf-openstreetmap-field' ),
					'resolve'     => function( $root ) {
						return ( isset( $root['layers'] ) && is_array( $root['layers'] ) )
							? array_values( $root['layers'] )
							: [];
					},
				],
				'markers' => [
					'type'        => [ 'list_of' => self::MARKER_TYPE ],
					'description' => __( 'Markers placed on the map.', 'acf-openstreetmap-field' ),
					'resolve'     => function( $root ) {
						return ( isset( $root['markers'] ) && is_array( $root['markers'] ) )
							? array_values( $root['markers'] )
							: [];
					},
				],
				'version' => [
					'type'        => 'String',
					'description' => __( 'Plugin version that stored the field value.', 'acf-openstreetmap-field' ),
					'resolve'     => function( $root ) {
						return ! empty( $root['version'] ) ? (string) $root['version'] : null;
					},
				],
			],
		] );
	}
}
