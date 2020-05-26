<?php

namespace Bitqit\Searchtap\Helper\Products;

use \Magento\Backend\Block\Template\Context as Context;
use \Magento\Catalog\Helper\Image as ImageFactory;

class ImageHelper
{
    const THUMBNAIL_SIZE = 75;

    private $imageFactory;

    public function __construct(
        Context $context,
        ImageFactory $productImageHelper,
        array $data = []
    )
    {
        $this->imageFactory = $productImageHelper;
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
                ->constrainOnly(TRUE)
                ->keepAspectRatio(TRUE)
                ->keepTransparency(TRUE)
                ->keepFrame(FALSE)
                ->getUrl();

        } catch (\Exception $e) {
            $imageUrl = $this->imageFactory->getDefaultPlaceholderUrl($this->getImageType($imageType));
        }

        return $imageUrl;
    }
}
