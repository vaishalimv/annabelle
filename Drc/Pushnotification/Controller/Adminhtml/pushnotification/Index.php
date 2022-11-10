<?php

namespace Drc\Pushnotification\Controller\Adminhtml\pushnotification;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPagee;
    protected $messageManager;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_messageManager = $messageManager;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Drc_Pushnotification::pushnotification');
        $resultPage->addBreadcrumb(__('Drc'), __('Drc'));
        $resultPage->addBreadcrumb(__('Manage item'), __('Manage Pushnotification'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Pushnotification'));
        return $resultPage;
    }
}
?>