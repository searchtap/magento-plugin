<?php

namespace Bitqit\Searchtap\Controller\Indexer;

class DeleteQueue extends \Magento\Framework\App\Action\Action
{
    private $queueFactory;
    private $searchtapHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bitqit\Searchtap\Model\QueueFactory $queueFactory,
        \Bitqit\Searchtap\Helper\SearchtapHelper $searchtapHelper
    )
    {
        $this->queueFactory = $queueFactory;
        $this->searchtapHelper = $searchtapHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $entity_id = $this->getRequest()->getParam('entity_id');
        $type=$this->getRequest()->getParam('type');
        $itemID=explode(',',$entity_id);
        $response = $this->queueFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('type',$type);
        if($entity_id)
            $response->addFieldToFilter('entity_id',array('in'=>$itemID));

        foreach($response as $itemId){
            $model = $this->queueFactory->create();
            $model->load($itemId->getId());
            $model->delete();
        }

    }
}