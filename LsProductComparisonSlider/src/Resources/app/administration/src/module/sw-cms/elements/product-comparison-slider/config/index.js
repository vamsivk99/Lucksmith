import template from './sw-cms-el-config-product-comparison-slider.html.twig';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-config-product-comparison-slider', {
    template,

    mixins: [Mixin.getByName('cms-element')] ,

    computed: {
        productCriteria() {
            const criteria = new Shopware.Data.Criteria(1, 25);
            criteria.addAssociation('cover');
            criteria.addAssociation('properties');
            criteria.addAssociation('prices');
            criteria.addAssociation('media');
            return criteria;
        }
    },

    created() {
        this.initElementConfig('product-comparison-slider');
    }
});

