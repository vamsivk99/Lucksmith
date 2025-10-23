<?php

declare(strict_types=1);

namespace Ls\ProductComparisonSlider\Content\Cms\Struct;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\Struct\Struct;

class ProductComparisonStruct extends Struct
{
    protected string $title;

    protected ProductCollection $products;

    protected array $attributes = [];

    protected string $highlightMode;

    protected string $animationStyle;

    protected bool $showComparisonTable;

    protected bool $enableQuickAdd;

    protected string $colorScheme;

    protected ?string $recommendationProductId;

    protected array $comparisonTags = [];

    protected array $normalizedData = [];

    protected array $highlightMetrics = [];

    public function __construct(
        string $title,
        ProductCollection $products,
        array $attributes,
        string $highlightMode,
        string $animationStyle,
        bool $showComparisonTable,
        bool $enableQuickAdd,
        string $colorScheme,
        ?string $recommendationProductId,
        array $comparisonTags,
        array $normalizedData,
        array $highlightMetrics
    ) {
        $this->title = $title;
        $this->products = $products;
        $this->attributes = $attributes;
        $this->highlightMode = $highlightMode;
        $this->animationStyle = $animationStyle;
        $this->showComparisonTable = $showComparisonTable;
        $this->enableQuickAdd = $enableQuickAdd;
        $this->colorScheme = $colorScheme;
        $this->recommendationProductId = $recommendationProductId;
        $this->comparisonTags = $comparisonTags;
        $this->normalizedData = $normalizedData;
        $this->highlightMetrics = $highlightMetrics;
    }

    public function getApiAlias(): string
    {
        return 'ls_product_comparison_struct';
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getProducts(): ProductCollection
    {
        return $this->products;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getHighlightMode(): string
    {
        return $this->highlightMode;
    }

    public function getAnimationStyle(): string
    {
        return $this->animationStyle;
    }

    public function showComparisonTable(): bool
    {
        return $this->showComparisonTable;
    }

    public function isQuickAddEnabled(): bool
    {
        return $this->enableQuickAdd;
    }

    public function getColorScheme(): string
    {
        return $this->colorScheme;
    }

    public function getRecommendationProductId(): ?string
    {
        return $this->recommendationProductId;
    }

    public function getComparisonTags(): array
    {
        return $this->comparisonTags;
    }

    public function getNormalizedData(): array
    {
        return $this->normalizedData;
    }

    public function getHighlightMetrics(): array
    {
        return $this->highlightMetrics;
    }
}

