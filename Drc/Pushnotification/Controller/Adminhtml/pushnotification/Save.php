<?php

namespace Drc\Pushnotification\Controller\Adminhtml\pushnotification;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action
{
    protected $_productRepository;

    protected $pushnotificationcollection;

    protected $adminsession;

    protected $_fileUploaderFactory;

    protected $_mediaDirectory;

    protected $_imageFactory;

    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Drc\Pushnotification\Model\PushnotificationFactory $pushnotificationcollection,
        \Magento\Backend\Model\Session $adminsession,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory  
    ){
        $this->_productRepository = $productRepository;
        $this->pushnotificationcollection = $pushnotificationcollection;
        $this->adminsession = $adminsession;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_imageFactory = $imageFactory; 
        parent::__construct($context);
    }
   
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();     
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {

            if($data['url'] == 2 && $data['product_sku'])
            {
                try{
                    $productObj = $this->_productRepository->get($data['product_sku']);
                }catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Invalid Product Id'));
                    return $resultRedirect->setPath('*/*/edit', ['pushnotification_id' => $this->getRequest()->getParam('pushnotification_id')]);
                }
            }
            $model = $this->pushnotificationcollection->create();
            $id = $this->getRequest()->getParam('pushnotification_id');
            if ($id) {
                $model->load($id);
                $model->setCreatedAt(date('Y-m-d H:i:s'));
            }
			try{
				$uploader = $this->_fileUploaderFactory->create(['fileId' => 'image']);

				$uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
				/** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
				$imageAdapter = $this->_imageFactory->create();
				$uploader->setAllowRenameFiles(true);
				$uploader->setFilesDispersion(false);
				/** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
				$mediaDirectory = $this->_mediaDirectory->getAbsolutePath('emizen_banner');
				$result = $uploader->save($mediaDirectory);
					if($result['error']==0)
					{
						$data['image'] = 'emizen_banner/' . $result['file'];
					} else {
						if(isset($data['image']['delete']) && $data['image']['delete'] == '1') {
							$data['image'] = '';
						} else {
							unset($data['image']);
						}
					}
			} catch (\Exception $e) {
				if(isset($data['image']['delete']) && $data['image']['delete'] == '1') {
					$data['image'] = '';
				} else {
					unset($data['image']);
				}
            }
			
			if(isset($data['image']['delete']) && $data['image']['delete'] == '1') {
				$data['image'] = '';
			} 
            
          	$model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Pushnotification has been saved.'));
                $this->adminsession->setFormData(false); 

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['pushnotification_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Pushnotification.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['pushnotification_id' => $this->getRequest()->getParam('pushnotification_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}