/**
 * LZ 27/08 — Barra de compartir + Mapa de todos los bonos (modal fullscreen)
 * - Compartir: WhatsApp, email, copiar enlace, Facebook, Telegram
 * - Mapa: el motor se elige en el panel (Apariencia → Ajustes BonosPremium,
 *   opción `map_provider`, Félix 16/10):
 *     · 'osm'      → Leaflet + teselas de OpenStreetMap (gratis, sin clave) — por defecto
 *     · 'maptiler' → SDK de MapTiler con el estilo elegido — necesita la API Key
 *   Capa/estilo por proveedor (panel): map_layer_osm (osm|osm_hot|carto_light|carto_dark|
 *   carto_voyager|topo|satellite) y map_layer_maptiler (streets|streets_pastel|basic|bright|
 *   topo|outdoor|satellite|hybrid|dataviz|voyager|toner).
 *   Con los dos motores: markers de todos los bonos con coordenadas; al pulsar un
 *   marker → tarjeta abajo con la info; al pulsar la tarjeta → ficha del bono.
 *
 * 15/10/2026 (Félix): SIN geolocalización del usuario y SIN movimientos
 *   automáticos de cámara. El mapa SIEMPRE arranca en el punto configurado en
 *   el panel y solo lo mueve el usuario (sin fitBounds: encuadrar el conjunto
 *   de bonos movía la cámara fuera del punto configurado). El radio de búsqueda
 *   (100–500 km) se mide desde ese punto —o desde el centro del mapa si el
 *   usuario lo ha movido— y se recalcula al deslizar el slider y al mover el
 *   mapa. El mapa se crea cuando el contenedor ya tiene tamaño real (si se
 *   creaba a 0×0 el SDK calculaba mal su viewport interno y la cámara saltaba
 *   al antimeridiano/ecuador dejando el mapa en blanco). Zoom inicial configurable en el panel de Apariencia (opcion `map_zoom`,
 *   por defecto 11), igual en móvil y escritorio.
 */
(function($) {
    'use strict';

    // ===== COMPARTIR =====
    function obtenerUrl() {
        return window.location.href.split('#')[0];
    }
    function obtenerTitulo() {
        return document.title || '';
    }
    function compartir(tipo) {
        var url = encodeURIComponent(obtenerUrl());
        var titulo = encodeURIComponent(obtenerTitulo());
        var texto = titulo;
        var destino = '';
        switch (tipo) {
            case 'whatsapp':
                destino = 'https://wa.me/?text=' + texto + '%20' + url;
                break;
            case 'email':
                destino = 'mailto:?subject=' + titulo + '&body=' + titulo + '%0A' + url;
                break;
            case 'facebook':
                destino = 'https://www.facebook.com/sharer/sharer.php?u=' + url;
                break;
            case 'telegram':
                destino = 'https://t.me/share/url?url=' + url + '&text=' + texto;
                break;
            case 'link':
                copiarEnlace();
                return;
            default:
                return;
        }
        window.open(destino, '_blank', 'noopener,width=650,height=550');
    }
    function copiarEnlace() {
        var url = obtenerUrl();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function() {
                aviso('Enlace copiado');
            }).catch(function() {
                fallbackCopiar(url);
            });
        } else {
            fallbackCopiar(url);
        }
    }
    function fallbackCopiar(texto) {
        var $tmp = $('<textarea style="position:fixed;opacity:0;">').val(texto).appendTo('body');
        $tmp.select();
        try { document.execCommand('copy'); aviso('Enlace copiado'); } catch (e) { aviso('No se pudo copiar'); }
        $tmp.remove();
    }
    function aviso(msg) {
        var $t = $('#bp-share-toast');
        if (!$t.length) {
            $t = $('<div id="bp-share-toast"></div>').appendTo('body');
        }
        $t.text(msg).addClass('show');
        clearTimeout(aviso._t);
        aviso._t = setTimeout(function() { $t.removeClass('show'); }, 1800);
    }

    // ===== PROVEEDOR Y CAPAS DEL MAPA (panel: BonosPremium -> Integraciones) =====
    // Proveedor (bp_compartir.map_provider):
    //   'osm'      -> Leaflet 1.9.4 (encolado desde functions.php) + capa de teselas elegida
    //   'maptiler' -> maptilersdk (encolado desde functions.php) + estilo elegido + API Key
    // Capa/estilo: el panel guarda map_layer_osm y map_layer_maptiler (16/10).
    var BP_CAPAS_OSM = {
        osm:           { url: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png', maxZoom: 19,
                         attr: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>' },
        osm_hot:       { url: 'https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', maxZoom: 19,
                         attr: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> - <a href="https://www.hotosm.org/" target="_blank" rel="noopener">Humanitarian OSM Team</a>' },
        carto_light:   { url: 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', maxZoom: 20,
                         attr: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions" target="_blank" rel="noopener">CARTO</a>' },
        carto_dark:    { url: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', maxZoom: 20,
                         attr: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions" target="_blank" rel="noopener">CARTO</a>' },
        carto_voyager: { url: 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', maxZoom: 20,
                         attr: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions" target="_blank" rel="noopener">CARTO</a>' },
        topo:          { url: 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', maxZoom: 17,
                         attr: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>, SRTM - &copy; <a href="https://opentopomap.org" target="_blank" rel="noopener">OpenTopoMap</a> (CC-BY-SA)' },
        satellite:     { url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', maxZoom: 19,
                         attr: 'Teselas &copy; <a href="https://www.esri.com/" target="_blank" rel="noopener">Esri</a>, Maxar, Earthstar Geographics' }
    };
    function bpCapaOsm() {
        var k = (window.bp_compartir && bp_compartir.map_layer_osm) ? String(bp_compartir.map_layer_osm) : 'osm';
        return BP_CAPAS_OSM[k] || BP_CAPAS_OSM.osm;
    }
    // Estilos del SDK de MapTiler (los objetos salen del propio SDK; si falta, STREETS)
    function bpEstiloMapTiler() {
        var k = (window.bp_compartir && bp_compartir.map_layer_maptiler) ? String(bp_compartir.map_layer_maptiler) : 'streets';
        var M = (window.maptilersdk && window.maptilersdk.MapStyle) || {};
        var estilos = {
            streets:        M.STREETS,
            streets_pastel: (M.STREETS && M.STREETS.PASTEL),
            basic:          M.BASIC,
            bright:         M.BRIGHT,
            topo:           M.TOPO,
            outdoor:        M.OUTDOOR,
            satellite:      M.SATELLITE,
            hybrid:         M.HYBRID,
            dataviz:        M.DATAVIZ,
            voyager:        M.VOYAGER,
            toner:          M.TONER
        };
        return estilos[k] || M.STREETS || 'streets-v4';
    }

    // ---- Motor Google Maps (16/10) ----
    function bpEsGmap() { return bpProveedor() === 'gmap'; }
    // Capas de Google: las propias de su API (no confundir con map_layer_osm)
    var BP_CAPAS_GMAP = { roadmap: 'roadmap', satellite: 'satellite', hybrid: 'hybrid', terrain: 'terrain' };
    function bpCapaGmap() {
        var k = (window.bp_compartir && bp_compartir.map_layer_gmap) ? String(bp_compartir.map_layer_gmap) : 'roadmap';
        return BP_CAPAS_GMAP[k] || BP_CAPAS_GMAP.roadmap;
    }
    // La clave de Google viaja en el src de la API (exigencia de Google); el
    // localize solo la publica si el proveedor activo es Google.
    function bpGmapKey() { return (window.bp_compartir && bp_compartir.maps_api_key) || ''; }
    // Google llama a esta función global cuando la clave no vale (caducada, sin
    // facturación, restringida a otro dominio...). Sin esto el mapa queda gris.
    var BP_GMAP_MSG = 'Google Maps rechazó la API Key (revisa la clave y la facturación en la consola de Google). Mientras tanto, cambia el proveedor a <strong>OpenStreetMap</strong> en el panel del tema.';
    window.gm_authFailure = function() {
        if (!bpEsGmap()) { return; }
        window.bpGmapAuthFallo = true;
        avisoMapa(BP_GMAP_MSG);
        var f = document.querySelector('.bp-bono-mapa');
        if (f && !f.querySelector('.gm-style')) {
            f.innerHTML = '<p style="padding:20px;text-align:center;color:#888;font-size:14px;margin:0;">No se pudo dibujar el mapa de Google (revisa la API Key en el panel).</p>';
        }
    };

    function bpProveedor() {
        var p = (window.bp_compartir && bp_compartir.map_provider) ? String(bp_compartir.map_provider) : 'osm';
        return (p === 'maptiler' || p === 'gmap') ? p : 'osm';
    }
    function bpEsOsm() { return bpProveedor() === 'osm'; }

    // Espera a que la librería del proveedor activo esté lista (se encola desde
    // functions.php, así que debería estarlo; si no, se reintenta hasta 5 s).
    function cargarLibreriaMapa() {
        return new Promise(function(resolve, reject) {
            if (bpEsGmap() && !bpGmapKey()) {
                reject(new Error('falta la API Key de Google Maps')); return;
            }
            function listo() {
                if (bpEsOsm()) return (window.L && window.L.map);
                if (bpEsGmap()) return (window.google && window.google.maps && window.google.maps.Map);
                return (window.maptilersdk && window.maptilersdk.Map);
            }
            if (listo()) { resolve(); return; }
            var espera = 0;
            var t = setInterval(function() {
                espera += 100;
                if (listo()) { clearInterval(t); resolve(); return; }
                if (espera > 5000) {
                    clearInterval(t);
                    reject(new Error(bpEsOsm() ? 'Leaflet no disponible' : 'MapTiler SDK no disponible'));
                }
            }, 100);
        });
    }

    // Aviso dentro del contenedor del mapa (nunca destruye un mapa ya creado)
    function avisoMapa(html) {
        var el = document.getElementById('bp-map-bonos');
        if (!el) return;
        el.innerHTML = '<p style="padding:40px;text-align:center;color:#888;">' + html + '</p>';
    }

    // ===== MAPA DEL BONO EN LA FICHA =====
    // Mismo proveedor que el mapa de bonos (selector del panel).

    // Silencia el aviso "Image could not be loaded" de MapLibre (solo MapTiler):
    // los estilos de MapTiler referencian iconos decorativos (p. ej.
    // transportation:road_) que no existen en el sprite; con este handler se
    // registran como transparentes y el mapa se dibuja igual (LZ 08/09).
    function bpSilenciarIconosFaltantes(m) {
        if (!m || !m.on) return;
        m.on('styleimagemissing', function(e) {
            try {
                if (e && e.id && !m.hasImage(e.id)) {
                    var c = document.createElement('canvas');
                    c.width = 1; c.height = 1;
                    m.addImage(e.id, c);
                }
            } catch (err) { /* noop */ }
        });
    }

    function iniciarMapaBonoFicha() {
        $('.bp-bono-mapa').each(function() {
            var $el = $(this);
            var lat = parseFloat($el.data('lat')), lng = parseFloat($el.data('lng'));
            if (!lat || !lng || $el.hasClass('bp-map-listos')) return;
            $el.addClass('bp-map-listos');
            cargarLibreriaMapa().then(function() {
                if (!$el.is(':visible')) return;   // no inicializar si está oculto
                if (bpEsOsm()) {
                    // Leaflet: mapa de la ubicación del bono + marcador estándar
                    var mc = L.map($el[0], {
                        center: [lat, lng],
                        zoom: 15,
                        zoomControl: false,
                        attributionControl: true,
                        scrollWheelZoom: false
                    });
                    var capaFicha = bpCapaOsm();
                    L.tileLayer(capaFicha.url, { maxZoom: capaFicha.maxZoom, attribution: capaFicha.attr }).addTo(mc);
                    L.marker([lat, lng]).addTo(mc);
                    setTimeout(function() { try { mc.invalidateSize(); } catch (e) {} }, 300);
                    $el.data('bp-map', mc);
                    return;
                }
                if (bpEsGmap()) {
                    // Google Maps en la ficha: misma capa que en el panel
                    if (!bpGmapKey()) return;
                    if (window.bpGmapAuthFallo) {
                        $el.html('<p style="padding:20px;text-align:center;color:#888;font-size:14px;margin:0;">No se pudo dibujar el mapa de Google (revisa la API Key en el panel).</p>');
                        return;
                    }
                    var gm = new google.maps.Map($el[0], {
                        center: { lat: lat, lng: lng },
                        zoom: 15,
                        mapTypeId: bpCapaGmap(),
                        disableDefaultUI: true,
                        mapTypeControl: false,
                        streetViewControl: false,
                        fullscreenControl: false,
                        clickableIcons: false,
                        gestureHandling: 'cooperative'
                    });
                    new google.maps.Marker({ position: { lat: lat, lng: lng }, map: gm });
                    setTimeout(function() { try { google.maps.event.trigger(gm, 'resize'); } catch (e) {} }, 300);
                    $el.data('bp-map', gm);
                    return;
                }
                var key = (window.bp_compartir && bp_compartir.maptiler_api_key) || '';
                if (!key) return;
                maptilersdk.config.apiKey = key;
                var m = new maptilersdk.Map({
                    container: $el[0],
                    style: bpEstiloMapTiler(),
                    center: [lng, lat],
                    zoom: 15,
                    attributionControl: true,
                    // Sin controles por defecto del SDK (solo el marcador, Félix 09/09)
                    navigationControl: false,
                    geolocateControl: false
                });
                setTimeout(function() { try { m.resize(); } catch (e) {} }, 300);
                bpSilenciarIconosFaltantes(m);
                // No atrapar el scroll de la página con la rueda del ratón
                try { m.scrollZoom.disable(); } catch (e) {}
                new maptilersdk.Marker()
                    .setLngLat([lng, lat])
                    .addTo(m);
                $el.data('bp-map', m);
            }).catch(function() { /* sin librería: el contenedor se queda vacío */ });
        });
    }
    // Inicializar los mapas de ficha ya presentes al cargar
    $(function() { iniciarMapaBonoFicha(); });

    // ===== MAPA DE BONOS (modal) — Leaflet (OSM) o MapTiler SDK =====

    // Centro configurado en el panel (Apariencia → Ajustes BonosPremium), con
    // respaldo al centro histórico de Lanzarote si el valor está vacío/no es número.
    function bpCentroConfig(eje, defecto) {
        var v = (window.bp_compartir && bp_compartir['map_center_' + eje]) || '';
        var n = parseFloat(v);
        return isFinite(n) ? n : defecto;
    }

    // Zoom inicial configurado en el panel (Ajustes BonosPremium → Zoom del mapa).
    // Se aplica igual en móvil y escritorio; en pantalla estrecha conviene bajarlo
    // porque abarca menos terreno y los bonos de las islas quedan fuera de la vista.
    function bpZoomConfig() {
        var v = (window.bp_compartir && bp_compartir.map_zoom);
        var z = parseInt(v, 10);
        return (isFinite(z) && z >= 1 && z <= 20) ? z : 11;
    }

    var mapa = null, markers = [], bonosData = [];
    var radioKm = 100;           // radio de búsqueda inicial (Félix 15/10)
    var vacioCerrado = false;    // true si el usuario cerró el aviso "Sin bonos en este radio"
    // Rango del selector de radio, en km (Félix 15/10: inicio 100, hasta 500)
    var RADIO_MIN = 100, RADIO_MAX = 500;
    var camaraMovidaPorUsuario = false;   // true en cuanto el usuario arrastra/zoomea el mapa

    // Recalcular el tamaño del mapa. MapTiler expone resize(), Leaflet invalidateSize().
    function mapaResize() {
        if (!mapa) return;
        try {
            if (typeof mapa.invalidateSize === 'function') { mapa.invalidateSize(); }
            else if (typeof mapa.resize === 'function') { mapa.resize(); }
            else if (window.google && google.maps && google.maps.event) { google.maps.event.trigger(mapa, 'resize'); }
        } catch (e) { /* noop */ }
    }

    // Distancia en km entre dos coordenadas (Haversine)
    function distanciaKm(lat1, lng1, lat2, lng2) {
        var R = 6371;
        var dLat = (lat2 - lat1) * Math.PI / 180;
        var dLng = (lng2 - lng1) * Math.PI / 180;
        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLng / 2) * Math.sin(dLng / 2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    // Centro desde el que se mide el radio de búsqueda: el centro actual del
    // mapa (el punto configurado en el panel o donde esté mirando el usuario).
    // NO se usa la geolocalización del navegador (Félix 15/10): el mapa nunca
    // se recentra por la ubicación del visitante.
    function centroBusqueda() {
        // Mientras el usuario no haya movido el mapa, la búsqueda se mide SIEMPRE
        // desde el punto configurado en el panel: el SDK puede devolver un centro
        // desviado si el contenedor no tenía tamaño al crear el mapa (Félix 15/10).
        if (!camaraMovidaPorUsuario) {
            var cfg = centroConfigurado();
            if (cfg) return cfg;
        }
        if (mapa && mapa.getCenter) {
            try {
                // MapTiler devuelve LngLat {lng, lat}; Leaflet LatLng {lat, lng}.
                // Ambos exponen .lat y .lng, así que sirve el mismo código.
                var c = mapa.getCenter();
                // Google devuelve un LatLng con lat()/lng() como métodos; Leaflet y
                // el SDK de MapTiler exponen propiedades numéricas.
                if (c) {
                    if (typeof c.lat === 'function') { c = { lat: c.lat(), lng: c.lng() }; }
                }
                if (c && isFinite(c.lat) && isFinite(c.lng)) {
                    var lng = c.lng;
                    while (lng > 180) { lng -= 360; }    // el SDK puede devolver el wrap
                    while (lng < -180) { lng += 360; }
                    return { lat: c.lat, lng: lng };
                }
            } catch (e) { /* noop */ }
        }
        return centroideBonos();   // respaldo: media de las coordenadas de los bonos
    }

    // Punto configurado en el panel (Apariencia → Ajustes BonosPremium)
    function centroConfigurado() {
        var lat = bpCentroConfig('lat', NaN), lng = bpCentroConfig('lng', NaN);
        return (isFinite(lat) && isFinite(lng)) ? { lat: lat, lng: lng } : null;
    }

    function centroideBonos() {
        var n = 0, slat = 0, slng = 0;
        (bonosData || []).forEach(function(b) {
            var lat = parseFloat(b.lat), lng = parseFloat(b.lng);
            if (!lat || !lng) return;
            slat += lat; slng += lng; n++;
        });
        return n ? { lat: slat / n, lng: slng / n } : null;
    }

    // Devuelve los bonos dentro del radio seleccionado, medido desde el CENTRO
    // del mapa (Félix 15/10). Sin geolocalización del visitante.
    function bonosCercanos() {
        // Si el usuario cerró el aviso de radio vacío → ver TODOS los bonos del mapa
        if (vacioCerrado) return bonosData;
        var centro = centroBusqueda();
        if (!centro) return bonosData;
        var dentro = (bonosData || []).filter(function(b) {
            var lat = parseFloat(b.lat), lng = parseFloat(b.lng);
            if (!lat || !lng) return false;
            return distanciaKm(centro.lat, centro.lng, lat, lng) <= radioKm;
        });
        if (!dentro.length) {
            // No hay bonos en el radio → overlay elegante (sin forzar la cámara)
            $('#bp-map-vacio-km').text(radioKm + ' km');
            $('#bp-map-vacio').show();
            return [];
        }
        $('#bp-map-vacio').hide();
        return dentro;
    }

    function abrirMapa() {
        var $modal = $('#bp-map-modal');
        if (!$modal.length) return;
        $modal.addClass('is-open');
        $('body').css('overflow', 'hidden');
        cargarLibreriaMapa().then(function() {
            if (mapa) { mapaResize(); return; }
            // El modal se abre con transición: si se crea el mapa con el
            // contenedor a 0×0 el SDK fija mal su viewport interno y cualquier
            // encuadre/zoom salta a coordenadas absurdas. Esperamos al tamaño real.
            crearCuandoVisible(0);
        }).catch(function(err) {
            avisoMapa('No se pudo cargar el mapa' + (err && err.message ? ' (' + err.message + ')' : '') + '.');
        });
    }

    // Espera a que el contenedor del mapa tenga tamaño real (hasta 2 s) y lo crea.
    function crearCuandoVisible(intentos) {
        var el = document.getElementById('bp-map-bonos');
        var w = el ? el.clientWidth : 0, h = el ? el.clientHeight : 0;
        if ((w < 50 || h < 50) && intentos < 20) {
            setTimeout(function() { crearCuandoVisible(intentos + 1); }, 100);
            return;
        }
        crearMapa();
    }

    function crearMapa() {
        if (mapa) return;
        if (bpEsOsm()) { crearMapaOsm(); return; }
        if (bpEsGmap()) { crearMapaGmap(); return; }
        crearMapaMapTiler();
    }

    // ---- OpenStreetMap (Leaflet) ----
    function crearMapaOsm() {
        if (typeof L === 'undefined' || !L.map) {
            avisoMapa('No se pudo cargar el mapa de OpenStreetMap (falta Leaflet).');
            return;
        }
        mapa = L.map('bp-map-bonos', {
            center: [bpCentroConfig('lat', 28.9784), bpCentroConfig('lng', -13.6168)],
            zoom: bpZoomConfig(),
            zoomControl: false,
            attributionControl: true
        });
        var capaMapa = bpCapaOsm();
        L.tileLayer(capaMapa.url, { maxZoom: capaMapa.maxZoom, attribution: capaMapa.attr }).addTo(mapa);
        L.control.zoom({ position: 'topright' }).addTo(mapa);
        // Recalcular dimensiones: al abrir y tras la transición del modal.
        setTimeout(mapaResize, 300);
        setTimeout(mapaResize, 1200);
        // 15/10: al mover el mapa (arrastre/zoom del usuario) se recalcula la
        // búsqueda por radio desde el nuevo centro. Leaflet no adjunta
        // originalEvent a moveend, así que se escuchan dragend/zoomend.
        mapa.on('dragend', function() { camaraMovidaPorUsuario = true; pintarMarkers(); });
        mapa.on('zoomend', function() { camaraMovidaPorUsuario = true; pintarMarkers(); });
        // Leaflet no emite un 'load' de estilo: los bonos se cargan ya.
        cargarBonos();
    }

    // ---- Google Maps ----
    // Clase de pin HTML (OverlayView): Google no admite HTML en Marker sin mapId,
    // así que pintamos el mismo .bp-map-pin del tema en un pane propio, anclado
    // por su punta inferior igual que en Leaflet/MapTiler.
    var BpPinOverlay = null;
    function bpPinOverlayClase() {
        if (BpPinOverlay) return BpPinOverlay;
        function Pin(latlng, html, alPulsar) {
            this.latlng_ = latlng; this.html_ = html; this.alPulsar_ = alPulsar;
        }
        Pin.prototype = new google.maps.OverlayView();
        Pin.prototype.onAdd = function() {
            var d = document.createElement('div');
            d.className = 'bp-gmap-pin';
            d.style.position = 'absolute';
            d.style.transform = 'translate(-50%, -100%)';   // ancla en la punta
            d.style.cursor = 'pointer';
            d.innerHTML = this.html_;
            if (this.alPulsar_) { d.addEventListener('click', this.alPulsar_); }
            this.div_ = d;
            this.getPanes().overlayMouseTarget.appendChild(d);
        };
        Pin.prototype.draw = function() {
            var p = this.getProjection() ? this.getProjection().fromLatLngToDivPixel(this.latlng_) : null;
            if (!p || !this.div_) return;
            this.div_.style.left = p.x + 'px';
            this.div_.style.top = p.y + 'px';
        };
        Pin.prototype.onRemove = function() {
            if (this.div_ && this.div_.parentNode) { this.div_.parentNode.removeChild(this.div_); }
            this.div_ = null;
        };
        BpPinOverlay = Pin;
        return Pin;
    }

    function crearMapaGmap() {
        if (window.bpGmapAuthFallo) { avisoMapa(BP_GMAP_MSG); return; }
        if (typeof google === 'undefined' || !google.maps || !google.maps.Map) {
            avisoMapa('No se pudo cargar el mapa de Google Maps.');
            return;
        }
        mapa = new google.maps.Map(document.getElementById('bp-map-bonos'), {
            center: { lat: bpCentroConfig('lat', 28.9784), lng: bpCentroConfig('lng', -13.6168) },
            zoom: bpZoomConfig(),
            mapTypeId: bpCapaGmap(),          // elegida en el panel (roadmap/satellite/hybrid/terrain)
            disableDefaultUI: true,
            zoomControl: true,
            zoomControlOptions: { position: google.maps.ControlPosition.RIGHT_TOP },
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: false,
            clickableIcons: false,
            gestureHandling: 'cooperative'    // no atrapar el scroll de la página
        });
        setTimeout(mapaResize, 300);
        setTimeout(mapaResize, 1200);
        // Igual que Leaflet: si el usuario mueve el mapa, se recalcula la búsqueda por radio
        mapa.addListener('dragend', function() { camaraMovidaPorUsuario = true; pintarMarkers(); });
        mapa.addListener('zoom_changed', function() { camaraMovidaPorUsuario = true; pintarMarkers(); });
        cargarBonos();
    }

    // Marker Google: burbuja con el precio, mismo HTML/CSS que los otros motores
    function pintarMarkersGmap(lista) {
        markers.forEach(function(m) { try { m.setMap(null); } catch (e) {} });
        markers = [];
        if (!lista || !lista.length) return;
        var Pin = bpPinOverlayClase();
        lista.forEach(function(b) {
            var lat = parseFloat(b.lat), lng = parseFloat(b.lng);
            if (!lat || !lng) return;
            var html = '<div class="bp-map-pin" title="' + $('<span>').text(b.titulo || b.nombre || '').html() + '">' +
                       '<span class="bp-map-pin-ico">' + $('<span>').text(precioMarker(b)).html() + '</span></div>';
            var ov = new Pin(new google.maps.LatLng(lat, lng), html, function() { mostrarTarjeta(b); });
            ov.setMap(mapa);
            markers.push(ov);
        });
    }

    // ---- MapTiler (maptilersdk) ----
    function crearMapaMapTiler() {
        var key = (window.bp_compartir && bp_compartir.maptiler_api_key) || '';
        if (!key) {
            avisoMapa('Falta la API Key de MapTiler. Pégala en el panel: Apariencia → Ajustes BonosPremium → Integraciones, o cambia el proveedor del mapa a OpenStreetMap.');
            return;
        }
        maptilersdk.config.apiKey = key;
        mapa = new maptilersdk.Map({
            container: 'bp-map-bonos',
            style: bpEstiloMapTiler(),
            center: [
                bpCentroConfig('lng', -13.6168),
                bpCentroConfig('lat', 28.9784)
            ],
            zoom: bpZoomConfig(),   // configurable en el panel (por defecto 11)
            attributionControl: true,
            // El SDK añade por defecto su propio NavigationControl y GeolocateControl
            // (top-right) — los desactivamos y dejamos SOLO el nuestro (Félix 09/09:
            // se veían los botones de zoom duplicados).
            navigationControl: false,
            geolocateControl: false
        });
        // Recalcular dimensiones: al abrir y tras la transición del modal.
        setTimeout(function() { try { if (mapa) mapa.resize(); } catch (e) {} }, 300);
        setTimeout(function() { try { if (mapa) mapa.resize(); } catch (e) {} }, 1200);
        bpSilenciarIconosFaltantes(mapa);
        mapa.addControl(new maptilersdk.NavigationControl({ showCompass: false }), 'top-right');
        // Errores del SDK: NO tocar el contenedor (eso destruiría el mapa).
        // Con la clave correcta el style carga; los errores menores (tiles,
        // fuentes) se ignoran y el mapa sigue funcionando.
        mapa.on('error', function(e) {
            if (window.console && console.warn) console.warn('MapTiler:', e && e.error ? e.error.message : 'error');
        });
        // 15/10: al mover el mapa (arrastre/zoom del usuario) se recalcula la
        // búsqueda por radio desde el nuevo centro. Solo con eventos del usuario:
        // los moveend internos (fitBounds/resize) no deben re-buscar si el SDK
        // devuelve un centro desplazado (el bug del antimeridiano).
        mapa.on('moveend', function(e) {
            if (!e || !e.originalEvent) return;   // movimientos internos del SDK: ignorar
            camaraMovidaPorUsuario = true;
            pintarMarkers();
        });
        // Al cargar: asegurar el tamaño y cargar los bonos (sin mover la cámara)
        mapa.on('load', function() { try { mapa.resize(); } catch (e) {} cargarBonos(); });
    }

    function cerrarMapa() {
        $('#bp-map-modal').removeClass('is-open');
        $('body').css('overflow', '');
        $('#bp-map-tarjeta').hide();
        $('#bp-map-vacio').hide();
        vacioCerrado = false;   // al reabrir el mapa, el aviso puede volver a mostrarse
    }

    function cargarBonos() {
        if (!window.bp_compartir || !bp_compartir.ajax_url) return;
        $.post(bp_compartir.ajax_url, { action: 'bp_bonos_mapa' }).done(function(res) {
            if (res && res.success) {
                bonosData = res.data || [];
                pintarMarkers();
            } else {
                // Sin datos: aviso cerrable, el mapa sigue cargado (FIX 07/09)
                $('#bp-map-vacio-km').text(radioKm + ' km');
                $('#bp-map-vacio').show();
            }
        }).fail(function() {
            // Error de red: aviso cerrable, el mapa sigue cargado (FIX 07/09)
            $('#bp-map-vacio-km').text(radioKm + ' km');
            $('#bp-map-vacio').show();
        });
    }

    // Pintar los markers del radio actual con el motor activo. NO mueve la cámara:
    // el mapa se queda donde lo puso el usuario (o en el punto del panel), que es
    // lo que pidió Félix (15/10). El encuadre automático movía la cámara.
    function pintarMarkers() {
        if (!mapa) return;
        var lista = bonosCercanos();
        if (bpEsOsm()) { pintarMarkersOsm(lista); }
        else if (bpEsGmap()) { pintarMarkersGmap(lista); }
        else { pintarMarkersMapLibre(lista); }
    }

    // Texto del precio para el marker: precio plano, "Desde X" si es variable, fallback '€'
    function precioMarker(b) {
        if (b.precio_txt) return b.precio_txt;
        if (b.precio) return '€';
        return '•';
    }

    // Marker Leaflet: burbuja blanca con el precio en negro (mismo HTML/CSS que
    // MapTiler, .bp-map-pin + .bp-map-pin-ico). El divIcon se ancla por su punta
    // inferior con la regla CSS .leaflet-marker-icon.bp-leaflet-pin > .bp-map-pin.
    function pintarMarkersOsm(lista) {
        markers.forEach(function(m) { try { m.remove(); } catch (e) {} });
        markers = [];
        if (!lista || !lista.length) return;
        lista.forEach(function(b) {
            var lat = parseFloat(b.lat), lng = parseFloat(b.lng);
            if (!lat || !lng) return;
            var el = document.createElement('div');
            el.className = 'bp-map-pin';
            var inner = document.createElement('span');
            inner.className = 'bp-map-pin-ico';
            inner.textContent = precioMarker(b);
            el.appendChild(inner);
            el.title = b.titulo || b.nombre || '';
            el.style.cursor = 'pointer';
            var icono = L.divIcon({ className: 'bp-leaflet-pin', html: el, iconSize: [0, 0] });
            var m = L.marker([lat, lng], { icon: icono, riseOnHover: true }).addTo(mapa);
            m.on('click', function() { mostrarTarjeta(b); });
            markers.push(m);
        });
    }

    // Marker MapTiler: burbuja blanca con el precio en negro.
    // Usa un elemento HTML → mismo CSS .bp-map-pin-ico.
    function pintarMarkersMapLibre(lista) {
        // Limpiar markers previos; si no hay bonos en el área NO se toca el mapa
        // (el overlay "Sin bonos" se gestiona en bonosCercanos). FIX 07/09.
        markers.forEach(function(m) { try { m.remove(); } catch (e) {} });
        markers = [];
        if (!lista || !lista.length) return;
        lista.forEach(function(b) {
            var lat = parseFloat(b.lat), lng = parseFloat(b.lng);
            if (!lat || !lng) return;
            var txt = precioMarker(b);
            var el = document.createElement('div');
            el.className = 'bp-map-pin';
            var inner = document.createElement('span');
            inner.className = 'bp-map-pin-ico';
            inner.textContent = txt;
            el.appendChild(inner);
            el.title = b.titulo || b.nombre || '';
            el.style.cursor = 'pointer';
            var m = new maptilersdk.Marker({ element: el, anchor: 'bottom' })
                .setLngLat([lng, lat])
                .addTo(mapa);
            el.addEventListener('click', function() { mostrarTarjeta(b); });
            markers.push(m);
        });
    }

    function mostrarTarjeta(b) {
        var $t = $('#bp-map-tarjeta');
        $t.html(
            '<div class="bp-map-tarjeta-inner">' +
                (b.img ? '<img class="bp-map-tarjeta-img" src="' + b.img + '" alt="">' : '<div class="bp-map-tarjeta-img"></div>') +
                '<div class="bp-map-tarjeta-info">' +
                    (b.nombre ? '<p class="bp-map-tarjeta-nombre">' + $('<span>').text(b.nombre).html() + '</p>' : '') +
                    '<p class="bp-map-tarjeta-titulo">' + $('<span>').text(b.titulo).html() + '</p>' +
                    '<p class="bp-map-tarjeta-meta">' +
                        (b.localidad ? '<span>' + $('<span>').text(b.localidad).html() + '</span>' : '') +
                        (b.precio_txt ? '<span>' + $('<span>').text(b.precio_txt).html() + '</span>' : '') +
                    '</p>' +
                '</div>' +
                '<span class="bp-map-tarjeta-arrow">›</span>' +
            '</div>'
        ).attr('data-url', b.url).show();
    }

    // ===== EVENTOS =====
    $(document).on('click', '.bp-share-btn[data-share]', function() {
        var tipo = $(this).data('share');
        if (tipo === 'link') { copiarEnlace(); }
        else { compartir(tipo); }
    });
    $('#bp-btn-mapa-bonos').on('click', function(e) { e.preventDefault(); abrirMapa(); });
    // LZ 03/09: enlace del menú de WP con clase 'bp-open-map' (o href="#mapa")
    // abre el modal del mapa desde cualquier página. Delegado para enlaces
    // creados desde Apariencia → Menús.
    $(document).on('click', '.bp-open-map, a[href="#mapa"]', function(e) {
        e.preventDefault();
        // Cerrar menú hamburguesa si está abierto
        $('.bp-user-nav-menu').removeClass('open');
        $('.bp-menu-toggle').removeClass('active');
        $('.bp-menu-toggle i').removeClass('fa-times').addClass('fa-bars');
        abrirMapa();
    });
    // LZ 05/09: añade el icono del mapa al enlace del menú desplegable (user-menu)
    // si el ítem fue creado desde Apariencia → Menús (no lleva icono).
    $('.bp-user-nav-menu a.bp-open-map, .bp-user-nav-menu a[href="#mapa"]').each(function() {
        if ($(this).find('i').length === 0 && $(this).find('img').length === 0) {
            var iconUrl = (window.bp_compartir && bp_compartir.template_uri) ? bp_compartir.template_uri + '/assets/icons/map-location-dot-light.svg' : '';
            if (!iconUrl) return;
            $(this).prepend('<img src="' + iconUrl + '" alt="" class="bp-map-icon-img" width="16" height="16" /> ');
        }
    });
    $('.bp-map-modal-close').on('click', function() { cerrarMapa(); });
    // Cerrar el aviso "Sin bonos en este radio" → ver TODOS los bonos del mapa (07/09)
    $('#bp-map-vacio-cerrar').on('click', function() {
        vacioCerrado = true;
        $('#bp-map-vacio').hide();
        if (mapa) { pintarMarkers(); }
    });
    // Slider de radio de búsqueda (100–500 km, Félix 15/10): cada deslizamiento
    // vuelve a buscar los bonos dentro del nuevo radio (medido desde el centro del mapa).
    $('#bp-map-rango-input').on('input', function() {
        radioKm = parseInt($(this).val(), 10);
        if (!isFinite(radioKm)) radioKm = RADIO_MIN;
        if (radioKm < RADIO_MIN) radioKm = RADIO_MIN;
        if (radioKm > RADIO_MAX) radioKm = RADIO_MAX;
        vacioCerrado = false;   // el aviso puede volver a aparecer si el radio sigue vacío
        $('#bp-map-rango-val').text(radioKm + ' km');
        if (mapa) { pintarMarkers(); }
    }).on('change', function() {
        if (mapa) { pintarMarkers(); }
        // (no se encuadra la cámara: se mantiene donde está el usuario)
    });
    // Botón "Ampliar radio" del overlay de sin bonos → al máximo del selector
    $('#bp-map-vacio-btn').on('click', function() {
        radioKm = RADIO_MAX;
        vacioCerrado = false;
        $('#bp-map-rango-input').val(radioKm);
        $('#bp-map-rango-val').text(radioKm + ' km');
        $('#bp-map-vacio').hide();
        if (mapa) { pintarMarkers(); }
    });
    $('#bp-map-tarjeta').on('click', function() {
        var url = $(this).attr('data-url');
        if (url) { window.location.href = url; }
    });
    $(document).on('keyup', function(e) {
        if (e.key === 'Escape') { cerrarMapa(); }
    });

})(jQuery);
