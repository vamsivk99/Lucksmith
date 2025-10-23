import template from './sw-cms-el-product-comparison-slider.html.twig';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-product-comparison-slider', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    computed: {
        comparisonTitle() {
            return this.element.config.title?.value || this.element.config.title?.defaultValue;
        },

        productCount() {
            return this.element.config.products?.value?.length || 0;
        }
    }
});

