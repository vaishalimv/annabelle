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

class UpdateToken
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

    protected $customdeviceFactory;
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
        \Drc\Pushnotification\Model\CustomerDevicesFactory $customdeviceFactory 
    ) {
        $this->tokenModelFactory = $tokenModelFactory;
        $this->accountManagement = $accountManagement;
        $this->tokenModelCollectionFactory = $tokenModelCollectionFactory;
        $this->validatorHelper = $validatorHelper;
        $this->_customer = $customer;
        $this->_storemanager = $storemanager;
        $this->customdeviceFactory = $customdeviceFactory; 
    }

    /**
     * {@inheritdoc}
     */
    public function UpdateDeviceToken($deviceid, $devicetoken)
    {
        $devObj = $this->customdeviceFactory->create()->getCollection();
        $devObj->addFieldToFilter("device_id",$deviceid);

        if(!empty($devObj->getData()))
        {
            //device found then update token with deviceId
            foreach($devObj as $item)
            {
                $item->setDeviceToken($devicetoken);
            }
            $devObj->save();
            return $devicetoken;
        }else{
            return 'Device Not found!!!';
        }
    }
}
