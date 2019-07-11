<?php

namespace Clerk\Clerk\Helper;

/**
 * Class Product
 */
class Product
{
    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $stockHelper;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistryStorage
     */
    protected $stockRegistryStorage;

    /**
     * Product constructor.
     * @param \Magento\CatalogInventory\Helper\Stock $stockHelper
     * @param \Magento\CatalogInventory\Model\StockRegistryStorage $stockRegistryStorage
     */
    public function __construct(
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        \Magento\CatalogInventory\Model\StockRegistryStorage $stockRegistryStorage,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper
    ) {
        $this->stockHelper = $stockHelper;
        $this->stockRegistryStorage = $stockRegistryStorage;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * Checks if product is salable
     *
     * Works around problems with cached
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isSalable(\Magento\Catalog\Model\Product $product)
    {
        $productId = $product->getId();

        // isSalable relies on status that is assigned after initial product load
        // stock registry holds cached old stock status, invalidate to force reload
        $this->stockRegistryStorage->removeStockStatus($productId);
        $this->stockHelper->assignStatusToProduct($product);

        return $product->isSalable();
    }

    /**
     * Format the given value as currency
     *
     * @param float $value
     * @return mixed
     */
    public function formatCurrency($value)
    {
        return $this->pricingHelper->currency($value, true, false);
    }
}