<?php

declare(strict_types=1);

namespace Ls\ProductComparisonSlider\Service;

use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewCollection;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;

class ProductComparisonService
{
    public function normalizeProducts(ProductCollection $products, array $attributes): array
    {
        $normalized = [];

        foreach ($products as $product) {
            $normalized[$product->getId()] = $this->normalizeProduct($product, $attributes);
        }

        return $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeProduct(ProductEntity $product, array $attributes): array
    {
        $data = [
            'name' => $product->getTranslation('name'),
            'cover' => $product->getCover()?->getMedia(),
            'availableStock' => $product->getAvailableStock(),
            'isCloseout' => $product->getIsCloseout(),
            'rating' => $product->getRatingAverage(),
            'reviewCount' => $this->resolveReviewCount($product->getReviews()),
        ];

        if (
            $attributes === [] ||
            \in_array('price', $attributes, true)
        ) {
            $data['price'] = $this->resolvePrice($product);
        }

        if (\in_array('properties', $attributes, true)) {
            $data['properties'] = $product->getSortedProperties();
        }

        if (\in_array('customFields', $attributes, true)) {
            $data['customFields'] = $product->getCustomFields() ?? [];
        }

        if (\in_array('availability', $attributes, true)) {
            $data['availability'] = $product->getAvailableStock() > 0;
        }

        if (\in_array('deliveryTime', $attributes, true)) {
            $data['deliveryTime'] = $product->getDeliveryTime()?->getTranslation('name');
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function calculateHighlightMetrics(array $normalizedProducts, string $highlightMode): array
    {
        $metrics = [];

        if ($highlightMode === 'neutral') {
            return $metrics;
        }

        $attributes = $this->collectAttributes($normalizedProducts);

        foreach ($attributes as $attribute) {
            $metricValues = [];

            foreach ($normalizedProducts as $productId => $data) {
                if (!\array_key_exists($attribute, $data)) {
                    continue;
                }

                $metricValues[$productId] = $data[$attribute];
            }

            if ($metricValues === []) {
                continue;
            }

            if ($highlightMode === 'best') {
                $this->markBestValues($metrics, $attribute, $metricValues);
            }

            if ($highlightMode === 'difference') {
                $metrics[$attribute] = $metricValues;
            }
        }

        return $metrics;
    }

    /**
     * @param array<string, array<string, mixed>> $normalizedProducts
     *
     * @return string[]
     */
    private function collectAttributes(array $normalizedProducts): array
    {
        $attributes = [];

        foreach ($normalizedProducts as $data) {
            $attributes = \array_unique(\array_merge($attributes, \array_keys($data)));
        }

        return $attributes;
    }

    /**
     * @param array<string, array<string, mixed>> $metrics
     * @param array<string, mixed> $metricValues
     */
    private function markBestValues(array &$metrics, string $attribute, array $metricValues): void
    {
        $bestProductId = null;
        $bestValue = null;

        foreach ($metricValues as $productId => $value) {
            $score = $this->extractComparableScore($attribute, $value);

            if ($score === null) {
                continue;
            }

            if ($bestValue === null || $this->isBetterScore($attribute, $score, $bestValue)) {
                $bestValue = $score;
                $bestProductId = $productId;
            }
        }

        if ($bestProductId === null) {
            return;
        }

        $metrics[$attribute] = [
            $bestProductId => $metricValues[$bestProductId]
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolvePrice(ProductEntity $product): array
    {
        $price = $product->getCalculatedPrice();

        if (!$price instanceof CalculatedPrice) {
            return ['value' => null];
        }

        $listPrice = $price->getListPrice();

        return [
            'value' => $price->getTotalPrice(),
            'unit' => $price->getUnitPrice(),
            'listPrice' => $listPrice?->getPrice(),
            'discount' => $listPrice !== null ? $listPrice->getPrice() - $price->getTotalPrice() : null
        ];
    }

    private function resolveReviewCount(?ProductReviewCollection $reviews): int
    {
        if ($reviews === null) {
            return 0;
        }

        return $reviews->count();
    }

    /**
     * @param mixed $value
     */
    private function extractComparableScore(string $attribute, $value): ?float
    {
        if ($attribute === 'price') {
            if (!\is_array($value) || !\array_key_exists('value', $value)) {
                return null;
            }

            return (float) $value['value'];
        }

        if ($attribute === 'rating') {
            return (float) $value;
        }

        if ($attribute === 'availability') {
            return (float) ($value ? 1 : 0);
        }

        if ($attribute === 'deliveryTime') {
            if (!\is_string($value) || $value === '') {
                return null;
            }

            return (float) \strlen($value) * -1;
        }

        return null;
    }

    private function isBetterScore(string $attribute, float $candidate, float $current): bool
    {
        if ($attribute === 'price') {
            return $candidate < $current;
        }

        if ($attribute === 'deliveryTime') {
            return $candidate > $current;
        }

        return $candidate > $current;
    }
}

