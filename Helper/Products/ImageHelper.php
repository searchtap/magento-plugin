<?php

namespace Bitqit\Searchtap\Helper\Products;

use Bitqit\Searchtap\Helper\Logger;
use \Magento\Backend\Block\Template\Context as Context;
use \Magento\Catalog\Helper\Image as ImageFactory;
use \Magento\Store\Model\StoreManagerInterface;

class ImageHelper
{
    private $imageFactory;
    private $logger;
    private $store;

    public function __construct(
        Context $context,
        ImageFactory $productImageHelper,
        Logger $logger,
        StoreManagerInterface $store,
        array $data = []
    )
    {
        $this->imageFactory = $productImageHelper;
        $this->logger = $logger;
        $this->store = $store;
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
            $imageBaseUrl = $this->store->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
            switch ($imageType) {
                case "base_image":
                    $images["image_url"] = $imageBaseUrl . $product->getImage();
                    break;
                case "thumbnail_image":
                    $images["image_url"] = $imageBaseUrl . $product->getThumbnail();
                    break;
                case "small_image":
                    $images["image_url"] = $imageBaseUrl . $product->getSmallImage();
                    break;
                default:
                    $images["image_url"] = $imageBaseUrl . $product->getImage();
            }

            /*
             * on-hover image
             */
            if ($onHoverImageStatus)
                switch ($onHoverImageType) {
                    case "base_image":
                        $images["on_hover_image"] = $imageBaseUrl . $product->getImage();
                        break;
                    case "thumbnail_image":
                        $images["on_hover_image"] = $imageBaseUrl . $product->getThumbnail();
                        break;
                    case "small_image":
                        $images["on_hover_image"] = $imageBaseUrl . $product->getSmallImage();
                        break;
                    default:
                        $images["on_hover_image"] = $imageBaseUrl . $product->getImage();
                }
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
