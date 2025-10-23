<?php

declare(strict_types=1);

namespace Ls\ProductComparisonSlider\Content\Cms\DataResolver;

use Ls\ProductComparisonSlider\Content\Cms\Struct\ProductComparisonStruct;
use Ls\ProductComparisonSlider\Service\ComparisonAnalyticsService;
use Ls\ProductComparisonSlider\Service\ProductComparisonService;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\CmsElementResolverInterface;
use Shopware\Core\Content\Cms\CmsElementResolverDataCollection;
use Shopware\Core\Content\Cms\Exception\CmsException;
use Shopware\Core\Content\Cms\SalesChannel\SalesChannelCmsElementResolverInterface;
use Shopware\Core\Content\Cms\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\Resolver\ResolverContext;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\Struct;

class ProductComparisonSliderResolver implements CmsElementResolverInterface, SalesChannelCmsElementResolverInterface
{
    private ProductComparisonService $comparisonService;

    private ComparisonAnalyticsService $analyticsService;

    private EntityRepository $productRepository;

    public function __construct(
        ProductComparisonService $comparisonService,
        ComparisonAnalyticsService $analyticsService,
        EntityRepository $productRepository
    ) {
        $this->comparisonService = $comparisonService;
        $this->analyticsService = $analyticsService;
        $this->productRepository = $productRepository;
    }

    public function getType(): string
    {
        return 'product-comparison-slider';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext, CmsElementResolverDataCollection $collection): void
    {
        $config = $slot->getFieldConfig();
        $productConfig = $config->get('products');

        if ($productConfig === null || !$productConfig->getValue()) {
            return;
        }

        $productIds = $productConfig->getValue();

        $criteria = new Criteria($productIds);
        $criteria->addAssociation('cover.media');
        $criteria->addAssociation('properties.group');
        $criteria->addAssociation('prices');
        $criteria->addAssociation('customFields');
        $criteria->addAssociation('deliveryTime');
        $criteria->addAssociation('manufacturer');
        $criteria->addAssociation('seoUrls');
        $criteria->addAssociation('reviews');

        $criteria->addFilter(new EqualsFilter('product.visibilities.salesChannelId', $resolverContext->getSalesChannelContext()->getSalesChannelId()));
        $criteria->addFilter(new EqualsFilter('product.visibilities.visibility', ProductVisibilityDefinition::VISIBILITY_LINK));

        $collection->addEntityResolver($slot, $this->productRepository, $criteria);
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $config = $slot->getFieldConfig();
        $context = $resolverContext->getContext();

        $title = (string)($config->get('title')?->getValue() ?? '');
        $attributes = $config->get('attributes')?->getValue() ?? ['price', 'rating', 'properties'];
        $highlightMode = (string)($config->get('highlightMode')?->getValue() ?? 'best');
        $animationStyle = (string)($config->get('animationStyle')?->getValue() ?? 'slide');
        $showTable = (bool)($config->get('showComparisonTable')?->getValue() ?? true);
        $quickAdd = (bool)($config->get('enableQuickAdd')?->getValue() ?? true);
        $colorScheme = (string)($config->get('colorScheme')?->getValue() ?? 'auto');
        $recommendationProductId = $config->get('recommendationProductId')?->getValue();
        $tags = $config->get('comparisonTags')?->getValue() ?? [];

        $productIds = $config->get('products')?->getValue() ?? [];

        if ($productIds === []) {
            $slot->setData(new ProductComparisonStruct(
                $title,
                new ProductCollection(),
                $attributes,
                $highlightMode,
                $animationStyle,
                $showTable,
                $quickAdd,
                $colorScheme,
                $recommendationProductId,
                $tags,
                [],
                []
            ));

            return;
        }

        $products = $this->resolveProducts($slot, $result, $context, $productIds);

        if ($products->count() < 2) {
            throw CmsException::slotInvalidConfig($slot->getUniqueIdentifier(), 'At least two products are required for comparison.');
        }

        $normalizedData = $this->comparisonService->normalizeProducts($products, $attributes);
        $highlightMetrics = $this->comparisonService->calculateHighlightMetrics($normalizedData, $highlightMode);

        $comparisonStruct = new ProductComparisonStruct(
            $title,
            $products,
            $attributes,
            $highlightMode,
            $animationStyle,
            $showTable,
            $quickAdd,
            $colorScheme,
            $recommendationProductId,
            $tags,
            $normalizedData,
            $highlightMetrics
        );

        $slot->setData($comparisonStruct);

        $this->analyticsService->trackComparison($productIds, $context);
    }

    private function resolveProducts(CmsSlotEntity $slot, ElementDataCollection $result, Context $context, array $productIds): ProductCollection
    {
        $productSearchResult = $result->getEntitySearchResult($slot->getUniqueIdentifier());

        if ($productSearchResult === null) {
            return new ProductCollection();
        }

        $entities = $productSearchResult->getEntities();

        if (!$entities instanceof ProductCollection) {
            return new ProductCollection();
        }

        $sorted = [];

        foreach ($productIds as $productId) {
            if ($entities->has($productId)) {
                $sorted[] = $entities->get($productId);
            }
        }

        return new ProductCollection(array_filter($sorted));
    }
}

