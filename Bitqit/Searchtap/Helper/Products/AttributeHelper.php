<?php

namespace Bitqit\Searchtap\Helper\Products;

use Magento\Catalog\Helper\Image as ImageHelper;
use Bitqit\Searchtap\Helper\SearchtapHelper as SearchtapHelper;
use Bitqit\Searchtap\Helper\Data as DataHelper;
use Bitqit\Searchtap\Helper\Logger as Logger;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as ProductAttributeCollectionFactory;

class AttributeHelper
{
    private $productAttributeCollectionFactory;
    private $searchtapHelper;
    private $imageHelper;
    private $logger;
    private $dataHelper;

    const INPUT_TYPE = [
        'select',
        'multiselect',
        'price'
    ];

    public function __construct(
        ProductAttributeCollectionFactory $productAttributeCollectionFactory,
        SearchtapHelper $searchtapHelper,
        ImageHelper $imageHelper,
        Logger $logger,
        DataHelper $dataHelper
    )
    {
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
        $this->searchtapHelper = $searchtapHelper;
        $this->imageHelper = $imageHelper;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
    }

    public function getFilterableAttributesCollection($token, $attributeCodes = null)
    {
        try {
            if (!$this->dataHelper->checkCredentials()) {
                return $this->searchtapHelper->error("Invalid credentials");
            }

            if (!$this->dataHelper->checkPrivateKey($token)) {
                return $this->searchtapHelper->error("Invalid token");
            }

            $data = [];
            $collection = $this->productAttributeCollectionFactory->create()
                ->addFieldToFilter('frontend_input', array('in' => self::INPUT_TYPE))
                ->addFieldToFilter('is_filterable', true);

            if ($attributeCodes)
                $collection->addFieldToFilter('attribute_code', ['in' => $attributeCodes]);

            foreach ($collection as $attribute) {
                $data[] = $this->getObject($attribute);
            }

            return $this->searchtapHelper->okResult($data, count($data));

        } catch (error $e) {
            $this->logger->error($e);
            return $this->searchtapHelper->error($e);
        }
    }

    public function getProductUserDefinedAttributeCodes()
    {
        $data = [];
        try {
            $collection = $this->productAttributeCollectionFactory->create()
                ->addFieldToFilter('is_user_defined', true);
            foreach ($collection as $attribute)
                $data[] = $attribute->getAttributeCode();
        } catch (error $e) {
            $this->logger->error($e);
        }
        return $data;
    }

    public function getProductAdditionalAttributes($product)
    {
        $data = [];
        $attributeIds = [];

        try {
            $attributeCodes = $this->getProductUserDefinedAttributeCodes();
            foreach ($attributeCodes as $attribute) {

                if (!$product->getData($attribute))
                    continue;
                $inputType = $product->getResource()->getAttribute($attribute)->getFrontendInput();
                $attributeName = $attribute . "_" . $inputType;
                switch ($inputType) {
                    case "multiselect":
                        $value = $product->getResource()->getAttribute($attribute)->getFrontend()->getValue($product);
                        if ($value) $data[$attributeName] = $this->searchtapHelper->getFormattedArray(explode(",", $value));
                        break;
                    case "select":
                        $value = $product->getResource()->getAttribute($attribute)->getFrontend()->getValue($product);
                        if ($value) $data[$attributeName] = $this->searchtapHelper->getFormattedString($value);
                        break;
                    case "price":
                        $value = $product->getData($attribute);
                        $data[$attributeName] = $this->searchtapHelper->getFormattedPrice($value);
                        break;
                    case "media_image":
                        //todo: This logic should be in ImageHelper
                        $image = $this->imageHelper->init($product, 'category_page_list', ['type' => $attribute]);
                        $data[$attributeName] = $image->getUrl();
                        break;
                    case "boolean":
                        $data[$attributeName] = (bool)$product->getData($attribute);
                        break;
                    case "text":
                    case "textarea":
                        $data[$attributeName] = $this->searchtapHelper->getFormattedString($product->getData());
                        break;
                    default:
                        $value = $product->getData($attribute);
                        if ($value) $data[$attributeName] = $value;
                }
            }

            return $data;
        } catch (error $e) {
            $this->logger->error($e);
            return [];
        }
    }

    public function getObject($attribute)
    {
        $data = [];
        $data['id'] = $attribute->getId();
        $data['attribute_code'] = $attribute->getAttributeCode();
        $data['attribute_label'] = $attribute->getFrontendLabel();
        $data['type'] = $attribute->getFrontendInput();
        $data['is_filterable'] = (int)$attribute->getIsFilterable();
        $data['is_searchable'] = (int)$attribute->getIsSearchable();
        $data['position'] = (int)$attribute->getPosition();
        $data['last_pushed_to_searchtap'] = $this->searchtapHelper->getCurrentDate();
        return $data;
    }

    public function canAttributeBeReindex($attribute)
    {
        if (in_array($attribute->getData('frontend_input'), self::INPUT_TYPE) && (bool)$attribute->getIsFilterable() === true)
            return true;
        return false;
    }
}