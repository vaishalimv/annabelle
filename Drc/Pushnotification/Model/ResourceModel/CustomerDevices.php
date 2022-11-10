<?php
namespace Drc\Pushnotification\Model\ResourceModel;

class CustomerDevices extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('customer_devices', 'id');
    }
}
