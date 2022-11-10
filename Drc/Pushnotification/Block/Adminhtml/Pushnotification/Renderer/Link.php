<?php

namespace Drc\Pushnotification\Block\Adminhtml\Pushnotification\Renderer;

use Magento\Framework\DataObject;

class Link extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    
    protected $_urlInterface;

    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->_urlInterface = $urlInterface;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
      if($row->getSentOn() == '0000-00-00 00:00:00' || (empty($row->getSentOn())) && $row->getStatus() == 0)
      {
        $url = $this->getColumn()->getPath();
        echo '<a href="'.$this->_urlInterface->getUrl('pushnotification/pushnotification/send/',['pid'=>$row->getPushnotificationId()]).'">Send Now</a>';
      } 
    }
}