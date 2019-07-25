<?php

namespace Bitqit\Searchtap\Helper\Products;

class AttributeHelper
{
    private $productAttributeCollectionFactory;
    private $searchtapHelper;

    const INPUT_TYPE = [
        'select',
        'multiselect',
        'price'
    ];

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $productAttributeCollectionFactory,
        \Bitqit\Searchtap\Helper\SearchtapHelper $searchtapHelper
    )
    {
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
        $this->searchtapHelper = $searchtapHelper;
    }

    public function getFilterableAttributesCollection()
    {
        $data = [];

        $collection = $this->productAttributeCollectionFactory->create()
            ->addFieldToFilter('frontend_input', array('in' => self::INPUT_TYPE))
            ->addFieldToFilter('is_filterable', true);

        foreach ($collection as $attribute) {
            $data[] = $this->getObject($attribute);
        }

        return $this->searchtapHelper->okResult($data, count($data));
    }

    public function getObject($attribute)
    {
        $data = [];

        $data['id'] = $attribute->getId();
        $data['attribute_code'] = $attribute->getAttributeCode();
        $data['attribute_label'] = $attribute->getFrontendLabel();
        $data['type'] = $attribute->getData('frontend_input');
        $data['is_filterable'] = $attribute->getIsFilterable();
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