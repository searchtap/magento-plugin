<?php

namespace Bitqit\Searchtap\Model\Config\Source;


class GetCategoryAttributes implements \Magento\Framework\Option\ArrayInterface {

    public function toOptionArray()
    {
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
      $categories = $categoryCollection->create();
      $categories->addAttributeToSelect('*');
       foreach($categories as $cat){
          print_r($cat->getName());
     //    $PCategory = $objectManager->get('Magento\Catalog\Model\CategoryFactory')->create()->load($cat->getId());    
        // each $PCategory->getIsCollectionPageListing();

      }

     //  $PCategory = $objectManager->get('Magento\Catalog\Model\CategoryFactory')->create()->load($category->getId());

    //   return $PCategory->getIsCollectionPageListing();

 //  print_r($category->getId());
    }
}
