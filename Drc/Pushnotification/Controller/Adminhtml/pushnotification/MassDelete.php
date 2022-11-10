<?php
namespace Drc\Pushnotification\Controller\Adminhtml\pushnotification;

use Magento\Backend\App\Action;

/**
 * Class MassDelete
 */
class MassDelete extends \Magento\Backend\App\Action
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
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $itemIds = $this->getRequest()->getParam('pushnotification');
        if (!is_array($itemIds) || empty($itemIds)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                foreach ($itemIds as $itemId) {
                    $post = $this->pushnotificationcollection->load($itemId);
                    $post->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($itemIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('pushnotification/*/index');
    }
}