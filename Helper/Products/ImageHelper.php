<?php

namespace Bitqit\Searchtap\Helper\Products;

use Bitqit\Searchtap\Helper\Logger;
use \Magento\Backend\Block\Template\Context as Context;
use \Magento\Catalog\Helper\Image as ImageFactory;

class ImageHelper
{
    private $imageFactory;
    private $logger;

    public function __construct(
        Context $context,
        ImageFactory $productImageHelper,
        Logger $logger,
        array $data = []
    )
    {
        $this->imageFactory = $productImageHelper;
        $this->logger = $logger;
    }

    public function getImages($config, $product)
    {
        $images = [];
        $width = $config["image_width"];
        $height = $config["image_height"];
        $imageType = $config["image_type"];
        $onHoverImageStatus = (boolean)json_decode($config["on_hover_image_status"]);
        $onHoverImageType = $config["on_hover_image_type"];
        $isCacheImage = (boolean)json_decode($config["is_cache_image"]);

        if ($isCacheImage) {
            $images["image_url"] = $this->getResizedImageUrl($product, "product_" . $imageType, $width, $height);

            if ($onHoverImageStatus) {
                $images["on_hover_image"] = $this->getResizedImageUrl($product, "product_" . $onHoverImageType, $width, $height);
            }
        } else {
            $images["image_url"] = $product->getData($this->getImageType($imageType));
            if ($onHoverImageStatus) $images["on_hover_image"] = $product->getData($this->getImageType($onHoverImageType));
        }

        return $images;
    }

    public function getImageType($type)
    {
        if ($type === "base_image") return "image";
        else if ($type === "thumbnail_image") return "thumbnail_image";

        return $type;
    }

    public function getResizedImageUrl($product, $imageType, $width, $height)
    {
        try {
            $imageUrl = $this->imageFactory
                ->init($product, $imageType)
                ->constrainOnly(true)
                ->keepAspectRatio(true)
                ->keepFrame(true)
                ->keepTransparency(false)
                ->resize($width, $height)
                ->getUrl();

        } catch (\Exception $e) {
            $this->logger->error($e);
            $imageUrl = $this->imageFactory->getDefaultPlaceholderUrl($this->getImageType($imageType));
        }

        return $imageUrl;
    }
}
