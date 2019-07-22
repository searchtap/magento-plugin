<?php
namespace Bitqit\Searchtap\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	protected $_postFactory;
	protected $queue;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Bitqit\Searchtap\Model\QueueFactory $queueFactory
		)
	{
		$this->_pageFactory = $pageFactory;
		$this->_queueFactory = $queueFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
        $this->queue = $this->_queueFactory->create();
        $entityId=$this->getRequest()->getParam('entityId',0);

        if($entityId){
            $this->getRequestedEntity($entityId);
        }
        else{
           $this->getTableData();
        }


	}

	public function getTableData(){

        $collection =  $this->queue->getCollection();
        foreach($collection as $item){
            echo "<pre>";
            print_r($item->getData());
            echo "</pre>";
        }
        exit();
        return $this->_pageFactory->create();
    }
    public function getRequestedEntity($entityId){
        $collection =  $this->queue->getCollection()->addFieldToFilter('entity_id', array('in' => $entityId));
        foreach($collection as $item){
            echo "<pre>";
            print_r($item->getData());
            echo "</pre>";
        }
        exit();
        return $this->_pageFactory->create();
    }

}
