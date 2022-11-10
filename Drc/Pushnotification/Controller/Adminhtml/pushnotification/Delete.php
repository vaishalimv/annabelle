<?php
namespace Drc\Pushnotification\Controller\Adminhtml\pushnotification;

use Magento\Backend\App\Action;

class Delete extends \Magento\Backend\App\Action
{
    protected $pushnotificationcollection;

    public function __construct(
        Action\Context $context,
        \Drc\Pushnotification\Model\PushnotificationFactory $pushnotificationcollection
    ){
        $this->pushnotificationcollection = $pushnotificationcollection;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /*check if we know what should be deleted*/
        $id = $this->getRequest()->getParam('pushnotification_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                /*init model and delete*/
                $model = $this->pushnotificationcollection->create()->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('The item has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                /* display error message */
                $this->messageManager->addError($e->getMessage());
                /*go back to edit form*/
                return $resultRedirect->setPath('*/*/edit', ['pushnotification_id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a item to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}