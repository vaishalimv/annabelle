<?php
namespace Drc\Pushnotification\Model\ResourceModel;

class Pushnotification extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('pushnotification', 'pushnotification_id');
    }
}
