<?php

namespace Clerk\Clerk\Model\Indexer\Product\Action;

use Clerk\Clerk\Model\Api;
use Clerk\Clerk\Model\Config;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;

class Rows
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var Api
     */
    protected $api;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $eventManager
     * @param ProductRepository $productRepository
     * @param Api $api
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $eventManager,
        ProductRepository $productRepository,
        Api $api
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->eventManager = $eventManager;
        $this->productRepository = $productRepository;
        $this->api = $api;
    }

    /**
     * Execute action for given ids
     *
     * @param array|int $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function execute($ids = null)
    {
        if (!$this->scopeConfig->getValue(Config::XML_PATH_PRODUCT_SYNCHRONIZATION_REAL_TIME_ENABLED)) {
            return;
        }

        if (!isset($ids) || empty($ids)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t rebuild the index for an undefined product.')
            );
        }

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        foreach ($ids as $id) {
            $this->reindexRow($id);
        }
    }

    /**
     * Refresh entity index
     *
     * @param int $productId
     * @return void
     */
    protected function reindexRow($productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $this->api->removeProduct($productId);
            return;
        }

        //Cancel if product visibility is not as defined
        if ($product->getVisibility() != $this->scopeConfig->getValue(Config::XML_PATH_PRODUCT_SYNCHRONIZATION_VISIBILITY)) {
            return;
        }

        //Cancel if product is not saleable
        if ($this->scopeConfig->getValue(Config::XML_PATH_PRODUCT_SYNCHRONIZATION_SALABLE_ONLY)) {
            if (!$product->isSalable()) {
                return;
            }
        }

        $store = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
        $imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();

        $productItem = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getFinalPrice(),
            'list_price' => $product->getPrice(),
            'image' => $imageUrl,
            'url' => $product->getUrlModel()->getUrl($product),
            'categories' => $product->getCategoryIds(),
            'sku' => $product->getSku(),
            'on_sale' => ($product->getFinalPrice() < $product->getPrice()),
        ];

        /**
         * @todo Refactor to use fieldhandlers or similar
         */
        $configFields = $this->scopeConfig->getValue(
            Config::XML_PATH_PRODUCT_SYNCHRONIZATION_ADDITIONAL_FIELDS
        );

        $fields = explode(',', $configFields);

        foreach ($fields as $field) {
            if (! isset($productItem[$field])) {
                $productItem[$field] = $product->getData($field);
            }
        }

        $productObject = new \Magento\Framework\DataObject();
        $productObject->setData($productItem);

        $this->eventManager->dispatch('clerk_product_sync_before', ['product' => $productObject]);

        $this->api->addProduct($productObject->toArray());
    }
}
