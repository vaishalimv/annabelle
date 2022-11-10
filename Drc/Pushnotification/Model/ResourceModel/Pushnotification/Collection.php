<?php

namespace Drc\Pushnotification\Model\ResourceModel\Pushnotification;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Drc\Pushnotification\Model\Pushnotification', 'Drc\Pushnotification\Model\ResourceModel\Pushnotification');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}
