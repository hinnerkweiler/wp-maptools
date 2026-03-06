<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attachment_id = isset( $attributes['attachmentId'] ) ? absint( $attributes['attachmentId'] ) : 0;

if ( ! $attachment_id ) {
	return;
}

$file_url = wp_get_attachment_url( $attachment_id );

if ( ! $file_url ) {
	return;
}

wp_enqueue_style(
	'leaflet',
	'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
	[],
	'1.9.4'
);

wp_enqueue_script(
	'leaflet',
	'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
	[],
	'1.9.4',
	true
);

$map_id = wp_unique_id( 'hw-map-' );

wp_add_inline_script(
	'leaflet',
	'(function () {
		var el = document.getElementById(' . wp_json_encode( $map_id ) . ');
		if ( ! el ) return;
		var map = L.map( el );
		L.tileLayer( "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
			attribution: "&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors",
			maxZoom: 19
		} ).addTo( map );
		L.tileLayer( "https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png", {
			attribution: "&copy; <a href=\"https://www.openseamap.org\">OpenSeaMap</a> contributors",
			maxZoom: 19,
			opacity: 1
		} ).addTo( map );
		fetch( ' . wp_json_encode( $file_url ) . ' )
			.then( function ( r ) { return r.json(); } )
			.then( function ( data ) {
				var layer = L.geoJSON( data ).addTo( map );
				if ( layer.getBounds().isValid() ) {
					map.fitBounds( layer.getBounds(), { padding: [20, 20] } );
				} else {
					map.setView( [0, 0], 2 );
				}
			} )
			.catch( function () { map.setView( [0, 0], 2 ); } );
	})();',
	'after'
);
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<div id="<?php echo esc_attr( $map_id ); ?>" style="height: 450px; width: 100%;"></div>
</div>