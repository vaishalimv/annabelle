<?php

namespace Drc\Pushnotification\Model\ResourceModel\CustomerDevices;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Drc\Pushnotification\Model\CustomerDevices', 'Drc\Pushnotification\Model\ResourceModel\CustomerDevices');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}
