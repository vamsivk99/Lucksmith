import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import WishlistPlugin from 'src/plugin/wishlist/wishlist-storage.plugin';

export default class ProductComparisonSlider extends Plugin {
    init() {
        this.httpClient = new HttpClient();
        this.state = {
            highlightedProduct: null,
            pinnedProduct: null,
            splitScreen: false,
            fullscreen: false,
            currentIndex: 0
        };

        this.products = JSON.parse(this.el.dataset.products || '{}');
        this.highlightMode = this.el.dataset.highlightMode || 'best';
        this.animationStyle = this.el.dataset.animationStyle || 'slide';
        this.showComparisonTable = this.el.dataset.showTable === 'true';
        this.enableQuickAdd = this.el.dataset.quickAdd === 'true';
        this.tags = JSON.parse(this.el.dataset.tags || '[]');

        this.initSlider();
        this.bindComparisonControls();
        this.initQuickCart();
        this.initWishlist();
        this.trackAnalytics();
    }

    initSlider() {
        this.track = this.el.querySelector('[data-role="track"]');
        this.slides = Array.from(this.track.children);
        this.viewport = this.el.querySelector('.ls-comparison-slider__viewport');

        this.applyAnimationStyle();
        this.registerNavigation();
        this.registerSwipeGestures();
        this.registerResizeObserver();
        this.updateSlidePositions();
        this.updateActiveSlideState();
        this.syncAriaAttributes();
    }

    applyAnimationStyle() {
        this.track.dataset.animationStyle = this.animationStyle;
    }

    registerNavigation() {
        this.viewport.addEventListener('keydown', this.onKeyDown.bind(this));
        this.viewport.setAttribute('tabindex', '0');
        this.viewport.setAttribute('role', 'group');
    }

    registerSwipeGestures() {
        let startX = 0;
        let currentX = 0;

        this.viewport.addEventListener('touchstart', (event) => {
            startX = event.touches[0].clientX;
        }, { passive: true });

        this.viewport.addEventListener('touchmove', (event) => {
            currentX = event.touches[0].clientX;
        }, { passive: true });

        this.viewport.addEventListener('touchend', () => {
            const deltaX = currentX - startX;

            if (deltaX > 50) {
                this.showPreviousSlide();
            }

            if (deltaX < -50) {
                this.showNextSlide();
            }
        });
    }

    registerResizeObserver() {
        const observer = new ResizeObserver(() => {
            this.updateSlidePositions();
        });

        observer.observe(this.viewport);
    }

    updateSlidePositions() {
        const viewportWidth = this.viewport.offsetWidth;

        this.slides.forEach((slide, index) => {
            slide.style.transform = `translateX(${(index - this.state.currentIndex) * viewportWidth}px)`;
        });

        this.updateActiveSlideState();
        this.syncAriaAttributes();
    }

    showPreviousSlide() {
        if (this.state.currentIndex === 0) {
            return;
        }

        this.state.currentIndex -= 1;
        this.updateSlidePositions();
    }

    showNextSlide() {
        if (this.state.currentIndex >= this.slides.length - 1) {
            return;
        }

        this.state.currentIndex += 1;
        this.updateSlidePositions();
    }

    onKeyDown(event) {
        if (event.key === 'ArrowLeft') {
            this.showPreviousSlide();
            event.preventDefault();
        }

        if (event.key === 'ArrowRight') {
            this.showNextSlide();
            event.preventDefault();
        }

        if (event.key === 'Home') {
            this.state.currentIndex = 0;
            this.updateSlidePositions();
            event.preventDefault();
        }

        if (event.key === 'End') {
            this.state.currentIndex = this.slides.length - 1;
            this.updateSlidePositions();
            event.preventDefault();
        }
    }

    updateActiveSlideState() {
        this.slides.forEach((slide, index) => {
            const isActive = index === this.state.currentIndex;

            slide.classList.toggle('is-active', isActive);
            slide.classList.toggle('is-inactive', !isActive);

            if (isActive && !slide.classList.contains('ls-comparison-slider__slide--pinned')) {
                slide.classList.add('ls-comparison-slider__slide--focused');
            } else {
                slide.classList.remove('ls-comparison-slider__slide--focused');
            }
        });

        const activeSlide = this.slides[this.state.currentIndex];

        if (activeSlide) {
            activeSlide.focus({ preventScroll: true });
        }
    }

    syncAriaAttributes() {
        this.slides.forEach((slide, index) => {
            const isActive = index === this.state.currentIndex;
            slide.setAttribute('aria-hidden', (!isActive).toString());
            slide.setAttribute('tabindex', isActive ? '0' : '-1');
        });
    }

    bindComparisonControls() {
        const controls = this.el.querySelectorAll('.ls-comparison-slider__control');

        controls.forEach((control) => {
            control.addEventListener('click', (event) => {
                const action = event.currentTarget.dataset.action;

                switch (action) {
                    case 'pin':
                        this.togglePin();
                        break;
                    case 'split':
                        this.toggleSplitScreen();
                        break;
                    case 'fullscreen':
                        this.toggleFullscreen();
                        break;
                    case 'share':
                        this.shareComparison();
                        break;
                }
            });

            control.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    control.click();
                }
            });
        });
    }

    togglePin() {
        const currentSlide = this.slides[this.state.currentIndex];

        if (!currentSlide) {
            return;
        }

        const productId = currentSlide.dataset.productId;

        if (this.state.pinnedProduct === productId) {
            this.state.pinnedProduct = null;
            currentSlide.classList.remove('ls-comparison-slider__slide--pinned');
        } else {
            this.state.pinnedProduct = productId;
            this.slides.forEach((slide) => slide.classList.remove('ls-comparison-slider__slide--pinned'));
            currentSlide.classList.add('ls-comparison-slider__slide--pinned');
        }
    }

    toggleSplitScreen() {
        this.state.splitScreen = !this.state.splitScreen;
        this.el.classList.toggle('ls-comparison-slider--split', this.state.splitScreen);
    }

    toggleFullscreen() {
        this.state.fullscreen = !this.state.fullscreen;
        this.el.classList.toggle('ls-comparison-slider--fullscreen', this.state.fullscreen);

        if (this.state.fullscreen) {
            this.el.requestFullscreen?.();
        } else if (document.fullscreenElement) {
            document.exitFullscreen?.();
        }
    }

    shareComparison() {
        const url = new URL(window.location.href);
        url.searchParams.set('comparison', JSON.stringify({
            elementId: this.el.dataset.elementId,
            productIds: Object.keys(this.products)
        }));

        if (navigator.share) {
            navigator.share({
                title: document.title,
                url: url.toString()
            }).catch(() => {});
        } else {
            navigator.clipboard.writeText(url.toString()).catch(() => {});
        }
    }

    initQuickCart() {
        if (!this.enableQuickAdd) {
            return;
        }

        const addButtons = this.el.querySelectorAll('[data-action="add-to-cart"]');

        addButtons.forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();

                const productId = event.currentTarget.closest('[data-product-id]')?.dataset.productId;

                if (!productId) {
                    return;
                }

                this.addToCartFromComparison(productId);
            });
        });
    }

    addToCartFromComparison(productId) {
        const payload = JSON.stringify({
            items: [{
                id: productId,
                referencedId: productId,
                type: 'product',
                quantity: 1
            }]
        });

        this.httpClient.post('/store-api/checkout/cart/line-item', payload, () => {
            this.dispatchCheckoutEvent('cart-add', productId);
            this.trackAnalyticsEvent('comparison_add_to_cart', productId);
        });
    }

    dispatchCheckoutEvent(eventName, productId) {
        const event = new CustomEvent(`ls-comparison-slider:${eventName}`, {
            detail: {
                productId
            }
        });

        document.body.dispatchEvent(event);
    }

    trackAnalytics() {
        this.trackAnalyticsEvent('comparison_view');
    }

    trackAnalyticsEvent(eventName, productId = null) {
        if (!window.gtag && !window.dataLayer) {
            return;
        }

        const productIds = Object.keys(this.products);

        if (window.gtag) {
            window.gtag('event', eventName, {
                items: productIds,
                productId
            });
        }

        if (window.dataLayer) {
            window.dataLayer.push({
                event: eventName,
                items: productIds,
                productId
            });
        }
    }

    initWishlist() {
        const wishlistButtons = this.el.querySelectorAll('[data-action="wishlist"]');

        if (wishlistButtons.length === 0) {
            return;
        }

        wishlistButtons.forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();

                const productId = event.currentTarget.closest('[data-product-id]')?.dataset.productId;

                if (!productId) {
                    return;
                }

                this.addToWishlist(productId);
            });
        });
    }

    addToWishlist(productId) {
        const wishlistPlugin = WishlistPlugin.getPluginInstanceFromElement(document.body);

        if (!wishlistPlugin) {
            return;
        }

        wishlistPlugin.add(productId).then(() => {
            this.trackAnalyticsEvent('comparison_add_to_wishlist', productId);
        }).catch(() => {});
    }
}

