<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Drc\Mobileapp\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\CredentialsValidator;
use Magento\Integration\Model\Oauth\Token as Token;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Framework\Exception\AuthenticationException;

class ListNotification
{
    /**
     * Token Model
     *
     * @var TokenModelFactory
     */
    private $tokenModelFactory;

    /**
     * Customer Account Service
     *
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var \Magento\Integration\Model\CredentialsValidator
     */
    private $validatorHelper;

    /**
     * Token Collection Factory
     *
     * @var TokenCollectionFactory
     */
    private $tokenModelCollectionFactory;

    /**
     * @var RequestThrottler
     */
    private $requestThrottler;

    protected $_customer;
    
    protected $_storemanager;

    protected $pushnotificationcollection;

    protected $_categoryFactory;

    protected $_productRepository;

    /**
     * Initialize service
     *
     * @param TokenModelFactory $tokenModelFactory
     * @param AccountManagementInterface $accountManagement
     * @param TokenCollectionFactory $tokenModelCollectionFactory
     * @param \Magento\Integration\Model\CredentialsValidator $validatorHelper
     */
    public function __construct(
        TokenModelFactory $tokenModelFactory,
        AccountManagementInterface $accountManagement,
        TokenCollectionFactory $tokenModelCollectionFactory,
        CredentialsValidator $validatorHelper,
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        \Drc\Pushnotification\Model\PushnotificationFactory $pushnotificationcollection,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->tokenModelFactory = $tokenModelFactory;
        $this->accountManagement = $accountManagement;
        $this->tokenModelCollectionFactory = $tokenModelCollectionFactory;
        $this->validatorHelper = $validatorHelper;
        $this->_customer = $customer;
        $this->_storemanager = $storemanager;
        $this->pushnotificationcollection = $pushnotificationcollection;
        $this->_categoryFactory = $categoryFactory;
        $this->_productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function UpdatePushnotification($customerid)
    {
        $baseurl = $this->_storemanager->getStore()->getBaseUrl();
        $mediaUrl = $this->_storemanager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $devObj = $this->pushnotificationcollection->create()->getCollection(); 
        $devObj->addFieldToFilter("status",1);
        $devObj->setOrder('sent_on','desc');
        $returnList = array();
        if(!empty($devObj->getData()))
        {
            // var_dump($devObj->getData());
            foreach ($devObj->getData() as $lk => $lv) 
            {
                $category_title='';
                $product_title='';
                $product_id='';
                if(isset($lv['category_id']))
                {
                    $categoryObj = $this->_categoryFactory->create()->load($lv['category_id']);
                    if(is_object($categoryObj)){

                        $category_title=$categoryObj->getName();
                    }
                }

                if(isset($lv['product_sku'])){
                    $productObj = $this->_productRepository->get($lv['product_sku']);
                    if(is_object($productObj)){
                        $product_title=$productObj->getName();
                        $product_id=$productObj->getId();
                    }
                }

                $returnList[$lk]['pushnotification_id']=$lv['pushnotification_id'];
                $returnList[$lk]['title']=$lv['title'];
                $returnList[$lk]['description']=$lv['description'];
                $returnList[$lk]['imageurl']=($lv['image'] != '' ? $mediaUrl.$lv['image'] : '');
                $returnList[$lk]['product_sku']=$lv['product_sku'];
                $returnList[$lk]['category_id']=$lv['category_id'];
                $returnList[$lk]['category_title']=$category_title;
                $returnList[$lk]['product_title']=$product_title;
                $returnList[$lk]['product_id']=$product_id;
                $returnList[$lk]['type']=$lv['url'];
                $returnList[$lk]['notification_type']=$lv['notification_type'];
                if (!empty($lv['date_time'])) {
                    $returnList[$lk]['datetime']=date("d M 'y",strtotime($lv['date_time']));
                } else {
                    $returnList[$lk]['datetime'] = '';
                }
            }
        }

        return $returnList;
    }
}
