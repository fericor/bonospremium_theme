/**
 * LZ 27/08 — Barra de compartir + Mapa de todos los bonos (modal fullscreen)
 * - Compartir: WhatsApp, email, copiar enlace, Facebook, Telegram
 * - Mapa: MapTiler (MapLibre GL + style.json streets-v4-pastel, capa STREETS.PASTEL)
 *   con fallback a Leaflet/OpenStreetMap si MapTiler falla; markers de todos los
 *   bonos con coordenadas. Al pulsar un marker → tarjeta abajo con la info;
 *   al pulsar la tarjeta → redirige a la ficha del bono.
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

    // ===== MAPA DEL BONO EN LA FICHA (LZ 08/09) =====
    // Ahora usa el MapTiler SDK (misma capa STREETS que el mapa de bonos).

    // Silencia el aviso "Image could not be loaded" de MapLibre: los estilos de
    // MapTiler referencian iconos decorativos (p. ej. transportation:road_) que
    // no existen en el sprite; con este handler se registran como transparentes
    // y el mapa se dibuja igual (LZ 08/09).
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
            cargarMapLibre().then(function() {
                if (!$el.is(':visible')) return;   // no inicializar si está oculto
                var key = (window.bp_compartir && bp_compartir.maptiler_api_key) || '';
                if (!key) return;
                maptilersdk.config.apiKey = key;
                var m = new maptilersdk.Map({
                    container: $el[0],
                    style: maptilersdk.MapStyle.STREETS,
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
            });
        });
    }
    // Inicializar los mapas de ficha ya presentes al cargar
    $(function() { iniciarMapaBonoFicha(); });

    // ===== MAPA DE BONOS (SOLO MapTiler SDK — LZ 08/09) =====

    // El SDK de MapTiler (maptilersdk) se carga de forma ESTÁNDAR desde
    // functions.php (wp_enqueue_script 'bp-lz-maptiler' + su CSS), igual que el
    // ejemplo oficial "Display a map". Aquí solo comprobamos que ya está listo:
    // si no, se espera al DOM-ready (los scripts con dependencia se cargan antes).
    function cargarMapLibre() {
        return new Promise(function(resolve, reject) {
            if (window.maptilersdk && maptilersdk.Map) { resolve(); return; }
            // El SDK no llegó (bloqueado por red/adblock): reintentar una vez
            // escuchando el siguiente 'load' del window; si sigue sin estar,
            // rechazar para mostrar el aviso en el contenedor.
            var espera = 0;
            var t = setInterval(function() {
                espera += 100;
                if (window.maptilersdk && maptilersdk.Map) { clearInterval(t); resolve(); return; }
                if (espera > 5000) { clearInterval(t); reject(new Error('MapTiler SDK no disponible')); }
            }, 100);
        });
    }

    var mapa = null, markers = [], bonosData = [];
    var miUbicacion = null;      // {lat, lng} de la geolocalización
    var markerUbicacion = null;  // marker/pin de "tu ubicación"
    var radioKm = 25;            // radio de búsqueda seleccionado por el usuario (slider)
    var vacioCerrado = false;    // true si el usuario cerró el aviso "Sin bonos en este radio"
    var CENTRO_TENERIFE = { lat: 28.2916, lng: -16.6291 }; // centro aproximado de la isla

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

    // Pide permiso de ubicación al usuario; si lo da, reordena los bonos por cercanía
    function solicitarUbicacion() {
        if (miUbicacion) return;
        if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition(function(pos) {
            miUbicacion = { lat: pos.coords.latitude, lng: pos.coords.longitude };
            pintarMarkerUbicacion();
            pintarMarkers(true);   // ajusta la cámara a la ubicación
            aviso('📍 Mostrando bonos cerca de ti');
        }, function() {
            // Usuario rechazó / falló: se muestran todos como antes
        }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 });
    }

    function pintarMarkerUbicacion() {
        if (!mapa || !miUbicacion) return;
        var el = document.createElement('div');
        el.className = 'bp-map-pin-ubicacion';
        markerUbicacion = new maptilersdk.Marker({ element: el })
            .setLngLat([miUbicacion.lng, miUbicacion.lat])
            .addTo(mapa);
    }

    // Devuelve los bonos según el radio seleccionado (solo si hay ubicación).
    // Sin ubicación: devuelve todos; el viewport actual del mapa recorta cuáles
    // se pintan (LZ 07/09 — al mover el mapa se cargan los bonos del área).
    function bonosCercanos() {
        // Si el usuario cerró el aviso de radio vacío → ver TODOS los bonos del mapa
        if (!miUbicacion || vacioCerrado) return bonosData;
        var conDist = bonosData.map(function(b) {
            var lat = parseFloat(b.lat), lng = parseFloat(b.lng);
            return { b: b, lat: lat, lng: lng, d: (!lat || !lng) ? Infinity : distanciaKm(miUbicacion.lat, miUbicacion.lng, lat, lng) };
        });
        conDist.sort(function(a, b2) { return a.d - b2.d; });
        var dentro = conDist.filter(function(x) { return x.d <= radioKm; });
        if (!dentro.length) {
            // No hay bonos en el radio → overlay elegante (sin forzar la cámara)
            $('#bp-map-vacio-km').text(radioKm + ' km');
            $('#bp-map-vacio').show();
            return [];
        }
        $('#bp-map-vacio').hide();
        return dentro.map(function(x) { return x.b; });
    }

    // True si el bono está dentro del viewport actual del mapa
    function bonoEnViewport(bono) {
        var lat = parseFloat(bono.lat), lng = parseFloat(bono.lng);
        if (!lat || !lng || !mapa || !mapa.getBounds) return false;
        var b = mapa.getBounds();
        if (!b || !b.contains) return true;
        try { return b.contains([lng, lat]); } catch (e) { /* noop */ }  // LngLatBounds [lng, lat]
        return true;
    }

    function abrirMapa() {
        var $modal = $('#bp-map-modal');
        if (!$modal.length) return;
        $modal.addClass('is-open');
        $('body').css('overflow', 'hidden');
        // LZ 08/09 — SOLO MapTiler SDK (ejemplo oficial):
        //   maptilersdk.config.apiKey = '...';
        //   new maptilersdk.Map({ container, style: maptilersdk.MapStyle.STREETS });
        cargarMapLibre().then(function() {
            if (mapa) return;
            var key = (window.bp_compartir && bp_compartir.maptiler_api_key) || '';
            if (!key) {
                $('#bp-map-bonos').html('<p style="padding:40px;text-align:center;color:#888;">Falta la API Key de MapTiler. Pégala en el panel: Apariencia → Ajustes BonosPremium → Integraciones.</p>');
                return;
            }
            maptilersdk.config.apiKey = key;
            mapa = new maptilersdk.Map({
                container: 'bp-map-bonos',
                style: maptilersdk.MapStyle.STREETS,
                center: [
                    parseFloat((window.bp_compartir && bp_compartir.map_center_lng) || '-13.6168'),
                    parseFloat((window.bp_compartir && bp_compartir.map_center_lat) || '28.9784')
                ],
                zoom: 8,
                attributionControl: true,
                // El SDK añade por defecto su propio NavigationControl y GeolocateControl
                // (top-right) — los desactivamos y dejamos SOLO el nuestro (Félix 09/09:
                // se veían los botones de zoom duplicados).
                navigationControl: false,
                geolocateControl: false
            });
            // LZ 08/09: el contenedor puede no tener aún su tamaño final (flex);
            // recalcular las dimensiones cuando esté visible.
            setTimeout(function() { try { if (mapa) mapa.resize(); } catch (e) {} }, 300);
            bpSilenciarIconosFaltantes(mapa);
            mapa.addControl(new maptilersdk.NavigationControl({ showCompass: false }), 'top-right');
            // Errores del SDK: NO tocar el contenedor (eso destruiría el mapa).
            // Con la clave correcta el style carga; los errores menores (tiles,
            // fuentes) se ignoran y el mapa sigue funcionando.
            mapa.on('error', function(e) {
                if (window.console && console.warn) console.warn('MapTiler:', e && e.error ? e.error.message : 'error');
            });
            // LZ 07/09: al mover el mapa se recargan los bonos del área visible
            mapa.on('moveend', function() { pintarMarkers(); });
            mapa.on('load', function() { cargarBonos(); solicitarUbicacion(); });
        }).catch(function(err) {
            $('#bp-map-bonos').html('<p style="padding:40px;text-align:center;color:#888;">No se pudo cargar el mapa de MapTiler' + (err && err.message ? ' (' + err.message + ')' : '') + '.</p>');
        });
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
                pintarMarkers(true);   // primera carga: ajusta la cámara a los bonos
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

    // Pintar los markers. `ajustar` = true solo en la carga inicial / geolocalización
    // (para fitBounds/centrar); false al mover el mapa (el usuario controla la cámara).
    function pintarMarkers(ajustar) {
        if (!mapa) return;
        var lista = bonosCercanos().filter(bonoEnViewport);
        pintarMarkersMapLibre(lista, ajustar);
    }

    // Texto del precio para el marker: precio plano, "Desde X" si es variable, fallback '€'
    function precioMarker(b) {
        if (b.precio_txt) return b.precio_txt;
        if (b.precio) return '€';
        return '•';
    }

    // Marker MapTiler: burbuja blanca con el precio en negro.
    // Usa un elemento HTML → mismo CSS .bp-map-pin-ico.
    function pintarMarkersMapLibre(lista, ajustar) {
        // Limpiar markers previos; si no hay bonos en el área NO se toca el mapa
        // (el overlay "Sin bonos" se gestiona en bonosCercanos). FIX 07/09.
        markers.forEach(function(m) { try { m.remove(); } catch (e) {} });
        markers = [];
        if (!lista || !lista.length) return;
        var bounds = [];
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
            bounds.push([lng, lat]);
        });
        if (ajustar && miUbicacion) {
            mapa.setCenter([miUbicacion.lng, miUbicacion.lat]);
            mapa.setZoom(12);
        } else if (ajustar && bounds.length) {
            try { mapa.fitBounds(bounds, { padding: [40, 40] }); } catch (e) { /* noop */ }
        }
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
    // Slider de radio de búsqueda (km)
    $('#bp-map-rango-input').on('input', function() {
        radioKm = parseInt($(this).val(), 10);
        vacioCerrado = false;   // el aviso puede volver a aparecer si el radio sigue vacío
        $('#bp-map-rango-val').text(radioKm + ' km');
        if (mapa) { pintarMarkers(); }
    });
    // Botón "Ampliar radio" del overlay de sin bonos
    $('#bp-map-vacio-btn').on('click', function() {
        radioKm = 100;
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
