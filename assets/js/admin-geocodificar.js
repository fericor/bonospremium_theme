/**
 * LZ 26/08 — Geocodificación automática desde el campo "Dirección"
 * Escucha el textarea ACF de dirección; al escribir (debounce 1.5s) geocodifica
 * vía AJAX (Nominatim en el backend) y muestra el marcador en un mapa en vivo
 * (Google Maps JS con la key del panel Integraciones). Las coordenadas quedan
 * guardadas en mapa_lat / mapa_lng automáticamente.
 */
(function($) {
    'use strict';

    var FIELD_KEY = 'field_64e9de6de5643'; // campo ACF "Dirección"
    var $campo, $mapaCont, $estado;
    var timer = null;
    var mapa = null, marcador = null, geocoder = null;
    var ultimaDireccion = '';

    function inicializar() {
        $campo = $('textarea[name="acf[' + FIELD_KEY + ']"], #acf-' + FIELD_KEY + ' textarea, textarea[data-name="direccion"]').first();
        if (!$campo.length) {
            // ACF puede tardar en pintar los campos; reintentar
            setTimeout(inicializar, 800);
            return;
        }
        // Contenedor del mapa debajo del campo dirección
        if (!$('#bp-geo-map-wrap').length) {
            var $wrap = $('<div id="bp-geo-map-wrap">' +
                '<div id="bp-geo-map" style="width:100%;height:280px;border:1px solid #dcdcde;border-radius:6px;margin-top:8px;background:#f6f7f7;"></div>' +
                '<div id="bp-geo-estado" style="font-size:12px;color:#666;margin-top:4px;">Escribe la dirección para localizarla en el mapa.</div>' +
                '</div>');
            // Insertar después del label/field del textarea
            var $field = $campo.closest('.acf-field').length ? $campo.closest('.acf-field') : $campo.closest('tr');
            $field.after($wrap);
            $mapaCont = $('#bp-geo-map');
            $estado = $('#bp-geo-estado');
            cargarGoogleMaps();
        } else {
            $mapaCont = $('#bp-geo-map');
            $estado = $('#bp-geo-estado');
        }
        $campo.on('input change keyup', function() {
            clearTimeout(timer);
            timer = setTimeout(geocodificar, 1500);
        });
        // Geocodificar si ya hay dirección al cargar
        if ($campo.val() && $campo.val() !== ultimaDireccion) {
            clearTimeout(timer);
            timer = setTimeout(geocodificar, 800);
        }
    }

    function cargarGoogleMaps() {
        if (typeof window.google !== 'undefined' && window.google.maps) {
            prepararMapa();
            return;
        }
        if (!window.bp_geo || !bp_geo.maps_key) {
            $estado.text('Sin API key de Google Maps: no se puede mostrar el mapa, pero las coordenadas se guardarán igualmente al escribir la dirección.');
            return;
        }
        // Evitar cargar la API de Google Maps DOS veces. ACF también carga Maps para el
        // campo google_map (con libraries=places). Si google.maps aún no está listo,
        // esperar (poll) a que termine el script que ya está en la página y reutilizarlo.
        // Solo inyectar el nuestro si tras ~1.5s no hay ni google.maps ni un <script> de Maps.
        var intentos = 0, inyectado = false;
        var poll = setInterval(function() {
            intentos++;
            if (typeof window.google !== 'undefined' && window.google.maps) {
                clearInterval(poll);
                prepararMapa();
                return;
            }
            var hayScript = Array.prototype.some.call(document.getElementsByTagName('script'), function(scr) {
                return scr.src && scr.src.indexOf('maps.googleapis.com/maps/api/js') !== -1;
            });
            if (intentos >= 15 && !hayScript && !inyectado) {
                inyectado = true;
                window.bpGeoInitMap = prepararMapa;
                var s = document.createElement('script');
                s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(bp_geo.maps_key) + '&callback=bpGeoInitMap';
                s.async = true;
                s.defer = true;
                document.head.appendChild(s);
            }
            if (intentos > 60) {
                clearInterval(poll);
                $estado.text('El mapa de Google no cargó (¿key restringida por referrer?). Las coordenadas se guardarán igualmente al escribir la dirección.');
            }
        }, 100);
    }

    function prepararMapa() {
        if (!window.google || !window.google.maps || !$mapaCont) return;
        if (mapa) return;
        mapa = new google.maps.Map($mapaCont[0], {
            zoom: 14,
            center: { lat: 28.9784, lng: -13.6168 } // Lanzarote centro
        });
        marcador = new google.maps.Marker({ map: mapa });
        geocoder = new google.maps.Geocoder();
        // Si ya hay coordenadas guardadas, mostrar el marcador
        var lat = $('#acf-field_' + 'mapa_lat').length ? $('#acf-field_' + 'mapa_lat').val() : '';
        // Buscar en metadatos via data en el campo (mapa_lat no tiene input ACF visible, es meta)
    }

    function geocodificar() {
        var direccion = $.trim($campo.val());
        if (!direccion || direccion === ultimaDireccion) return;
        ultimaDireccion = direccion;
        $estado.text('Buscando la dirección…').css('color', '#666');
        $.post(bp_geo.ajax_url, {
            action: 'bp_geo_geocodificar',
            nonce: bp_geo.nonce,
            post_id: $('#post_ID').val() || (typeof wp !== 'undefined' && wp.data ? '' : ''),
            direccion: direccion
        }).done(function(res) {
            if (res && res.success) {
                $estado.text('✓ ' + (res.data.dir || direccion)).css('color', '#46b450');
                // Actualizar mapa si hay Google Maps
                if (mapa && marcador) {
                    var pos = { lat: parseFloat(res.data.lat), lng: parseFloat(res.data.lng) };
                    marcador.setPosition(pos);
                    mapa.setCenter(pos);
                    mapa.setZoom(15);
                } else if (geocoder) {
                    geocoder.geocode({ location: { lat: parseFloat(res.data.lat), lng: parseFloat(res.data.lng) } });
                }
                // Rellenar campos ocultos de lat/lng si existen en el admin
                $('#acf-field_mapa_lat, [data-name="mapa_lat"] input').val(res.data.lat);
                $('#acf-field_mapa_lng, [data-name="mapa_lng"] input').val(res.data.lng);
            } else {
                var msg = (res && res.data && res.data.mensaje) || 'No se pudo localizar la dirección.';
                $estado.text('⚠ ' + msg).css('color', '#b32d2e');
                ultimaDireccion = ''; // permitir reintentar
            }
        }).fail(function() {
            $estado.text('⚠ Error de conexión al geocodificar.').css('color', '#b32d2e');
        });
    }

    $(function() { setTimeout(inicializar, 500); });
})(jQuery);
