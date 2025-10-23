import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'product-comparison-slider',
    label: 'ls-product-comparison-slider.element.label',
    component: 'sw-cms-el-product-comparison-slider',
    previewComponent: 'sw-cms-el-preview-product-comparison-slider',
    configComponent: 'sw-cms-el-config-product-comparison-slider',
    defaultConfig: {
        title: {
            source: 'static',
            value: 'Finde das perfekte Produkt für dich'
        },
        products: {
            source: 'static',
            value: [],
            entity: {
                name: 'product',
                criteria: {
                    limit: 5
                }
            }
        },
        attributes: {
            source: 'static',
            value: ['price', 'rating', 'properties']
        },
        highlightMode: {
            source: 'static',
            value: 'best'
        },
        animationStyle: {
            source: 'static',
            value: 'slide'
        },
        showComparisonTable: {
            source: 'static',
            value: true
        },
        enableQuickAdd: {
            source: 'static',
            value: true
        },
        colorScheme: {
            source: 'static',
            value: 'auto'
        },
        recommendationProductId: {
            source: 'static',
            value: null
        },
        comparisonTags: {
            source: 'static',
            value: []
        }
    }
});

