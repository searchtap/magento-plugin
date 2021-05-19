<?php

namespace Bitqit\Searchtap\Controller\Products;

use \Magento\Framework\App\Action\Context as Context;
use \Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;
use \Magento\CatalogInventory\Api\StockRegistryInterface as StockRepository;
use \Magento\Checkout\Model\Cart as CartModel;
use \Magento\Framework\Data\Form\FormKey as FormKey;
use \Bitqit\Searchtap\Helper\SearchtapHelper;

class Cart extends \Magento\Framework\App\Action\Action
{
    protected $productRepository;
    protected $stockRepository;
    protected $cartModel;
    protected $formKey;
    protected $searchtapHelper;

    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        StockRepository $stockRegistry,
        CartModel $cartModel,
        FormKey $formKey,
        SearchtapHelper $searchtapHelper
    )
    {
        $this->productRepository = $productRepository;
        $this->stockRepository = $stockRegistry;
        $this->cartModel = $cartModel;
        $this->formKey = $formKey;
        $this->searchtapHelper = $searchtapHelper;

        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $productId = $this->getRequest()->getParam("product_id");
            $quantity = $this->getRequest()->getParam("qty");

            if (!$productId) {
                $this->_setResponse($this->searchtapHelper::$statusCodeBadRequest, "Product id is missing.");
                return;
            };

            /*
             * Proceed only if product exists
             */
            try {
                $product = $this->productRepository->getById($productId);
            } catch (\Exception $error) {
                $this->_setResponse($this->searchtapHelper::$statusCodeBadRequest, $error->getMessage());
                return;
            }

            /*
             * Proceed only if product type is simple
             */
            if ($product->getTypeId() !== "simple") {
                $this->_setResponse($this->searchtapHelper::$statusCodeBadRequest, "Invalid product type.");
                return;
            };

            /*
             * Proceed only if product is in stock and is salable
             */
            if (!$this->getStockStatus($product)) {
                $this->_setResponse($this->searchtapHelper::$statusCodeBadRequest, "Product is out of stock.");
                return;
            };

            $this->addToCart($product, $quantity);

            $this->_setResponse($this->searchtapHelper::$statusCodeOk, "OK");
        } catch (\Exception $error) {
            $this->_setResponse($this->searchtapHelper::$statusCodeInternalError, $error->getMessage());
        }
    }

    public function addToCart($product, $quantity = 1)
    {
        $params = [
            'form_key' => $this->formKey->getFormKey(),
            'product' => $product->getId(),
            'qty' => $quantity
        ];

        $this->cartModel->addProduct($product, $params);
        $this->cartModel->save();
    }

    public function getStockStatus($product)
    {
        if ($product->isSalable()) {
            return $this->stockRepository->getStockItem($product->getId())->getIsInStock();
        }
        return false;
    }

    public function _setResponse($statusCode, $response)
    {
        $this->getResponse()->setHeader('content-type', 'application/json');
        $this->getResponse()->setStatusCode($statusCode);
        $this->getResponse()->setBody($response);
    }
}