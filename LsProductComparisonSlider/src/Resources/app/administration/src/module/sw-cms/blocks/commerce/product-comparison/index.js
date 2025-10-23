import './component';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'product-comparison',
    label: 'ls-product-comparison-slider.block.label',
    category: 'commerce',
    component: 'sw-cms-block-product-comparison',
    previewComponent: 'sw-cms-preview-product-comparison-slider',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '0px',
        marginRight: '0px'
    },
    slots: {
        comparison: {
            type: 'product-comparison-slider'
        }
    }
});

