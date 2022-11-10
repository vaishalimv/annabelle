<?php
namespace Drc\Pushnotification\Model;

class Pushnotification extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Drc\Pushnotification\Model\ResourceModel\Pushnotification');
    }
}
