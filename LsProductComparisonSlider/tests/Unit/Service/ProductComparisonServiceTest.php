<?php

declare(strict_types=1);

namespace Ls\ProductComparisonSlider\Tests\Unit\Service;

use Ls\ProductComparisonSlider\Service\ProductComparisonService;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewCollection;
use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewEntity;

class ProductComparisonServiceTest extends TestCase
{
    public function testNormalizeProducts(): void
    {
        $service = new ProductComparisonService();

        $product = new ProductEntity();
        $product->setId('product-id');
        $product->setTranslation('name', 'Product Name');
        $product->setCalculatedPrice(new CalculatedPrice(1, 1, new PriceCollection(), new QuantityPriceCollection(), []));
        $product->setReviews(new ProductReviewCollection([
            (new ProductReviewEntity())->assign(['id' => 'review-1']),
            (new ProductReviewEntity())->assign(['id' => 'review-2']),
        ]));

        $collection = new ProductCollection([$product]);

        $normalized = $service->normalizeProducts($collection, ['price', 'rating', 'deliveryTime']);

        static::assertArrayHasKey('product-id', $normalized);
        static::assertArrayHasKey('price', $normalized['product-id']);
        static::assertSame(2, $normalized['product-id']['reviewCount']);
    }

    public function testCalculateHighlightMetricsPrefersLowerPrice(): void
    {
        $service = new ProductComparisonService();

        $normalizedProducts = [
            'product-a' => ['price' => ['value' => 199.0]],
            'product-b' => ['price' => ['value' => 149.0]],
        ];

        $metrics = $service->calculateHighlightMetrics($normalizedProducts, 'best');

        static::assertArrayHasKey('price', $metrics);
        static::assertArrayHasKey('product-b', $metrics['price']);
        static::assertSame(['value' => 149.0], $metrics['price']['product-b']);
    }
}

