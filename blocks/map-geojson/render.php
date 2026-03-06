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

$map_title    = trim( get_the_title( $attachment_id ) );
$map_subtitle = trim( get_post_field( 'post_content', $attachment_id ) );
$map_caption  = trim( wp_get_attachment_caption( $attachment_id ) );

wp_enqueue_style(
	'leaflet',
	HWMAPTOOL_URL . 'assets/leaflet/leaflet.css',
	[],
	'1.9.4'
);

wp_enqueue_script(
	'leaflet',
	HWMAPTOOL_URL . 'assets/leaflet/leaflet.js',
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

		L.tileLayer("https://sgx.geodatenzentrum.de/wmts_topplus_open/tile/1.0.0/web_grau/default/WEBMERCATOR/{z}/{y}/{x}.png", {
	maxZoom: 20,
	attribution: "Map data: &copy; <a href=\"https://www.govdata.de/dl-de/by-2-0\">dl-de/by-2-0</a>"
}).addTo( map );

		
		var COLORS = [
			"#e6194b","#3cb44b","#4363d8","#f58231","#911eb4",
			"#42d4f4","#f032e6","#bfef45","#469990","#9a6324"
		];

		/* Escape a value for safe insertion into innerHTML. */
		function escHtml( s ) {
			return String( s )
				.replace( /&/g, "&amp;" )
				.replace( /</g, "&lt;" )
				.replace( />/g, "&gt;" )
				.replace( /"/g, "&quot;" )
				.replace( /\'/g, "&#39;" );
		}

		/* Return the best human-readable label for a feature, or null if none. */
		function featureLabel( feature ) {
			var p   = feature.properties || {};
			var geo = feature.geometry && feature.geometry.type;
			var isPoint = geo === "Point" || geo === "MultiPoint";
			/* Points: prefer label, then name, then title */
			if ( isPoint ) {
				return p.label || p.name || p.title || null;
			}
			/* Lines / Polygons: prefer name, then title */
			return p.name || p.title || null;
		}

		/* Build an HTML tooltip string from available properties. */
		function featureTooltip( feature ) {
			var p    = feature.properties || {};
			var geo  = feature.geometry && feature.geometry.type;
			var isPoint = geo === "Point" || geo === "MultiPoint";
			var rows = [];

			if ( isPoint ) {
				var heading = p.label || p.name || p.title;
				if ( heading ) rows.push( "<strong>" + escHtml( heading ) + "</strong>" );
				if ( p.timestamp ) rows.push( "<em>" + escHtml( p.timestamp ) + "</em>" );
				if ( p.description ) rows.push( escHtml( p.description ) );
			} else {
				var heading = p.name || p.title;
				if ( heading ) rows.push( "<strong>" + escHtml( heading ) + "</strong>" );
				if ( p.description ) rows.push( escHtml( p.description ) );
				if ( p.start && p.finish ) rows.push( escHtml( p.start ) + " &rarr; " + escHtml( p.finish ) );
				if ( p.navigation_warning ) rows.push( "<em>" + escHtml( p.navigation_warning ) + "</em>" );
				if ( p.route_type ) rows.push( "Type: " + escHtml( p.route_type ) );
			}

			return rows.join( "<br>" );
		}

		fetch( ' . wp_json_encode( $file_url ) . ' )
			.then( function ( r ) { return r.json(); } )
			.then( function ( data ) {
				var features = data.features || [];

				/* Assign a color per feature index */
				var featureColors = features.map( function ( f, i ) {
					return COLORS[ i % COLORS.length ];
				} );

				/* Build legend entries: only features that have a meaningful label */
				var legendEntries = [];
				var legendSeen    = {};
				features.forEach( function ( f, i ) {
					var lbl = featureLabel( f );
					if ( lbl && !legendSeen[ lbl ] ) {
						legendSeen[ lbl ] = true;
						legendEntries.push( { label: lbl, color: featureColors[ i ] } );
					}
				} );

				var layer = L.geoJSON( data, {
					style: function ( feature ) {
						var i = features.indexOf( feature );
						var c = featureColors[ i ] || "#3388ff";
						return { color: c, fillColor: c, fillOpacity: 0.35, weight: 2.5 };
					},
					pointToLayer: function ( feature, latlng ) {
						var i = features.indexOf( feature );
						var c = featureColors[ i ] || "#3388ff";
						return L.circleMarker( latlng, {
							radius: 6, color: c, fillColor: c,
							fillOpacity: 0.85, weight: 2
						} );
					},
					onEachFeature: function ( feature, leafletLayer ) {
						var tip = featureTooltip( feature );
						if ( tip ) {
							leafletLayer.bindTooltip( tip, { sticky: true } );
						}
					}
				} ).addTo( map );

				if ( layer.getBounds().isValid() ) {
					map.fitBounds( layer.getBounds(), { padding: [20, 20] } );
				} else {
					map.setView( [0, 0], 2 );
				}

				/* Legend control – only rendered when at least one label exists */
				if ( legendEntries.length > 0 ) {
					var Legend = L.Control.extend( {
						onAdd: function () {
							var div = L.DomUtil.create( "div" );
							div.style.cssText =
								"background:#fff;padding:8px 12px;border-radius:4px;" +
								"box-shadow:0 1px 5px rgba(0,0,0,.45);font:13px/1.6 sans-serif;" +
								"max-width:220px;pointer-events:none;";

							var html = "";
							legendEntries.forEach( function ( entry ) {
								html += "<div style=\"display:flex;align-items:center;gap:8px;margin:2px 0;\">"
								      + "<span style=\"display:inline-block;width:13px;height:13px;"
								      +   "border-radius:3px;background:" + entry.color + ";flex-shrink:0;\"></span>"
								      + "<span>" + escHtml( entry.label ) + "</span>"
								      + "</div>";
							} );

							div.innerHTML = html;
							return div;
						}
					} );
					new Legend( { position: "bottomright" } ).addTo( map );
				}
			} )
			.catch( function () { map.setView( [0, 0], 2 ); } );
	})();',
	'after'
);
?>
<div style="font-family:Arial, Helvetica, sans-serif;" <?php echo get_block_wrapper_attributes(); ?>>
	<?php if ( $map_title !== '' ) : ?>
		<h6 style="margin:0 0 4px;"><?php echo esc_html( $map_title ); ?></h6>
	<?php endif; ?>
    <?php if ( $map_caption !== '' ) : ?>
		<strong style="margin:6px 0 0;font-size:.875em;color:#666;"><?php echo wp_kses_post( $map_caption ); ?></strong>
	<?php endif; ?>
	<div id="<?php echo esc_attr( $map_id ); ?>" style="height: 450px; width: 100%;"></div>
	<?php if ( $map_subtitle !== '' ) : ?>
		<figcaption style="margin:0.5rem 0 8px;font-size:.8em;color:#555;"><?php echo wp_kses_post( $map_subtitle ); ?></figcaption>
	<?php endif; ?>

</div>