const initPreloader = () => {
    const preloader = document.getElementById('preloader');
    const isSiteLockActive = () => document.body.classList.contains('site-lock-active');

    if (!preloader) {
        document.documentElement.classList.remove('preloader-lock');
        if (!isSiteLockActive()) {
            document.body.classList.remove('ovh');
            document.body.style.removeProperty('overflow');
        }
        return;
    }

    if (preloader.dataset.initialized === 'true') {
        return;
    }

    preloader.dataset.initialized = 'true';

    const counters = preloader.querySelectorAll('.preloader-num');
    const isMobile = window.matchMedia('(max-width: 600px)').matches;

    const sceneDuration = isMobile ? 3400 : 2400;
    const exitDuration = isMobile ? 900 : 650;
    const totalFallbackDuration = sceneDuration + exitDuration + 1000;
    const startTime = performance.now();

    let isClosing = false;
    let isCompleted = false;
    let fallbackTimer = null;

    document.documentElement.classList.add('preloader-lock');
    document.body.classList.add('ovh');
    document.body.style.overflow = 'hidden';

    const easeOutCubic = (progress) => {
        return 1 - Math.pow(1 - progress, 3);
    };

    const completePreloader = () => {
        if (isCompleted) {
            return;
        }

        isCompleted = true;

        if (fallbackTimer) {
            window.clearTimeout(fallbackTimer);
        }

        preloader.removeEventListener('animationend', onPreloaderAnimationEnd);

        preloader.setAttribute('aria-hidden', 'true');
        preloader.classList.add('preloader-completed');

        document.documentElement.classList.remove('preloader-lock');
        if (!isSiteLockActive()) {
            document.body.classList.remove('ovh');
            document.body.style.removeProperty('overflow');
        }
    };

    const onPreloaderAnimationEnd = (event) => {
        if (event.target !== preloader) {
            return;
        }

        if (event.animationName !== 'preloaderFadeOut') {
            return;
        }

        completePreloader();
    };

    const closePreloader = () => {
        if (isClosing) {
            return;
        }

        isClosing = true;

        preloader.addEventListener('animationend', onPreloaderAnimationEnd);

        window.requestAnimationFrame(() => {
            preloader.classList.add('preload-hidden');
        });

        fallbackTimer = window.setTimeout(completePreloader, exitDuration + 500);
    };

    const tick = (now) => {
        const progress = Math.min((now - startTime) / sceneDuration, 1);
        const eased = easeOutCubic(progress);

        counters.forEach((el) => {
            const target = parseInt(el.dataset.target, 10) || 0;
            el.textContent = Math.round(eased * target);
        });

        if (progress < 1) {
            window.requestAnimationFrame(tick);
            return;
        }

        closePreloader();
    };

    window.requestAnimationFrame(tick);
    window.setTimeout(completePreloader, totalFallbackDuration);
};

const initProductSliders = () => {
    if (typeof Swiper !== 'undefined') {
        document.querySelectorAll('.js-products-slider').forEach((sliderElement) => {
            const sliderId = sliderElement.dataset.sliderId;

            if (!sliderId) {
                return;
            }

            // Avoid double init on repeated lifecycle hooks.
            if (sliderElement.swiper) {
                return;
            }

            const prevButton = document.querySelector(`[data-slider-prev="${sliderId}"]`);
            const nextButton = document.querySelector(`[data-slider-next="${sliderId}"]`);
            const pagination = document.querySelector(`[data-slider-pagination="${sliderId}"]`);

            new Swiper(sliderElement, {
                slidesPerView: 1,
                spaceBetween: 14,
                speed: 700,
                watchOverflow: true,
                navigation: {
                    nextEl: nextButton,
                    prevEl: prevButton
                },
                pagination: {
                    el: pagination,
                    type: 'fraction',
                    formatFractionCurrent: (number) => `0${number}`,
                    formatFractionTotal: (number) => `0${number}`
                },
                breakpoints: {
                    576: {
                        slidesPerView: 2,
                        spaceBetween: 14
                    },
                    992: {
                        slidesPerView: 3,
                        spaceBetween: 14
                    },
                    1200: {
                        slidesPerView: 3,
                        spaceBetween: 14
                    }
                }
            });
        });
    }
};

const initSingleProductGallerySliders = () => {
    if (typeof Swiper === 'undefined') {
        return;
    }

    document.querySelectorAll('.js-product-gallery-slider').forEach((sliderElement) => {
        const sliderId = sliderElement.dataset.sliderId;

        if (!sliderId || sliderElement.swiper) {
            return;
        }

        const prevButton = document.querySelector(`[data-slider-prev="${sliderId}"]`);
        const nextButton = document.querySelector(`[data-slider-next="${sliderId}"]`);
        const pagination = document.querySelector(`[data-slider-pagination="${sliderId}"]`);

        new Swiper(sliderElement, {
            slidesPerView: 1,
            spaceBetween: 0,
            speed: 700,
            watchOverflow: true,
            navigation: {
                nextEl: nextButton,
                prevEl: prevButton
            },
            pagination: {
                el: pagination,
                type: 'fraction',
                formatFractionCurrent: (number) => `0${number}`,
                formatFractionTotal: (number) => `0${number}`
            }
        });
    });
};

const syncSingleProductGalleryWithVariation = () => {
    if (typeof jQuery === 'undefined') {
        return;
    }

    jQuery('.variations_form').each(function () {
        const $form = jQuery(this);

        if ($form.data('rastiVariationGalleryBound')) {
            return;
        }

        $form.data('rastiVariationGalleryBound', true);

        const galleryElement = document.querySelector('.woocommerce-product-gallery');
        const sliderElement = galleryElement?.querySelector('.js-product-gallery-slider');
        const wrapperElement = galleryElement?.querySelector('.woocommerce-product-gallery__wrapper');
        const defaultTemplate = galleryElement?.querySelector('.js-default-product-gallery-template');
        const navigationElement = sliderElement?.dataset?.sliderId
            ? document.querySelector(`[data-slider-nav="${sliderElement.dataset.sliderId}"]`)
            : null;

        if (!galleryElement || !sliderElement || !wrapperElement || !defaultTemplate) {
            return;
        }

        const defaultGalleryHtml = defaultTemplate.innerHTML.trim();
        const defaultSlideCount = Number.parseInt(galleryElement.dataset.defaultSlideCount || '0', 10) || 0;

        const updateGalleryState = (slideCount) => {
            galleryElement.dataset.slideCount = String(slideCount);

            if (navigationElement) {
                navigationElement.hidden = slideCount <= 1;
            }

            if (sliderElement.swiper) {
                sliderElement.swiper.update();
                sliderElement.swiper.slideTo(0, 0);
            }
        };

        let renderGeneration = 0;

        const renderGallery = (galleryHtml, slideCount) => {
            if (!galleryHtml) {
                return;
            }

            const generation = ++renderGeneration;

            const doSwap = () => {
                if (generation !== renderGeneration) {
                    return;
                }
                wrapperElement.innerHTML = galleryHtml;
                window.requestAnimationFrame(() => {
                    updateGalleryState(slideCount);
                });
            };

            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = galleryHtml;
            const imgSrcs = Array.from(tempDiv.querySelectorAll('img[src]')).map((img) => img.src);

            if (imgSrcs.length === 0) {
                doSwap();
                return;
            }

            let remaining = imgSrcs.length;
            imgSrcs.forEach((src) => {
                const img = new Image();
                img.onload = img.onerror = () => {
                    remaining--;
                    if (remaining <= 0) {
                        doSwap();
                    }
                };
                img.src = src;
            });
        };

        const restoreDefaultGallery = () => {
            renderGallery(defaultGalleryHtml, defaultSlideCount);
        };

        const renderVariationGallery = (variation) => {
            const variationGalleryHtml = typeof variation?.rasti_gallery_html === 'string'
                ? variation.rasti_gallery_html.trim()
                : '';
            const variationSlideCount = Array.isArray(variation?.rasti_gallery_image_ids)
                ? variation.rasti_gallery_image_ids.length
                : 0;

            if (!variationGalleryHtml || variationSlideCount < 1) {
                restoreDefaultGallery();
                return;
            }

            renderGallery(variationGalleryHtml, variationSlideCount);
        };

        let pendingRender = null;
        const scheduleRender = (callback) => {
            if (pendingRender !== null) {
                window.cancelAnimationFrame(pendingRender);
                pendingRender = null;
            }

            pendingRender = window.requestAnimationFrame(() => {
                pendingRender = null;
                callback();
            });
        };

        $form.on('found_variation', (event, variation) => {
            scheduleRender(() => {
                renderVariationGallery(variation);
            });
        });

        $form.on('reset_image reset_data hide_variation', () => {
            scheduleRender(restoreDefaultGallery);
        });
    });
};

const getCartIdsFromDom = () => {
    try {
        const el = document.getElementById('rasti-cart-ids');
        if (el) {
            return JSON.parse(el.textContent);
        }
    } catch (e) { /* ignore */ }
    return { productIds: [], variationIds: [] };
};

const syncCartIdsToTheme = () => {
    const data = getCartIdsFromDom();
    if (window.rastiTheme) {
        window.rastiTheme.cartProductIds   = data.productIds   || [];
        window.rastiTheme.cartVariationIds = data.variationIds || [];
    }
};

const inCartText = 'Вже в кошику';

const setButtonInCart = ($btn, inCart) => {
    if (inCart) {
        $btn.data('originalText', $btn.data('originalText') || $btn.text());
        $btn.text(inCartText).addClass('is-in-cart added').prop('disabled', true);
    } else {
        const original = $btn.data('originalText') || $btn.data('addToCartText');
        if (original) {
            $btn.text(original);
        }
        $btn.removeClass('is-in-cart added').prop('disabled', false);
    }
};

const refreshPageInCartButtons = () => {
    if (typeof jQuery === 'undefined') {
        return;
    }
    const productIds   = (window.rastiTheme?.cartProductIds   || []).map(Number);
    const variationIds = (window.rastiTheme?.cartVariationIds || []).map(Number);

    // Simple product button
    jQuery('.cart .single_add_to_cart_button[name="add-to-cart"]').each(function () {
        const $btn      = jQuery(this);
        const productId = Number($btn.val() || 0);
        if (productId) {
            setButtonInCart($btn, productIds.includes(productId));
        }
    });

    // Variation — retrigger current selected variation if any
    jQuery('.variations_form').each(function () {
        const $form      = jQuery(this);
        const variationId = Number($form.find('input.variation_id').val() || 0);
        if (variationId > 0) {
            const $btn = $form.find('.single_add_to_cart_button');
            setButtonInCart($btn, variationIds.includes(variationId));
        }
    });
};

const fetchCartIds = () => {
    const ajaxUrl   = window.rastiTheme?.ajaxUrl;
    const ajaxNonce = window.rastiTheme?.ajaxNonce;
    if (!ajaxUrl || !ajaxNonce || typeof jQuery === 'undefined') {
        return;
    }
    jQuery.post(ajaxUrl, { action: 'project_theme_get_cart_ids', nonce: ajaxNonce })
        .done((response) => {
            if (response?.success && response.data) {
                if (window.rastiTheme) {
                    window.rastiTheme.cartProductIds   = response.data.productIds   || [];
                    window.rastiTheme.cartVariationIds = response.data.variationIds || [];
                }
                refreshPageInCartButtons();
            }
        });
};

const initCartStateSync = () => {
    if (typeof jQuery === 'undefined') {
        return;
    }

    syncCartIdsToTheme();

    const clearAllSwupCache = () => {
        if (window.rastiSwup?.cache && typeof window.rastiSwup.cache.clear === 'function') {
            window.rastiSwup.cache.clear();
        }
    };

    jQuery(document.body).on('wc_fragments_refreshed wc_fragments_loaded', () => {
        syncCartIdsToTheme();
        clearAllSwupCache();
        refreshPageInCartButtons();
    });

    jQuery(document.body).on('removed_from_cart updated_cart_totals', () => {
        clearAllSwupCache();
    });
};

const initSingleProductInCartState = () => {
    if (typeof jQuery === 'undefined') {
        return;
    }

    jQuery('.variations_form').each(function () {
        const $form = jQuery(this);

        if ($form.data('rastiInCartBound')) {
            return;
        }
        $form.data('rastiInCartBound', true);

        const $btn = $form.find('.single_add_to_cart_button');

        $form.on('found_variation', (event, variation) => {
            const variationId  = Number(variation?.variation_id || 0);
            const variationIds = (window.rastiTheme?.cartVariationIds || []).map(Number);
            setButtonInCart($btn, variationId > 0 && variationIds.includes(variationId));
        });

        $form.on('reset_image reset_data hide_variation', () => {
            setButtonInCart($btn, false);
        });
    });

    jQuery(document.body).on('added_to_cart.inCartState', function (event, fragments, cartHash, $button) {
        if (!$button || !$button.length) {
            return;
        }
        const $form = $button.closest('.variations_form');
        if ($form.length) {
            setButtonInCart($button, true);
        }
    });
};

const initWooCommerceSingleProduct = () => {
    if (typeof jQuery === 'undefined') {
        return;
    }

    const $ = jQuery;

    $('.woocommerce-product-gallery').each(function () {
        const $gallery = $(this);

        if (typeof $gallery.wc_product_gallery === 'function' && !$gallery.data('wc_product_gallery')) {
            $gallery.wc_product_gallery();
        }

        $gallery.css('opacity', '1');
    });

    $('.variations_form').each(function () {
        const $form = $(this);

        if (typeof $form.wc_variation_form === 'function' && !$form.data('wc_variation_form')) {
            $form.wc_variation_form();
            $form.trigger('check_variations');
        }
    });

    syncSingleProductGalleryWithVariation();
};

const initVariationSelectInfo = () => {
    if (typeof jQuery === 'undefined') {
        return;
    }

    jQuery('.variations_form').each(function () {
        const $form = jQuery(this);

        if ($form.data('rastiVariationInfoBound')) {
            return;
        }

        $form.data('rastiVariationInfoBound', true);

        const $variationInfo = $form.find('.variation_select_info');
        const $variationStockInfo = $form.find('.variation_select_info_stock');
        const $variationButtonWrap = $form.find('.woocommerce-variation-add-to-cart');
        const $variationButton = $variationButtonWrap.find('.single_add_to_cart_button');
        const $variationQuantity = $variationButtonWrap.find('.quantity');

        if (!$variationInfo.length && !$variationStockInfo.length) {
            return;
        }

        const animateVisibility = ($element, isVisible) => {
            if (!$element.length) {
                return;
            }

            $element.stop(true, true);
            $element.css({
                display: '',
                transition: 'opacity 0.3s ease, visibility 0.3s ease',
                visibility: isVisible ? 'visible' : 'hidden',
                opacity: isVisible ? '1' : '0',
                pointerEvents: isVisible ? 'auto' : 'none'
            });
        };

        const areAllAttributesSelected = () => {
            const selects = $form.find('.variations select');

            if (!selects.length) {
                return false;
            }

            let allSelected = true;

            selects.each(function () {
                if (!jQuery(this).val()) {
                    allSelected = false;
                    return false;
                }
            });

            return allSelected;
        };

        const showState = (state) => {
            const showButton = state === null;

            animateVisibility($variationInfo, state === 'select');
            animateVisibility($variationStockInfo, state === 'stock');
            animateVisibility($variationButton, showButton);
            animateVisibility($variationQuantity, showButton);
        };

        showState('select');

        $form.on('found_variation', (event, variation) => {
            const isInStock = Boolean(variation?.is_in_stock);
            showState(isInStock ? null : 'stock');
        });

        $form.on('hide_variation', () => {
            showState(areAllAttributesSelected() ? 'stock' : 'select');
        });

        $form.on('reset_data', () => {
            showState('select');
        });

        $form.on('woocommerce_variation_select_change', () => {
            showState(areAllAttributesSelected() ? null : 'select');
        });
    });
};

const initVariationSwatchesForSwup = () => {
    if (typeof jQuery === 'undefined') {
        return;
    }

    const $ = jQuery;
    const runInit = () => {
        const $forms = $('.variations_form');

        if (!$forms.length) {
            return;
        }

        $forms.each(function () {
            const $form = $(this);

            $form.removeClass('th-var-active');
            $form.off('.thwvsf_variation_form');
            $form.off('.thwvs_variation_form');

            if ($form.data('wc_variation_form')) {
                $form.off('.wc-variation-form');
                $form.removeData('wc_variation_form');
            }
        });

        if (typeof init_thwvsf === 'function') {
            init_thwvsf();
        }

        $forms.each(function () {
            const $form = $(this);

            if (typeof $form.wc_variation_form === 'function') {
                $form.wc_variation_form();
                $form.trigger('check_variations');
            }
        });
    };

    runInit();
    window.requestAnimationFrame(runInit);
    window.setTimeout(runInit, 120);
};

const initReadMoreExcerpt = () => {
    document.querySelectorAll('[data-read-more-excerpt]').forEach((element) => {
        if (element.dataset.readMoreBound === 'true') {
            return;
        }

        element.dataset.readMoreBound = 'true';

        const expandExcerpt = () => {
            if (element.dataset.readMoreExpanded === 'true') {
                return;
            }

            const preview = element.querySelector('.js-read-more-preview');
            const full = element.querySelector('.js-read-more-full');
            const content = element.querySelector('p');

            if (!preview || !full || !content) {
                return;
            }

            element.dataset.readMoreExpanded = 'true';
            element.removeAttribute('data-read-more-excerpt');
            element.removeAttribute('role');
            element.removeAttribute('tabindex');

            const startHeight = element.offsetHeight;

            full.hidden = false;
            preview.hidden = true;

            const endHeight = element.scrollHeight;

            preview.hidden = false;
            full.hidden = true;

            element.style.overflow = 'hidden';
            element.style.height = `${startHeight}px`;
            element.style.transition = 'height 320ms ease, opacity 320ms ease';
            content.style.transition = 'opacity 220ms ease';
            content.style.opacity = '0.72';

            window.requestAnimationFrame(() => {
                preview.hidden = true;
                full.hidden = false;
                element.classList.remove('is-collapsed');
                element.setAttribute('aria-expanded', 'true');
                element.style.height = `${endHeight}px`;
                content.style.opacity = '1';
            });

            window.setTimeout(() => {
                element.style.removeProperty('height');
                element.style.removeProperty('overflow');
                element.style.removeProperty('transition');
                content.style.removeProperty('transition');
                content.style.removeProperty('opacity');
            }, 340);
        };

        element.addEventListener('click', expandExcerpt);
        element.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                expandExcerpt();
            }
        });
    });
};

const initFancybox = () => {
    if (typeof Fancybox === 'undefined') {
        return;
    }

    Fancybox.bind('[data-fancybox]', {
        groupAll: false
    });
};

const syncBodyClasses = (visit = null) => {
    const nextBody = visit?.to?.document?.body;

    if (!nextBody) {
        return;
    }

    document.body.className = nextBody.className;
};

const initCheckoutCountrySelects = () => {
    if (typeof jQuery === 'undefined') {
        return;
    }

    const $ = jQuery;
    const $checkoutForm = $('form.woocommerce-checkout');

    if (!$checkoutForm.length) {
        return;
    }

    const enhanceSelect = ($select) => {
        if (!$select.length || typeof $select.selectWoo !== 'function') {
            return;
        }

        if ($select.hasClass('select2-hidden-accessible')) {
            try {
                $select.selectWoo('destroy');
            } catch (error) {
                // Ignore selectWoo destroy errors on partially initialized fields.
            }
        }

        $select.selectWoo();
    };

    enhanceSelect($('#billing_country'));
    enhanceSelect($('#shipping_country'));

    $checkoutForm.find('select.country_select, select.state_select').each(function () {
        enhanceSelect($(this));
    });
};

const initCartPageFreshContent = () => {
    const cartUrl = window.rastiTheme?.cartUrl;

    if (!cartUrl || normalizePathname(window.location.pathname) !== normalizePathname(new URL(cartUrl, window.location.origin).pathname)) {
        return;
    }

    const cartBox = document.querySelector('.template-page.cart .box-text');

    if (!cartBox || cartBox.dataset.cartRefreshPending === 'true') {
        return;
    }

    cartBox.dataset.cartRefreshPending = 'true';

    const refreshUrl = new URL(cartUrl, window.location.origin);
    refreshUrl.searchParams.set('_cart_refresh', `${Date.now()}`);

    window.fetch(refreshUrl.toString(), {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then((response) => response.text())
        .then((html) => {
            const parser = document.createElement('div');
            parser.innerHTML = html;

            const nextCartBox = parser.querySelector('.template-page.cart .box-text');
            const nextCartCount = parser.querySelector('li.cart-count');

            if (nextCartBox) {
                cartBox.innerHTML = nextCartBox.innerHTML;
            }

            if (nextCartCount) {
                const currentCartCount = document.querySelector('li.cart-count');

                if (currentCartCount) {
                    currentCartCount.replaceWith(nextCartCount);
                }
            }
        })
        .finally(() => {
            delete cartBox.dataset.cartRefreshPending;
        });
};

const bindCheckoutCountrySelects = () => {
    if (typeof jQuery === 'undefined') {
        return;
    }

    const $ = jQuery;

    if ($('body').data('rastiCheckoutSelectsBound')) {
        return;
    }

    $('body').data('rastiCheckoutSelectsBound', true);

    $(document.body).on('updated_checkout country_to_state_changed updated_wc_div', () => {
        window.requestAnimationFrame(initCheckoutCountrySelects);
        window.setTimeout(initCheckoutCountrySelects, 120);
    });
};

const initWcUkrShipping = (visit) => {
    if (!document.body.classList.contains('woocommerce-checkout')) {
        return;
    }

    if (typeof jQuery === 'undefined') {
        return;
    }

    const fetchedDoc = visit?.to?.document;
    if (!fetchedDoc) {
        return;
    }

    // Inject WooCommerce data vars + plugin state from fetched document
    const paramPatterns = ['wc_ukr_shipping_globals', 'WCUS_APP_STATE', 'wc_checkout_params', 'woocommerce_params'];
    fetchedDoc.querySelectorAll('script:not([src])').forEach(script => {
        if (paramPatterns.some(p => script.textContent.includes(p))) {
            try { (0, eval)(script.textContent); } catch (e) {}
        }
    });

    if (window.wcusCheckoutScriptsLoaded) {
        jQuery(document.body).trigger('updated_checkout');
        return;
    }

    // Inject missing stylesheets from fetched checkout page, before theme styles
    const loadedHrefs = new Set(
        Array.from(document.querySelectorAll('link[rel="stylesheet"]')).map(l => l.href)
    );
    const themeStylesheet = document.querySelector('link[href*="main.css"]');
    Array.from(fetchedDoc.querySelectorAll('link[rel="stylesheet"]'))
        .filter(l => !loadedHrefs.has(l.href))
        .forEach(l => {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = l.href;
            if (themeStylesheet) {
                document.head.insertBefore(link, themeStylesheet);
            } else {
                document.head.appendChild(link);
            }
        });

    // Find scripts present in checkout but not yet loaded in current page
    const loadedSrcs = new Set(
        Array.from(document.querySelectorAll('script[src]')).map(s => s.src)
    );
    const scriptsToLoad = Array.from(fetchedDoc.querySelectorAll('script[src]'))
        .filter(s => !loadedSrcs.has(s.src));

    const loadNext = (index) => {
        if (index >= scriptsToLoad.length) {
            window.wcusCheckoutScriptsLoaded = true;
            // Scripts that initialize on DOMContentLoaded (like checkout2.min.js which mounts Vue)
            // won't self-init when loaded dynamically — re-dispatch the event to trigger them.
            document.dispatchEvent(new Event('DOMContentLoaded'));
            // Use updated_checkout (not update_checkout) to avoid triggering WooCommerce AJAX
            // which blocks the form via $.blockUI() and slows down subsequent navigation.
            // #order_review is already in the DOM from Swup's page fetch.
            setTimeout(() => {
                jQuery(document.body).trigger('updated_checkout');
            }, 100);
            return;
        }
        const el = document.createElement('script');
        el.src = scriptsToLoad[index].src;
        el.onload = () => loadNext(index + 1);
        el.onerror = () => loadNext(index + 1);
        document.body.appendChild(el);
    };

    loadNext(0);
};

const getScrollTargetPosition = (element) => {
    const header = document.querySelector('.header .header-1');
    const headerOffset = header ? header.offsetHeight : 0;

    return element.getBoundingClientRect().top + window.scrollY - headerOffset;
};

const scrollToHashTarget = (hash, updateUrl = false) => {
    if (!hash || !hash.startsWith('#')) {
        return;
    }

    const target = document.getElementById(hash.slice(1));

    if (!target) {
        return;
    }

    if (updateUrl) {
        window.history.pushState({}, '', hash);
    }

    window.requestAnimationFrame(() => {
        window.scrollTo({
            top: getScrollTargetPosition(target),
            behavior: 'smooth'
        });
    });
};

const scrollToPageTop = (updateUrl = false) => {
    if (updateUrl) {
        window.history.replaceState({}, '', `${window.location.pathname}${window.location.search}`);
    }

    window.requestAnimationFrame(() => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
};

const handleCurrentHashScroll = () => {
    if (!window.location.hash || !window.location.hash.startsWith('#go')) {
        return;
    }

    window.setTimeout(() => {
        scrollToHashTarget(window.location.hash);
    }, 120);
};

const normalizePathname = (pathname) => {
    if (!pathname) {
        return '/';
    }

    const normalizedPath = pathname.endsWith('/') ? pathname : `${pathname}/`;

    return normalizedPath === '//' ? '/' : normalizedPath;
};

const isDynamicWooPageUrl = (url) => {
    if (!url) {
        return false;
    }

    try {
        const parsedUrl = new URL(url, window.location.origin);
        const targetPath = normalizePathname(parsedUrl.pathname);
        const cartPath = normalizePathname(new URL(window.rastiTheme?.cartUrl || '/', window.location.origin).pathname);
        const checkoutPath = normalizePathname(new URL(window.rastiTheme?.checkoutUrl || '/', window.location.origin).pathname);

        return targetPath === cartPath || targetPath === checkoutPath;
    } catch (error) {
        return false;
    }
};

const bindHashScrollLinks = () => {
    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href^="/#"], a[href^="#"]');

        if (!link) {
            return;
        }

        if (link.matches('[data-no-swup], [data-fancybox], .ajax_add_to_cart')) {
            return;
        }

        const targetUrl = new URL(link.href, window.location.origin);
        const currentUrl = new URL(window.location.href);
        const targetPath = normalizePathname(targetUrl.pathname);
        const currentPath = normalizePathname(currentUrl.pathname);

        if (targetPath !== currentPath || !targetUrl.hash) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const mobileMenu = link.closest('.js-mobile-menu');
        if (mobileMenu) {
            setTimeout(() => {
                document.body.classList.remove('ovh');
                document.querySelector('.js-burger')?.classList.remove('is-active');
                mobileMenu.classList.remove('is-active');
            }, 500);
        }

        scrollToHashTarget(targetUrl.hash, true);
    }, true);
};

const bindLogoHomeScroll = () => {
    document.addEventListener('click', (event) => {
        const link = event.target.closest('.js-logo-home-link');

        if (!link) {
            return;
        }

        const targetUrl = new URL(link.href, window.location.origin);
        const currentUrl = new URL(window.location.href);
        const targetPath = normalizePathname(targetUrl.pathname);
        const currentPath = normalizePathname(currentUrl.pathname);

        if (targetPath !== '/' || currentPath !== '/') {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        scrollToPageTop(Boolean(currentUrl.hash));
    }, true);
};

const initScrollTopButton = () => {
    const button = document.querySelector('.scroll-top-button');

    if (!button || button.dataset.scrollTopBound === 'true') {
        return;
    }

    button.dataset.scrollTopBound = 'true';

    const toggleButtonVisibility = () => {
        const showButton = window.scrollY >= window.innerHeight * 0.5;

        button.classList.toggle('is-visible', showButton);
        button.setAttribute('aria-hidden', showButton ? 'false' : 'true');
    };

    button.addEventListener('click', () => {
        scrollToPageTop(window.location.hash !== '');
    });

    window.addEventListener('scroll', toggleButtonVisibility, { passive: true });
    window.addEventListener('resize', toggleButtonVisibility);

    toggleButtonVisibility();
};

const initScheduledSiteLock = () => {
    const config = window.rastiTheme?.siteLock;
    const overlay = document.querySelector('[data-site-lock-overlay]');
    const timerElement = overlay?.querySelector('[data-site-lock-timer]');
    const titleElement = overlay?.querySelector('[data-site-lock-title]');
    const titleSubElement = overlay?.querySelector('[data-site-lock-title-sub]');
    const messageElement = overlay?.querySelector('[data-site-lock-message]');

    if (!config?.enabled || !overlay || !timerElement || !titleElement || !messageElement) {
        return;
    }

    if (window.rastiSiteLock?.destroy) {
        window.rastiSiteLock.destroy();
    }

    const serverDeltaMs = window.rastiSiteLock?._serverDeltaMs !== undefined
        ? window.rastiSiteLock._serverDeltaMs
        : Number(config.serverNowMs || 0) - Date.now();

    const state = {
        serverDeltaMs,
        repeatIntervalMs: Number(config.repeatIntervalMs || 86400000),
        timers: Array.isArray(config.timers) ? config.timers.map((timer) => ({
            id: String(timer?.id || ''),
            durationMs: Number(timer?.durationSeconds || 0) * 1000,
            activeStartMs: Number(timer?.activeStartMs || 0),
            nextStartMs: Number(timer?.nextStartMs || 0),
            title: typeof timer?.title === 'string' ? timer.title : '',
            titleSub: typeof timer?.titleSub === 'string' ? timer.titleSub : '',
            message: typeof timer?.message === 'string' ? timer.message : ''
        })).filter((timer) => timer.durationMs > 0 && timer.nextStartMs > 0 || timer.activeStartMs > 0) : [],
        tickTimer: null,
        startTimers: [],
        blockersBound: false
    };

    if (!state.timers.length) {
        return;
    }

    const getNowMs = () => Date.now() + state.serverDeltaMs;
    const formatRemaining = (remainingMs) => {
        const totalSeconds = Math.max(0, Math.ceil(remainingMs / 1000));
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;

        return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    };

    const preventInteraction = (event) => {
        if (!document.body.classList.contains('site-lock-active')) {
            return;
        }

        if (event.type === 'keydown' && ['F5', 'Escape'].includes(event.key)) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    };

    const bindBlockers = () => {
        if (state.blockersBound) {
            return;
        }

        document.addEventListener('click', preventInteraction, true);
        document.addEventListener('submit', preventInteraction, true);
        document.addEventListener('keydown', preventInteraction, true);
        document.addEventListener('pointerdown', preventInteraction, true);
        document.addEventListener('touchstart', preventInteraction, true);
        document.addEventListener('wheel', preventInteraction, { passive: false, capture: true });
        state.blockersBound = true;
    };

    const unbindBlockers = () => {
        if (!state.blockersBound) {
            return;
        }

        document.removeEventListener('click', preventInteraction, true);
        document.removeEventListener('submit', preventInteraction, true);
        document.removeEventListener('keydown', preventInteraction, true);
        document.removeEventListener('pointerdown', preventInteraction, true);
        document.removeEventListener('touchstart', preventInteraction, true);
        document.removeEventListener('wheel', preventInteraction, true);
        state.blockersBound = false;
    };

    const showOverlay = () => {
        overlay.hidden = false;
        overlay.setAttribute('aria-hidden', 'false');
        document.documentElement.classList.add('site-lock-active');
        document.body.classList.add('site-lock-active');
        document.body.classList.add('ovh');
        document.body.style.overflow = 'hidden';
        bindBlockers();
    };

    const hideOverlay = () => {
        overlay.hidden = true;
        overlay.setAttribute('aria-hidden', 'true');
        document.documentElement.classList.remove('site-lock-active');
        document.body.classList.remove('site-lock-active');
        document.body.classList.remove('ovh');
        document.body.style.removeProperty('overflow');
        unbindBlockers();
    };

    const stopTick = () => {
        if (state.tickTimer) {
            window.clearInterval(state.tickTimer);
            state.tickTimer = null;
        }
    };

    const scheduleTimerStart = (timer) => {
        const startMs = Number(timer?.nextStartMs || 0);

        if (startMs < 1) {
            return;
        }

        const delay = Math.max(0, startMs - getNowMs());
        const timeoutId = window.setTimeout(() => {
            activateCountdown(timer, startMs);
        }, delay);

        state.startTimers.push(timeoutId);
    };

    const clearScheduledStarts = () => {
        state.startTimers.forEach((timeoutId) => {
            window.clearTimeout(timeoutId);
        });

        state.startTimers = [];
    };

    const setOverlayContent = (timer) => {
        if (timer.title) {
            titleElement.textContent = timer.title;
            titleElement.hidden = false;
        } else {
            titleElement.textContent = '';
            titleElement.hidden = true;
        }

        if (titleSubElement) {
            if (timer.titleSub) {
                titleSubElement.innerHTML = timer.titleSub;
                titleSubElement.hidden = false;
            } else {
                titleSubElement.innerHTML = '';
                titleSubElement.hidden = true;
            }
        }

        if (timer.message) {
            messageElement.innerHTML = timer.message;
            messageElement.hidden = false;
        } else {
            messageElement.innerHTML = '';
            messageElement.hidden = true;
        }
    };

    const finishCountdown = (timer, completedStartMs) => {
        stopTick();
        hideOverlay();
        timer.activeStartMs = 0;
        timer.nextStartMs = completedStartMs + state.repeatIntervalMs;
        scheduleTimerStart(timer);
    };

    const tickCountdown = (timer, startMs) => {
        const endMs = startMs + timer.durationMs;
        const remainingMs = endMs - getNowMs();

        timerElement.textContent = formatRemaining(remainingMs);

        if (remainingMs <= 0) {
            finishCountdown(timer, startMs);
        }
    };

    function activateCountdown(timer, startMs) {
        stopTick();
        clearScheduledStarts();
        setOverlayContent(timer);
        showOverlay();
        tickCountdown(timer, startMs);
        state.tickTimer = window.setInterval(() => {
            tickCountdown(timer, startMs);
        }, 250);
    }

    const nowMs = getNowMs();
    const activeTimer = state.timers.find((timer) => timer.activeStartMs && nowMs < timer.activeStartMs + timer.durationMs);

    if (activeTimer) {
        activateCountdown(activeTimer, activeTimer.activeStartMs);
    } else {
        hideOverlay();
        clearScheduledStarts();
        state.timers.forEach((timer) => {
            scheduleTimerStart(timer);
        });
    }

    window.rastiSiteLock = {
        _serverDeltaMs: serverDeltaMs,
        destroy: () => {
            stopTick();
            clearScheduledStarts();
            hideOverlay();
        }
    };
};

const initLazyLoad = () => {
    const imgs = document.querySelectorAll('img.lazy-img[data-src]:not([data-lazy-observed])');

    if (!imgs.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            const img = entry.target;

            if (!img.dataset.src) {
                observer.unobserve(img);
                return;
            }

            const picture = img.closest('picture');
            if (picture) {
                picture.querySelectorAll('source[data-srcset]').forEach((source) => {
                    source.srcset = source.dataset.srcset;
                    source.removeAttribute('data-srcset');
                });
            }

            img.src = img.dataset.src;
            img.removeAttribute('data-src');
            img.removeAttribute('data-lazy-observed');
            observer.unobserve(img);
        });
    }, { rootMargin: '200px 0px' });

    imgs.forEach((img) => {
        img.dataset.lazyObserved = 'true';
        observer.observe(img);
    });
};

const initCartNotification = () => {
    if (typeof jQuery === 'undefined') {
        return;
    }

    const notification = document.getElementById('cart-notification');

    if (!notification || notification.dataset.cartNotificationBound === 'true') {
        return;
    }

    notification.dataset.cartNotificationBound = 'true';

    const textEl = notification.querySelector('.cart-notification__text');
    const closeBtn = notification.querySelector('.cart-notification__close');
    let hideTimer = null;

    const hideNotification = () => {
        notification.classList.remove('is-active');
        if (hideTimer) {
            window.clearTimeout(hideTimer);
            hideTimer = null;
        }
    };

    const showNotification = (productName) => {
        if (hideTimer) {
            window.clearTimeout(hideTimer);
        }

        if (textEl) {
            textEl.textContent = productName
                ? `«${productName}» додано до кошику`
                : 'Товар додано до кошику';
        }

        notification.classList.add('is-active');
        hideTimer = window.setTimeout(hideNotification, 6000);
    };

    if (closeBtn) {
        closeBtn.addEventListener('click', hideNotification);
    }

    const linkEl = notification.querySelector('.cart-notification__link');
    if (linkEl) {
        linkEl.addEventListener('click', hideNotification);
    }

    document.addEventListener('swup:visit:start', hideNotification);

    jQuery(document.body).on('added_to_cart', function (event, fragments, cartHash, $button) {
        let productName = '';

        if ($button && $button.length) {
            const product = $button[0].closest('.product, .woocommerce-product-details__short-description, .summary');
            if (product) {
                const titleEl = product.querySelector('.woocommerce-loop-product__title, h1.product_title, .product_title');
                if (titleEl) {
                    productName = titleEl.textContent.trim();
                }
            }
        }

        showNotification(productName);
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initPreloader();
    bindHashScrollLinks();
    bindLogoHomeScroll();
    bindCheckoutCountrySelects();
    initScrollTopButton();
    initScheduledSiteLock();
    initCartNotification();
    initCartStateSync();

    if (typeof Swup !== 'undefined') {
        const swupPlugins = [];

        if (typeof SwupPreloadPlugin !== 'undefined') {
            swupPlugins.push(new SwupPreloadPlugin());
        }

        const swup = new Swup({
            containers: ['#swup'],
            linkSelector: 'a[href]:not([data-no-swup]):not([data-fancybox])',
            plugins: swupPlugins,
        });

        window.rastiSwup = swup;

        if (swup.hooks && typeof swup.hooks.on === 'function') {
            swup.hooks.on('visit:start', (visit) => {
                const targetUrl = visit?.to?.url || '';

                if (!isDynamicWooPageUrl(targetUrl)) {
                    return;
                }

                visit.cache.read = false;
                visit.cache.write = false;

                if (window.rastiSwup?.cache && typeof window.rastiSwup.cache.delete === 'function') {
                    window.rastiSwup.cache.delete(targetUrl);
                }
            });
            swup.hooks.on('page:view', syncBodyClasses);
            swup.hooks.on('page:view', initProductSliders);
            swup.hooks.on('page:view', initSingleProductGallerySliders);
            swup.hooks.on('page:view', initWooCommerceSingleProduct);
            swup.hooks.on('page:view', initSingleProductInCartState);
            swup.hooks.on('page:view', fetchCartIds);
            swup.hooks.on('page:view', initVariationSwatchesForSwup);
            swup.hooks.on('page:view', initVariationSelectInfo);
            swup.hooks.on('page:view', initCheckoutCountrySelects);
            swup.hooks.on('page:view', initWcUkrShipping);
            swup.hooks.on('page:view', initCartPageFreshContent);
            swup.hooks.on('page:view', initReadMoreExcerpt);
            swup.hooks.on('page:view', initFancybox);
            swup.hooks.on('page:view', initLazyLoad);
            swup.hooks.on('page:view', handleCurrentHashScroll);
            swup.hooks.on('page:view', initScheduledSiteLock);
        }
    }

    initProductSliders();
    initSingleProductGallerySliders();
    initWooCommerceSingleProduct();
    initSingleProductInCartState();
    initVariationSwatchesForSwup();
    initVariationSelectInfo();
    initCheckoutCountrySelects();
    initCartPageFreshContent();
    initReadMoreExcerpt();
    initFancybox();
    initLazyLoad();
    handleCurrentHashScroll();
    initScheduledSiteLock();
});


jQuery(document).ready(function ($) {
    $.ajaxPrefilter(function (options) {
        if (!options.url || options.url.indexOf('wc-ajax=add_to_cart') === -1) {
            return;
        }

        const originalSuccess = options.success;

        options.success = function (response) {
            if (response && response.error && response.product_url && window.rastiSwup && typeof window.rastiSwup.navigate === 'function') {
                window.rastiSwup.navigate(response.product_url);
                return;
            }

            if (originalSuccess) {
                originalSuccess.apply(this, arguments);
            }
        };
    });

    const invalidateSwupCache = (urls = []) => {
        if (!window.rastiSwup?.cache || typeof window.rastiSwup.cache.delete !== 'function') {
            return;
        }

        urls.forEach((url) => {
            if (!url) {
                return;
            }

            try {
                const parsedUrl = new URL(url, window.location.origin);
                window.rastiSwup.cache.delete(parsedUrl.pathname + parsedUrl.search);
            } catch (error) {
                // Ignore invalid cache keys.
            }
        });
    };

    const refreshCheckoutCartUi = (response) => {
        if (!response?.success) {
            return;
        }

        // Очищаємо весь кеш щоб продуктові сторінки оновили стан кнопки
        if (window.rastiSwup?.cache && typeof window.rastiSwup.cache.clear === 'function') {
            window.rastiSwup.cache.clear();
        } else {
            invalidateSwupCache([
                response.data?.cartUrl,
                window.location.href
            ]);
        }

        if (response.data?.cartCountHtml) {
            $('li.cart-count').replaceWith(response.data.cartCountHtml);
        }

        if (response.data?.isCartEmpty && response.data?.cartUrl) {
            if (window.rastiSwup && typeof window.rastiSwup.navigate === 'function') {
                window.rastiSwup.navigate(response.data.cartUrl);
                return;
            }

            window.location.href = response.data.cartUrl;
            return;
        }

        if (response.data?.checkoutCartItemsHtml) {
            $('.checkout-order-products').replaceWith(response.data.checkoutCartItemsHtml);
        }

        if (response.data?.checkoutOrderReviewHtml) {
            $('#order_review').html(response.data.checkoutOrderReviewHtml);
        }

        $(document.body).trigger('updated_checkout');
    };

    const refreshHeaderCartCount = () => {
        const ajaxUrl = window.rastiTheme?.ajaxUrl;
        const ajaxNonce = window.rastiTheme?.ajaxNonce;

        if (!ajaxUrl || !ajaxNonce) {
            return;
        }

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'project_theme_get_cart_count',
                nonce: ajaxNonce
            }
        }).done(function (response) {
            if (response?.success && response.data?.cartCountHtml) {
                $('li.cart-count').replaceWith(response.data.cartCountHtml);
            }
        });
    };

    setTimeout(function () {
        $('[aria-current="page"]:not([href="/#go-book"]):not([href="/#go-catalog"])').addClass('js-active');
    }, 500);

    $('body').on('click', '.js-colapps-title', function (e) {
        $(this).toggleClass('active');
        $(this).next('.colapps-result').slideToggle(500);
    })

    $('body').click('.menu a', function (e) {
        console.log('>>>>>> ------');
    });

    $('body').on('click', '.menu a', function (e) {
        // e.preventDefault();
        $('.js-active').removeClass('js-active')
        $(this).addClass('js-active');

        console.log('>>>>>>' );

        if ( $(this).parents('.menu').hasClass('menu-4') ) {
            setTimeout(function () {
                $('body').removeClass('ovh');
                $('.js-burger').removeClass('is-active');
                $('.js-mobile-menu').removeClass('is-active');
            }, 500);
        }
    });

    $(window).scroll(function () {
        if ($(window).scrollTop() > 0 ) {
            $('.header').addClass('scroll');
        } else {
            $('.header').removeClass('scroll');
        }
    });

    $('body').on('click', '.js-burger', function (e) {
        e.preventDefault();
        $('body').toggleClass('ovh');
        $('.js-burger').toggleClass('is-active');
        $('.js-mobile-menu').toggleClass('is-active');
    });

    const submitCartFormViaFetch = ($form) => {
        const $updateButton = $form.find('button[name="update_cart"]');
        $updateButton.prop('disabled', false);

        const formData = new FormData($form[0]);
        formData.set('update_cart', $updateButton.val() || 'Update cart');

        window.fetch($form.attr('action') || window.location.href, {
            method: 'POST',
            credentials: 'same-origin',
            redirect: 'follow',
            body: formData
        })
            .then(function (response) { return response.text(); })
            .then(function (html) {
                const parser = document.createElement('div');
                parser.innerHTML = html;

                const nextCartBox = parser.querySelector('.template-page.cart .box-text');
                const currentCartBox = document.querySelector('.template-page.cart .box-text');

                if (nextCartBox && currentCartBox) {
                    currentCartBox.innerHTML = nextCartBox.innerHTML;
                }

                const nextCartCount = parser.querySelector('li.cart-count');
                if (nextCartCount) {
                    document.querySelectorAll('li.cart-count').forEach(function (el) {
                        el.replaceWith(nextCartCount.cloneNode(true));
                    });
                }

                if (window.rastiSwup?.cache && typeof window.rastiSwup.cache.delete === 'function') {
                    try {
                        window.rastiSwup.cache.delete(window.location.pathname + window.location.search);
                    } catch (err) {}
                }
            });
    };

    $('body').on('change', '.cart-quantity-select', function () {
        const $form = $(this).closest('form.woocommerce-cart-form');
        const $updateButton = $form.find('button[name="update_cart"]');

        if (!$form.length || !$updateButton.length) {
            return;
        }

        if ($form.hasClass('checkout-cart-items-form')) {
            const ajaxUrl = window.rastiTheme?.ajaxUrl;
            const ajaxNonce = window.rastiTheme?.ajaxNonce;
            const $item = $(this).closest('[data-cart-item-key]');
            const cartItemKey = $item.data('cart-item-key');
            const quantity = $(this).val();

            if (!ajaxUrl || !ajaxNonce || !cartItemKey) {
                return;
            }

            $item.css('opacity', '0.5');

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'project_theme_update_checkout_cart_item',
                    nonce: ajaxNonce,
                    cart_item_key: cartItemKey,
                    quantity: quantity
                }
            }).done(function (response) {
                refreshCheckoutCartUi(response);
            });

            return;
        }

        $(this).closest('tr.woocommerce-cart-form__cart-item').css('opacity', '0.5');
        submitCartFormViaFetch($form);
    });

    $('body').on('click', '.woocommerce-cart-form .remove', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $row = $(this).closest('tr.woocommerce-cart-form__cart-item');
        $row.css('opacity', '0.5');

        const removeUrl = $(this).attr('href');
        if (!removeUrl || removeUrl === '#') {
            return false;
        }

        window.fetch(removeUrl, {
            credentials: 'same-origin',
            redirect: 'follow'
        })
            .then(function (response) { return response.text(); })
            .then(function (html) {
                const parser = document.createElement('div');
                parser.innerHTML = html;

                const nextCartBox = parser.querySelector('.template-page.cart .box-text');
                const currentCartBox = document.querySelector('.template-page.cart .box-text');

                if (nextCartBox && currentCartBox) {
                    currentCartBox.innerHTML = nextCartBox.innerHTML;
                }

                const nextCartCount = parser.querySelector('li.cart-count');
                if (nextCartCount) {
                    document.querySelectorAll('li.cart-count').forEach(function (el) {
                        el.replaceWith(nextCartCount.cloneNode(true));
                    });
                }

                if (window.rastiSwup?.cache && typeof window.rastiSwup.cache.delete === 'function') {
                    try {
                        window.rastiSwup.cache.delete(window.location.pathname + window.location.search);
                    } catch (err) {}
                }
            });

        return false;
    });

    $('body').on('click', '.checkout-cart-items-form .remove', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const ajaxUrl = window.rastiTheme?.ajaxUrl;
        const ajaxNonce = window.rastiTheme?.ajaxNonce;
        const $item = $(this).closest('[data-cart-item-key]');
        const cartItemKey = $item.data('cart-item-key');

        $item.css('opacity', '0.5');

        if (!ajaxUrl || !ajaxNonce || !cartItemKey) {
            return;
        }

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'project_theme_remove_checkout_cart_item',
                nonce: ajaxNonce,
                cart_item_key: cartItemKey
            }
        }).done(function (response) {
            refreshCheckoutCartUi(response);
        });

        return false;
    });

$(document.body).on('updated_wc_div updated_cart_totals removed_from_cart added_to_cart', function () {
        refreshHeaderCartCount();
    });

    // $('.xoo-wl-btn-container.xoo-wl-btc-variable').hide();

});
