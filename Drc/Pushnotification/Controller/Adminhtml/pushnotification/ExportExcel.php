<?php

namespace Drc\Pushnotification\Controller\Adminhtml\pushnotification;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportExcel extends \Magento\Backend\App\Action
{
    protected $fileFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->_view->loadLayout(false);
        $fileName = 'pushnotification.xml';
        $exportBlock = $this->_view->getLayout()->createBlock('Drc\Pushnotification\Block\Adminhtml\Pushnotification\Grid');        
        return $this->fileFactory->create(
            $fileName,
            $exportBlock->getExcelFile(),
            DirectoryList::VAR_DIR
        );
    }
}