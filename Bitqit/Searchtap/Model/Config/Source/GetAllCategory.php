<?php

namespace Bitqit\Searchtap\Model\Config\Source;


class GetAllCategory implements \Magento\Framework\Option\ArrayInterface {

    public function toOptionArray()
    {
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

      $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
      $categories = $categoryCollection->create();
      $categories->addAttributeToSelect('*');
      foreach ($categories as  $value) {
            $options[] = ['label' => $value->getName(), 'value' => $value->getId()];
        }
      
      /* return [
            ['value' => '1', 'label' => __('Top Right')],
            ['value' => '2', 'label' => __('Top Left')],
            ['value' => '3', 'label' => __('Middle Right')],
            ['value' => '4', 'label' => __('Middle')],
            ['value' => '5', 'label' => __('Middle Left')],
            ['value' => '6', 'label' => __('Bottom Right')],
            ['value' => '7', 'label' => __('Bottom Left')]
        ];*/
       return $options;

    }
}
