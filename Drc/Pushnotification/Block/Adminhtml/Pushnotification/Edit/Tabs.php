<?php
namespace Drc\Pushnotification\Block\Adminhtml\Pushnotification\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('pushnotification_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Pushnotification Information'));
    }
}