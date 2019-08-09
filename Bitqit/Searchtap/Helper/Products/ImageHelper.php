<?php

namespace Bitqit\Searchtap\Helper\Products;

class ImageHelper
{

    const  IMAGE_TYPE_BASE = 'base_image';
    const IMAGE_TYPE_SMALL='small_image';
    const  IMAGE_TYPE_THUMBNAIL = 'thumbnail_image';
    const  IMAGE_SIZE = 300;
    const  THUMBNAIL_SIZE = 75;
    private $catalogImageFactory;
    private $_productRepository;
    public static $actionTypes = [
        self::IMAGE_TYPE_BASE,
        self::IMAGE_TYPE_THUMBNAIL,
        self::IMAGE_SIZE,
        self::THUMBNAIL_SIZE,
    ];

    public function __construct( \Magento\Backend\Block\Template\Context $context,
                                 \Magento\Catalog\Model\ProductRepository $productRepository,
                                 \Magento\Catalog\Helper\Image $productImageHelper,
                                 array $data = [])
    {
        $this->catalogImageFactory=$productImageHelper;
        $this->_productRepository=$productRepository;
    }

    // Function to get Image base on type

    public function generateImage($product, $type = self::IMAGE_TYPE_BASE, $width = self::IMAGE_SIZE, $height =  self::IMAGE_SIZE)
    {
        $image = null;

        //todo: need to index the required image with defined width and height
        $productImage = $product->getData('small_image');
        //print_r($productImage);
        if (!empty($productImage) && $productImage != 'no_selection') {
            try {
                $image = $this->catalogImageFactory
                    ->init($product,'product_base_image')
                    ->resize($width, $height)->getUrl();

            } catch (\Exception $e) {
                // image not exists
                $image = null;
            }
        }
        //echo $image;
        return $image;
    }

    // Function to get Media gallary image

    //todo: no need
    public function getMediaGallary($product)
    {

        return $product->getResource()->getAttribute('media_gallery');
    }


}