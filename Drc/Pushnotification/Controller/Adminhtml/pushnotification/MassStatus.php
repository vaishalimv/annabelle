<?php
namespace Drc\Pushnotification\Controller\Adminhtml\pushnotification;

use Magento\Backend\App\Action;

class MassStatus extends \Magento\Backend\App\Action
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
     * Update blog post(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $itemIds = $this->getRequest()->getParam('pushnotification');
        if (!is_array($itemIds) || empty($itemIds)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                $status = (int) $this->getRequest()->getParam('status');
                foreach ($itemIds as $postId) {
                    $post = $this->pushnotificationcollection->load($postId);
                    $post->setIsActive($status)->save();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been updated.', count($itemIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('pushnotification/*/index');
    }

}