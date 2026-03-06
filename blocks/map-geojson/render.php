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
			maxZoom: 19
		} ).addTo( map );

		var COLORS = [
			"#e6194b","#3cb44b","#4363d8","#f58231","#911eb4",
			"#42d4f4","#f032e6","#bfef45","#469990","#9a6324"
		];

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
				if ( heading ) rows.push( "<strong>" + heading + "</strong>" );
				if ( p.timestamp ) rows.push( "<em>" + p.timestamp + "</em>" );
				if ( p.description ) rows.push( p.description );
			} else {
				var heading = p.name || p.title;
				if ( heading ) rows.push( "<strong>" + heading + "</strong>" );
				if ( p.description ) rows.push( p.description );
				if ( p.start && p.finish ) rows.push( p.start + " &rarr; " + p.finish );
				if ( p.navigation_warning ) rows.push( "<em>" + p.navigation_warning + "</em>" );
				if ( p.route_type ) rows.push( "Type: " + p.route_type );
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
								      + "<span>" + entry.label + "</span>"
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
<div <?php echo get_block_wrapper_attributes(); ?>>
	<div id="<?php echo esc_attr( $map_id ); ?>" style="height: 450px; width: 100%;"></div>
</div>