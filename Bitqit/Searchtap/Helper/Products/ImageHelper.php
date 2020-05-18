<?php

namespace Bitqit\Searchtap\Helper\Products;

use \Magento\Backend\Block\Template\Context as Context;
use \Magento\Catalog\Helper\Image as ImageFactory;
use \Bitqit\Searchtap\Helper\Products\ProductHelper;
use \Bitqit\Searchtap\Helper\SearchtapHelper;
use \Bitqit\Searchtap\Helper\Data;

class ImageHelper
{
    const THUMBNAIL_SIZE = 75;

    private $imageFactory;
    private $productHelper;
    private $searchtapHelper;
    private $dataHelper;

    public function __construct(
        Context $context,
        ImageFactory $productImageHelper,
        ProductHelper $productHelper,
        SearchtapHelper $searchtapHelper,
        Data $dataHelper,
        array $data = []
    )
    {
        $this->imageFactory = $productImageHelper;
        $this->productHelper = $productHelper;
        $this->searchtapHelper = $searchtapHelper;
        $this->dataHelper = $dataHelper;
    }

    public function getImages($config, $product)
    {
        $images = [];
        $width = $config["image_width"];
        $height = $config["image_height"];
        $imageType = $config["image_type"];
        $onHoverImageType = $config["on_hover_image_type"];

        if ($config["is_cache_image"]) {
            $images["image_url"] = $this->getResizedImageUrl($product, "product_" . $imageType, $width, $height);

            if ($onHoverImageType)
                $images["on_hover_image"] = $this->getResizedImageUrl($product, "product_" . $onHoverImageType, $width, $height);

        } else {
            $images["image_url"] = $product->getData($this->getImageType($imageType));
            $images["on_hover_image"] = $product->getData($this->getImageType($onHoverImageType));
        }

        try {
            $images["thumbnail_url"] = $this->getResizedImageUrl(
                $product,
                "product_base_image",
                self::THUMBNAIL_SIZE,
                self::THUMBNAIL_SIZE);

        } catch (error $e) {
            $images["thumbnail_url"] = $this->imageFactory->getDefaultPlaceholderUrl("image");
        }

        return $images;
    }

    public function getImageType($type)
    {
        if ($type === "base_image") return "image";
        else if ($type === "thumbnail_image") return "thumbnail";

        return $type;
    }

    public function getResizedImageUrl($product, $imageType, $width, $height)
    {
        try {
            $imageUrl = $this->imageFactory
                ->init($product, $imageType)
                ->resize($width, $height)
                ->getUrl();

        } catch (\Exception $e) {
            $imageUrl = $this->imageFactory->getDefaultPlaceholderUrl($this->getImageType($imageType));
        }

        return $imageUrl;
    }

    public function processImages($token, $storeId, $height, $width, $count, $page)
    {
        try {
            if (!$this->dataHelper->checkCredentials()) {
                return $this->searchtapHelper->error("Invalid credentials");
            }

            if (!$this->dataHelper->checkPrivateKey($token)) {
                return $this->searchtapHelper->error("Invalid token");
            }

            if (!$this->dataHelper->isStoreAvailable($storeId)) {
                return $this->searchtapHelper->error("store not found for ID " . $storeId, 404);
            }

            // Start Frontend Simulation
            $this->searchtapHelper->startEmulation($storeId);

            $productCollection = $this->productHelper->getProductCollection($storeId, $count, $page);

            foreach ($productCollection as $product) {
                $this->getResizedImageUrl($product, 'base_image', $width, $height);
                $this->getResizedImageUrl($product, 'small_image', $width, $height);
                $this->getResizedImageUrl($product, 'thumbnail_image', $width, $height);
            }

            // Stop Simulation
            $this->searchtapHelper->stopEmulation();

            return $this->searchtapHelper->okResult("Images Created", $productCollection->getSize());

        } catch (\Exception $e) {
            return $e;
        }
    }
}
