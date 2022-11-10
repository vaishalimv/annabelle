<?php

namespace Drc\Pushnotification\Model;

class PushnotificationFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Drc\Pushnotification\Model\Pushnotification', $arguments, false);
    }
}