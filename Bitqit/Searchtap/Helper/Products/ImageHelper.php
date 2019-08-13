<?php

namespace Bitqit\Searchtap\Helper\Products;
use \Magento\Backend\Block\Template\Context ;
use \Magento\Catalog\Model\ProductRepository ;
use \Magento\Catalog\Helper\Image;
class ImageHelper
{

    const  IMAGE_TYPE_BASE = 'base_image';
    const  IMAGE_TYPE_SMALL = 'small_image';
    const  IMAGE_TYPE_THUMBNAIL = 'thumbnail_image';
    const  IMAGE_SIZE = 300;
    const  THUMBNAIL_IMAGE_TYPE = 75;

    private $catalogImageFactory;
    private $_productRepository;

    public static $actionTypes = [
        self::IMAGE_TYPE_BASE,
        self::IMAGE_TYPE_THUMBNAIL,
        self::IMAGE_SIZE,
        self::THUMBNAIL_IMAGE_TYPE,
    ];

    public function __construct(Context $context,
                                ProductRepository $productRepository,
                                Image $productImageHelper,
                                array $data = [])
    {
        $this->catalogImageFactory = $productImageHelper;
        $this->_productRepository = $productRepository;
    }

    // Function to get Image

    public function generateImage($product, $type = self::IMAGE_TYPE_BASE, $width = self::IMAGE_SIZE, $height = self::IMAGE_SIZE)
    {
        $image = null;
        $image_type = 'base';
        $productImage = $product->getData($type);
        if (!empty($productImage) && $productImage != 'no_selection') {
            try {

                if ($type == 'thumbnail') {
                    $width = $height = self::THUMBNAIL_IMAGE_TYPE;
                    $image_type = $type;
                }
                $image = $this->catalogImageFactory
                    ->init($product, 'product_' . $image_type . '_image')
                    ->resize($width, $height)->getUrl();

            } catch (\Exception $e) {
                $image = null;// image not exists
            }
        }
        return $image;
    }

}