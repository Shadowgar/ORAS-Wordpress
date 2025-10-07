/**
 * Start open street map widget script
 */

( function( $, elementor ) {

	'use strict';

	var widgetOpenStreetMap = function( $scope, $ ) {

		var $openStreetMap = $scope.find( '.bdt-open-street-map' ),
            settings       = $openStreetMap.data('settings'),
            markers        = $openStreetMap.data('map_markers'),
            tileSource = '';

        if ( ! $openStreetMap.length ) {
            return;
        }

        var avdOSMap = L.map($openStreetMap[0], {
                zoomControl: settings.zoomControl,
                scrollWheelZoom: false
            }).setView([
                    settings.lat,
                    settings.lng
                ], 
                settings.zoom
            );

        if (settings.mapboxToken !== '' && settings.mapboxToken !== false) {
          tileSource = 'https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=' + settings.mapboxToken;
            L.tileLayer( tileSource, {
                maxZoom: 18,
                attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery &copy; <a href="https://www.mapbox.com/">Mapbox</a>',
                id: 'mapbox/streets-v11',
                tileSize: 512,
                zoomOffset: -1
            }).addTo(avdOSMap);
        } else {
            L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(avdOSMap);
        }


        for (var i in markers) { 
            if( (markers[i]['iconUrl']) != '' && typeof (markers[i]['iconUrl']) !== 'undefined'){ 
                var LeafIcon = L.Icon.extend({
                    options: {
                        iconSize   : [25, 41],
                        iconAnchor : [12, 41],
                        popupAnchor: [2, -41]
                    }
                });
                var greenIcon = new LeafIcon({iconUrl: markers[i]['iconUrl'] });
                // Create a safe popup content that allows HTML formatting but prevents XSS
                var popupContent = document.createElement('div');
                popupContent.innerHTML = markers[i]['infoWindow'];
                // Remove any script tags and event handlers for security
                var scripts = popupContent.querySelectorAll('script');
                for (var j = 0; j < scripts.length; j++) {
                    scripts[j].remove();
                }
                // Remove any elements with event handlers
                var elementsWithEvents = popupContent.querySelectorAll('[onclick], [onload], [onerror], [onmouseover], [onmouseout]');
                for (var k = 0; k < elementsWithEvents.length; k++) {
                    elementsWithEvents[k].removeAttribute('onclick');
                    elementsWithEvents[k].removeAttribute('onload');
                    elementsWithEvents[k].removeAttribute('onerror');
                    elementsWithEvents[k].removeAttribute('onmouseover');
                    elementsWithEvents[k].removeAttribute('onmouseout');
                }
                L.marker( [markers[i]['lat'], markers[i]['lng']], {icon: greenIcon} ).bindPopup(popupContent).addTo(avdOSMap);
            } else {
                if( (markers[i]['lat']) != '' && typeof (markers[i]['lat']) !== 'undefined'){ 
                    // Create a safe popup content that allows HTML formatting but prevents XSS
                    var popupContent = document.createElement('div');
                    popupContent.innerHTML = markers[i]['infoWindow'];
                    // Remove any script tags and event handlers for security
                    var scripts = popupContent.querySelectorAll('script');
                    for (var j = 0; j < scripts.length; j++) {
                        scripts[j].remove();
                    }
                    // Remove any elements with event handlers
                    var elementsWithEvents = popupContent.querySelectorAll('[onclick], [onload], [onerror], [onmouseover], [onmouseout]');
                    for (var k = 0; k < elementsWithEvents.length; k++) {
                        elementsWithEvents[k].removeAttribute('onclick');
                        elementsWithEvents[k].removeAttribute('onload');
                        elementsWithEvents[k].removeAttribute('onerror');
                        elementsWithEvents[k].removeAttribute('onmouseover');
                        elementsWithEvents[k].removeAttribute('onmouseout');
                    }
                    L.marker( [markers[i]['lat'], markers[i]['lng']] ).bindPopup(popupContent).addTo(avdOSMap);
                }
            }
        }

	};


	jQuery(window).on('elementor/frontend/init', function() {
		elementorFrontend.hooks.addAction( 'frontend/element_ready/bdt-open-street-map.default', widgetOpenStreetMap );
	});

}( jQuery, window.elementorFrontend ) );

/**
 * End open street map widget script
 */

