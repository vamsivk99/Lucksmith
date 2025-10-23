<?php

declare(strict_types=1);

namespace Ls\ProductComparisonSlider\Tests\Unit\Content\Cms\DataResolver;

use Ls\ProductComparisonSlider\Content\Cms\DataResolver\ProductComparisonSliderResolver;
use Ls\ProductComparisonSlider\Service\ComparisonAnalyticsService;
use Ls\ProductComparisonSlider\Service\ProductComparisonService;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\CmsSlotFieldConfig;
use Shopware\Core\Content\Cms\CmsSlotFieldConfigCollection;
use Shopware\Core\Content\Cms\CmsSlotFieldConfigEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Struct\StructCollection;
use Shopware\Storefront\Page\Cms\CmsPageLoaderResult;

class ProductComparisonSliderResolverTest extends TestCase
{
    public function testGetType(): void
    {
        $resolver = $this->createResolver();

        static::assertSame('product-comparison-slider', $resolver->getType());
    }

    private function createResolver(): ProductComparisonSliderResolver
    {
        return new ProductComparisonSliderResolver(
            $this->createMock(ProductComparisonService::class),
            $this->createMock(ComparisonAnalyticsService::class),
            $this->createMock(EntityRepository::class)
        );
    }
}

