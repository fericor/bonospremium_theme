jQuery(document).ready(function($) {
    // ===== HEADER DROPDOWN (menú hamburguesa) =====
    var $menuToggle = $('.bp-menu-toggle');
    var $userMenu = $('.bp-user-nav-menu');

    function positionDropdown() {
        if ($(window).width() > 768) return;
        var headerHeight = $('.bp-header').outerHeight() || 56;
        $userMenu.css('top', headerHeight + 'px');
    }

    $(window).on('scroll resize', function() {
        if ($userMenu.hasClass('open')) {
            positionDropdown();
        }
    });

    $menuToggle.on('click', function(e) {
        e.stopPropagation();
        positionDropdown();
        $userMenu.toggleClass('open');
        $(this).toggleClass('active');
        if ($(this).hasClass('active')) {
            $(this).find('span').eq(0).css('transform', 'rotate(45deg) translate(4px, 4px)');
            $(this).find('span').eq(1).css('opacity', '0');
            $(this).find('span').eq(2).css('transform', 'rotate(-45deg) translate(4px, -4px)');
        } else {
            $(this).find('span').each(function() { $(this).css('transform', '').css('opacity', ''); });
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
            $('.bp-menu-toggle span').each(function() { $(this).css('transform', '').css('opacity', ''); });
        }
    });

    // Close menu when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.bp-header-left').length) {
            $('.bp-user-nav-menu').removeClass('open');
            $('.bp-menu-toggle').removeClass('active');
            $('.bp-menu-toggle span').each(function() { $(this).css('transform', '').css('opacity', ''); });
        }
    });

    // Wishlist button
    $('.bp-wishlist-btn').on('click', function(e) {
        e.preventDefault();
        var $icon = $(this).find('i');
        $icon.toggleClass('far fas');
        $icon.css('color', $icon.hasClass('fas') ? '#e74c3c' : '');
    });

    // Infinite scroll - Auto load on scroll
    var loadingMore = false;
    var $loadWrap = $('.bp-load-more-wrap');

    // Product image slider dots
    $('.bp-slider').each(function() {
        var $slider = $(this);
        var $track = $slider.find('.bp-slider-track');
        var $slides = $track.find('.bp-slide');
        var $dots = $slider.find('.bp-slider-dots');

        if ($slides.length > 1) {
            // Create dots
            for (var i = 0; i < $slides.length; i++) {
                $dots.append('<span data-index="' + i + '"></span>');
            }
            $dots.find('span:first').addClass('active');

            // Update active dot on scroll
            $track.on('scroll', function() {
                var index = Math.round($track.scrollLeft() / $track.outerWidth());
                $dots.find('span').removeClass('active').eq(index).addClass('active');
            });

            // Click dot to scroll
            $dots.on('click', 'span', function() {
                var idx = $(this).data('index');
                $track.animate({ scrollLeft: idx * $track.outerWidth() }, 300);
            });

            // Mouse drag to scroll
            var isDown = false, startX = 0, scrollStart = 0;
            $slider.on('mousedown', function(e) {
                isDown = true;
                $slider.addClass('bp-dragging');
                startX = e.pageX - $slider.offset().left;
                scrollStart = $track.scrollLeft();
                e.preventDefault();
            });
            $slider.on('mousemove', function(e) {
                if (!isDown) return;
                e.preventDefault();
                var x = e.pageX - $slider.offset().left;
                var walk = (x - startX) * 1.5;
                $track.scrollLeft(scrollStart - walk);
            });
            $(document).on('mouseup', function() {
                isDown = false;
                $slider.removeClass('bp-dragging');
            });
            $slider.on('mouseleave', function() {
                isDown = false;
                $slider.removeClass('bp-dragging');
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
            document.documentElement.style.setProperty('--bp-nav-sticky-top', headerHeight + 'px');
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

    // ===== LOGIN TOGGLE (colapsible) =====
    $('.showlogin').off('click').on('click', function(e) {
        e.preventDefault();
        $('.woocommerce-form-login').slideToggle(300).toggleClass('bp-login-open');
    });
    
    // ===== COUPON TOGGLE (colapsible) =====
    $('.showcoupon').off('click').on('click', function(e) {
        e.preventDefault();
        $('.checkout_coupon').slideToggle(300).toggleClass('bp-coupon-open');
    });
    
    // ===== AUTO-DISMISS NOTICES =====
    setTimeout(function() {
        $('.woocommerce-notices-wrapper .woocommerce-message, .woocommerce-notices-wrapper .woocommerce-info, .woocommerce-notices-wrapper .woocommerce-error').fadeOut(400, function() { $(this).remove(); });
    }, 3000);

    // ===== CONDICIONES TOGGLE (single product) =====
    $('.bp-condiciones-toggle').on('click', function() {
        var panel = $(this).closest('.bp-condiciones-panel');
        panel.find('.bp-condiciones-body').slideToggle(250);
        panel.toggleClass('is-open');
    });
});
