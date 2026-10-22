jQuery(document).ready(function($) {
    // ===== HEADER DROPDOWN (menú hamburguesa) =====
    var $menuToggle = $('.bp-menu-toggle');
    var $userMenu = $('.bp-user-nav-menu');

    // Determinar si estamos en inicio o en categorías/archivo de productos
    // (clases del body: home, page-id..., post-type-archive-product, tax-product_cat, archive)
    function bpIsNavPage() {
        var $body = document.body;
        // Inicio (home) o archivo de productos o categoría de producto
        return $body.classList.contains('home')
            || $body.classList.contains('post-type-archive-product')
            || $body.classList.contains('tax-product_cat')
            || $body.classList.contains('woocommerce-page') && $body.classList.contains('archive')
            || $body.classList.contains('blog');
    }

    function bpUpdateHeaderControls() {
        if (bpIsNavPage()) {
            // En inicio/categorías: hamburguesa (mostrar)
            $('body').removeClass('bp-is-back-page');
            $('body').addClass('bp-is-nav-page');
        } else {
            // En otras páginas: el menú hamburguesa SIEMPRE visible (Félix 10/08:
            // quitado el botón volver atrás del header — solo el menú)
            $('body').removeClass('bp-is-back-page');
            $('body').addClass('bp-is-nav-page');
        }
    }
    bpUpdateHeaderControls();

    $menuToggle.on('click', function(e) {
        e.stopPropagation();
        $userMenu.toggleClass('open');
        $(this).toggleClass('active');
        // X normal: alternar icono hamburguesa <-> X (Félix 11/08)
        var $icon = $(this).find('i');
        if ($(this).hasClass('active')) {
            $icon.removeClass('fa-bars').addClass('fa-times');
        } else {
            $icon.removeClass('fa-times').addClass('fa-bars');
        }
    });

    // Search overlay toggle
    $('.bp-search-toggle').on('click', function(e) {
        e.preventDefault();
        $('.bp-search-overlay').addClass('open');
        setTimeout(function() {
            $('.bp-search-overlay input[type="search"]').focus();
        }, 100);
    });
    
    $('.bp-search-close').on('click', function() {
        $('.bp-search-overlay').removeClass('open');
    });

    // Close on escape
    $(document).on('keyup', function(e) {
        if (e.key === 'Escape') {
            $('.bp-search-overlay').removeClass('open');
            $('.bp-user-nav-menu').removeClass('open');
            $('.bp-menu-toggle').removeClass('active');
            $('.bp-menu-toggle i').removeClass('fa-times').addClass('fa-bars');
        }
    });

    // Close menu when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.bp-header-left').length) {
            $('.bp-user-nav-menu').removeClass('open');
            $('.bp-menu-toggle').removeClass('active');
            $('.bp-menu-toggle i').removeClass('fa-times').addClass('fa-bars');
        }
    });

    // ===== WISHLIST (Favoritos) con localStorage + servidor =====
    var wishlistKey = 'bp_wishlist';
    
    // Cargar wishlist: localStorage (todos) + servidor (logueados)
    function bpLoadWishlist() {
        var ids = [];
        // Guests: desde localStorage
        try {
            var local = JSON.parse(localStorage.getItem(wishlistKey));
            if (Array.isArray(local)) ids = local;
        } catch(e) {}
        // Logueados: desde el server (sobrescribe)
        if (bp_lz_ajax.wishlist && bp_lz_ajax.wishlist.length) {
            ids = bp_lz_ajax.wishlist;
        }
        return ids;
    }
    
    // Guardar wishlist
    function bpSaveWishlist(ids) {
        localStorage.setItem(wishlistKey, JSON.stringify(ids));
    }
    
    // Marcar corazones en la página
    function bpMarkHearts(ids) {
        $('.bp-wishlist-btn').each(function() {
            var $icon = $(this).find('i');
            var pid = parseInt($(this).data('product-id'));
            if (ids.indexOf(pid) !== -1) {
                $icon.removeClass('far').addClass('fas').css('color', '#e74c3c');
            } else {
                $icon.removeClass('fas').addClass('far').css('color', '');
            }
        });
    }
    
    // Inicializar corazones al cargar
    bpMarkHearts(bpLoadWishlist());
    
    // Click en corazón
    $('.bp-wishlist-btn').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $icon = $btn.find('i');
        var productId = parseInt($btn.data('product-id'));
        var ids = bpLoadWishlist();
        var idx = ids.indexOf(productId);
        
        if (idx !== -1) {
            ids.splice(idx, 1);
        } else {
            ids.push(productId);
        }
        
        bpSaveWishlist(ids);
        bpMarkHearts(ids);
        
        // Sincronizar con servidor si está logueado
        if (bp_lz_ajax.user_id > 0) {
            $.post(bp_lz_ajax.ajax_url, {
                action: 'bp_toggle_wishlist',
                product_id: productId,
            });
        }
    });

    // Infinite scroll - Auto load on scroll
    var loadingMore = false;
    var $loadWrap = $('.bp-load-more-wrap');

    // Product image slider dots (transform-based, sin overflow scroll, bucle infinito)
    $('.bp-slider').each(function() {
        var $slider = $(this);
        var $track  = $slider.find('.bp-slider-track');
        var $slides = $track.find('.bp-slide');
        var $dots   = $slider.find('.bp-slider-dots');
        var slidesCount = $slides.length;
        var currentIndex = 0;

        if (slidesCount < 2) return;

        for (var i = 0; i < slidesCount; i++) {
            $dots.append('<span data-index="' + i + '"></span>');
        }
        $dots.find('span:first').addClass('active');

        // Indice circular: al pasar de la ultima vuelve a la primera y al reves
        var normalizar = function(i) { return ((i % slidesCount) + slidesCount) % slidesCount; };

        var goToSlide = function(index) {
            var n = normalizar(index);
            currentIndex = n;
            $track.css('transform', 'translateX(-' + ((n / slidesCount) * 100) + '%)');
            $dots.find('span').removeClass('active').eq(n).addClass('active');
        };

        $dots.on('click', 'span', function() {
            goToSlide(parseInt($(this).data('index'), 10) || 0);
        });

        // Arrastre con raton (al soltar da la vuelta si se pasa del final)
        var isDown = false, startX = 0, dragStartIndex = 0, dragIdx = 0;
        $slider.on('mousedown', function(e) {
            isDown = true;
            $slider.addClass('bp-dragging');
            startX = e.pageX - $slider.offset().left;
            dragStartIndex = currentIndex;
            dragIdx = currentIndex;
            e.preventDefault();
        });
        $slider.on('mousemove', function(e) {
            if (!isDown) return;
            e.preventDefault();
            var x = e.pageX - $slider.offset().left;
            var walk = (x - startX) / $slider.width();
            dragIdx = Math.round(dragStartIndex - walk * 3);
            $track.css('transition', 'none');
            var dragPos = ((dragStartIndex - (walk * 3)) / slidesCount) * 100;
            $track.css('transform', 'translateX(-' + Math.min(Math.max(dragPos, 0), ((slidesCount - 1) / slidesCount) * 100) + '%)');
        });
        $(document).on('mouseup', function() {
            if (!isDown) return;
            isDown = false;
            $slider.removeClass('bp-dragging');
            $track.css('transition', 'transform 0.3s ease');
            goToSlide(dragIdx);
        });
        $slider.on('mouseleave', function() {
            if (!isDown) return;
            isDown = false;
            $slider.removeClass('bp-dragging');
            $track.css('transition', 'transform 0.3s ease');
            goToSlide(dragIdx);
        });

        // Tactil
        var touchStartX = 0, touchStartIndex = 0, touchIdx = 0;
        $slider.on('touchstart', function(e) {
            touchStartX = e.originalEvent.touches[0].pageX - $slider.offset().left;
            touchStartIndex = currentIndex;
            touchIdx = currentIndex;
            $track.css('transition', 'none');
        });
        $slider.on('touchmove', function(e) {
            var x = e.originalEvent.touches[0].pageX - $slider.offset().left;
            var walk = (x - touchStartX) / $slider.width();
            touchIdx = Math.round(touchStartIndex - walk * 3);
            var dragPos = ((touchStartIndex - (walk * 3)) / slidesCount) * 100;
            $track.css('transform', 'translateX(-' + Math.min(Math.max(dragPos, 0), ((slidesCount - 1) / slidesCount) * 100) + '%)');
        });
        $slider.on('touchend', function() {
            $track.css('transition', 'transform 0.3s ease');
            goToSlide(touchIdx);
        });
    });

    // Sticky nav on scroll
    var $nav = $('.bp-nav');
    $(window).on('scroll', function() {
        var scrollTop = $(window).scrollTop();
        var headerHeight = $('.bp-header').outerHeight() || 0;
        
        // Activar cuando se ha scrolleado pasado el header
        if (scrollTop > headerHeight) {
            $nav.addClass('bp-nav-sticky');
        } else {
            $nav.removeClass('bp-nav-sticky');
        }

        // Infinite scroll
        if ($loadWrap.length && !loadingMore) {
            var wrapTop = $loadWrap.offset().top;
            var scrollBottom = scrollTop + $(window).height();
            if (scrollBottom >= wrapTop - 200) {
                loadingMore = true;
                loadMoreProducts();
            }
        }
    });

    function loadMoreProducts() {
        // FIX 22/10 (Felix): carga por DESPLAZAMIENTO (lo que ya hay en pantalla) con el
        // mismo tamano de pagina que el archivo. Antes pedia la pagina 2 con 10 elementos
        // mientras el archivo mostraba 30 → los bonos salian repetidos.
        var $wrap = $('.bp-load-more-wrap');
        if (!$wrap.length) { loadingMore = false; return; }
        var $grid = $('.bp-products-grid');
        var perPage = parseInt($wrap.data('per-page'), 10) || 30;
        var total = parseInt($wrap.data('total'), 10) || 0;
        var offset = $grid.children('li.product').length;
        var category = $wrap.data('category') || '';

        if (total && offset >= total) { $wrap.remove(); loadingMore = false; return; }

        $('.bp-load-more-spinner').show();

        $.post(bp_lz_ajax.ajax_url, {
            action: 'bp_load_more',
            offset: offset,
            per_page: perPage,
            category: category
        }, function(data) {
            $('.bp-load-more-spinner').hide();
            if (!data) { $wrap.remove(); return; }

            var anadidos = 0;
            $('<div>').append(data).find('li.product').each(function() {
                var m = ($(this).attr('class') || '').match(/post-(\d+)/);
                if (m && $grid.children('li.product.post-' + m[1]).length) return; // ya estaba
                $grid.append(this);
                anadidos++;
            });

            if (anadidos === 0 || anadidos < perPage || (total && $grid.children('li.product').length >= total)) {
                $wrap.remove();
                loadingMore = false;
            } else {
                loadingMore = false;
                $(window).trigger('scroll');
            }
        }).fail(function() {
            $('.bp-load-more-spinner').hide();
            loadingMore = false;
        });
    }

    // ===== CUPÓN COLAPSIBLE (checkout) =====
    $(document.body).on('click', '.bp-coupon-toggle', function() {
        $(this).closest('.bp-checkout-coupon-wrap').find('.bp-coupon-body').slideToggle(250);
        $(this).closest('.bp-checkout-coupon-wrap').toggleClass('is-open');
    });
    $(document.body).on('click', '.bp-coupon-apply', function() {
        var code = $('#coupon_code').val();
        if (code) {
            var url = window.location.origin + '/?wc-ajax=apply_coupon';
            var data = new URLSearchParams();
            data.append('coupon_code', code);
            data.append('security', (bp_lz_ajax && bp_lz_ajax.coupon_nonce) || '');
            fetch(url, { method: 'POST', body: data }).then(function() {
                $(document.body).trigger('update_checkout');
            });
        }
    });

    // ===== PRECIO DE LA VARIACIÓN EN LA FICHA (inmediato) — 16/10 =====
    // WooCommerce 11.x retrasa a propósito 300 ms el evento `show_variation`
    // (add-to-cart-variation.js: "Add a delay ... screen readers"), así que el precio
    // se quedaba ~300 ms con el valor anterior y luego cambiaba (doble pintado).
    // Aquí se pinta en el MISMO tick del `change`, leyendo la variación del JSON
    // embebido (data-product_variations) y usando su `price_html`, que es EXACTAMENTE
    // el HTML formateado por WooCommerce (38€, no 38,00€).
    function bpPintarPrecioVariacion(variation) {
        var $dest = $('#bp-single-price');
        if (!$dest.length) return false;
        var priceHtml = (variation && variation.price_html) ? variation.price_html : '';
        if (!priceHtml) return false;
        var $wrap = $('<div>').html(priceHtml);
        var $del = $wrap.find('del .woocommerce-Price-amount').first();
        var $ins = $wrap.find('ins .woocommerce-Price-amount').first();
        if (!$ins.length) $ins = $wrap.find('.woocommerce-Price-amount').not($del).first();
        if (!$ins.length) return false;
        var html = '';
        if ($del.length) html += '<span class="bp-price-original">' + $del.prop('outerHTML') + '</span> ';
        html += '<span class="bp-price-sale">' + $ins.prop('outerHTML') + '</span>';
        if ($dest.html() !== html) $dest.html(html); // sin escritura si ya es el mismo HTML
        return true;
    }
    window.bpPintarPrecioVariacion = bpPintarPrecioVariacion; // el plugin la reutiliza

    // Variación que corresponde a la selección actual (o null si está incompleta)
    function bpVariacionSeleccionada($form) {
        var raw = $form.attr('data-product_variations');
        if (!raw) return null;
        var variaciones;
        try { variaciones = JSON.parse(raw); } catch (e) { return null; }
        if (!variaciones || !variaciones.length) return null;
        var buscados = {}, completo = true;
        $form.find('.bp-variation-select').each(function () {
            var val = $(this).val();
            var nombre = $(this).attr('name') || $(this).data('attribute_name');
            if (!val) { completo = false; return; }
            buscados[nombre] = val;
        });
        if (!completo) return null;
        for (var i = 0; i < variaciones.length; i++) {
            var attrs = variaciones[i].attributes || {}, ok = true;
            for (var k in buscados) {
                if (attrs[k] !== buscados[k]) { ok = false; break; }
            }
            if (ok) return variaciones[i];
        }
        return null;
    }

    var bpPrecioInicial = $('#bp-single-price').html();

    $(document).on('change', '.bp-variation-select', function () {
        var $form = $(this).closest('.variations_form');
        if (!$form.length) return;
        var variacion = bpVariacionSeleccionada($form);
        if (variacion) bpPintarPrecioVariacion(variacion);
    });

    // Selección incompleta → volver al precio con el que se cargó la ficha
    $(document).on('hide_variation reset_data', '.variations_form', function () {
        var $dest = $('#bp-single-price');
        if (bpPrecioInicial && $dest.length && $dest.html() !== bpPrecioInicial) $dest.html(bpPrecioInicial);
    });

    // ===== CONDICIONES (single product) — 08/09: texto normal, sin toggle =====
});
