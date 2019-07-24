<?php

class Queue
{
    private $queueFactory;

    public function __construct(
        \Bitqit\Searchtap\Model\QueueFactory $queueFactory
    )
    {
        $this->queueFactory = $queueFactory;
    }

    public function addToQueue($data)
    {
        $model = $this->queueFactory->create();
        $model->setData($data);
        $model->save();
    }

    public function getData()
    {
        $model = $this->queueFactory->create();
        $collection = $model->getCollection();
    }
}