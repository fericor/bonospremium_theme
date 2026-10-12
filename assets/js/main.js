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

    // Product image slider dots (transform-based, sin overflow scroll)
    $('.bp-slider').each(function() {
        var $slider = $(this);
        var $track = $slider.find('.bp-slider-track');
        var $slides = $track.find('.bp-slide');
        var $dots = $slider.find('.bp-slider-dots');
        var slidesCount = $slides.length;
        var currentIndex = 0;

        if (slidesCount > 1) {
            // Create dots
            for (var i = 0; i < slidesCount; i++) {
                $dots.append('<span data-index="' + i + '"></span>');
            }
            $dots.find('span:first').addClass('active');

            // Go to slide function (transform-based)
            function goToSlide(index) {
                if (index < 0) index = 0;
                if (index >= slidesCount) index = slidesCount - 1;
                currentIndex = index;
                // translateX(-index/slidesCount * 100%) shifts by one slide
                $track.css('transform', 'translateX(-' + ((index / slidesCount) * 100) + '%)');
                $dots.find('span').removeClass('active').eq(index).addClass('active');
            }

            // Click dot to go to slide
            $dots.on('click', 'span', function() {
                goToSlide(parseInt($(this).data('index')));
            });

            // Mouse drag to scroll (transform-based)
            var isDown = false, startX = 0, dragStartIndex = 0;
            $slider.on('mousedown', function(e) {
                isDown = true;
                $slider.addClass('bp-dragging');
                startX = e.pageX - $slider.offset().left;
                dragStartIndex = currentIndex;
                e.preventDefault();
            });
            $slider.on('mousemove', function(e) {
                if (!isDown) return;
                e.preventDefault();
                var x = e.pageX - $slider.offset().left;
                var walk = (x - startX) / $slider.width();
                var idx = Math.round(dragStartIndex - walk * 3);
                if (idx < 0) idx = 0;
                if (idx >= slidesCount) idx = slidesCount - 1;
                // Show intermediate positions during drag
                $track.css('transition', 'none');
                var dragPos = ((dragStartIndex - (walk * 3)) / slidesCount) * 100;
                $track.css('transform', 'translateX(-' + Math.min(Math.max(dragPos, 0), ((slidesCount-1) / slidesCount) * 100) + '%)');
            });
            $(document).on('mouseup', function() {
                if (!isDown) return;
                isDown = false;
                $slider.removeClass('bp-dragging');
                $track.css('transition', 'transform 0.3s ease');
                // Snap to nearest slide
                var idx = Math.round(currentIndex);
                goToSlide(idx);
            });
            $slider.on('mouseleave', function() {
                if (!isDown) return;
                isDown = false;
                $slider.removeClass('bp-dragging');
                $track.css('transition', 'transform 0.3s ease');
                goToSlide(currentIndex);
            });

            // Touch support for mobile
            var touchStartX = 0, touchStartIndex = 0;
            $slider.on('touchstart', function(e) {
                touchStartX = e.originalEvent.touches[0].pageX - $slider.offset().left;
                touchStartIndex = currentIndex;
                $track.css('transition', 'none');
            });
            $slider.on('touchmove', function(e) {
                var x = e.originalEvent.touches[0].pageX - $slider.offset().left;
                var walk = (x - touchStartX) / $slider.width();
                var dragPos = ((touchStartIndex - (walk * 3)) / slidesCount) * 100;
                $track.css('transform', 'translateX(-' + Math.min(Math.max(dragPos, 0), ((slidesCount-1) / slidesCount) * 100) + '%)');
            });
            $slider.on('touchend', function(e) {
                $track.css('transition', 'transform 0.3s ease');
                var x = e.originalEvent.changedTouches[0].pageX - $slider.offset().left;
                var walk = (x - touchStartX) / $slider.width();
                var idx = Math.round(touchStartIndex - walk * 3);
                if (idx < 0) idx = 0;
                if (idx >= slidesCount) idx = slidesCount - 1;
                goToSlide(idx);
            });
        }
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
        var $wrap = $('.bp-load-more-wrap');
        var page = parseInt($wrap.data('page')) + 1;
        var max = parseInt($wrap.data('max'));
        var category = $wrap.data('category') || '';

        $('.bp-load-more-spinner').show();

        $.post(bp_lz_ajax.ajax_url, {
            action: 'bp_load_more',
            page: page,
            category: category
        }, function(data) {
            $('.bp-load-more-spinner').hide();
            if (data) {
            $('.bp-products-grid').append(data);
            $wrap.data('page', page);
                if (page >= max) {
                    $wrap.remove();
                } else {
                    loadingMore = false;
                    // Re-trigger in case still visible
                    $(window).trigger('scroll');
                }
            } else {
                $wrap.remove();
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

    // ===== CONDICIONES (single product) — 08/09: texto normal, sin toggle =====
});
