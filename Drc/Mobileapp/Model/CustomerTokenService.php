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

class CustomerTokenService
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
    public function createCustomerAccessToken($username, $password,$devicetoken, $devicetype, $deviceid)
    {
        $this->validatorHelper->validate($username, $password);
        $this->getRequestThrottler()->throttle($username, RequestThrottler::USER_TYPE_CUSTOMER);
        try {
            $customerDataObject = $this->accountManagement->authenticate($username, $password);
            $customerId = $customerDataObject->getId();

            /* device entry */

            /* check device ID */
            $devObj = $this->customdeviceFactory->create()->getCollection();
            $devObj->addFieldToFilter("device_id",$deviceid);

            if(!empty($devObj->getData()))
            {
                //device found then update token with deviceId

                foreach($devObj as $item)
                {
                    $item->setCustomerId($customerId);
                    $item->setDeviceType($devicetype);
                    $item->setDeviceToken($devicetoken);
                }

                $devObj->save();

            }else{
                //insert token with deviceID
                $tblDev = $this->customdeviceFactory->create();
                $tblDev->addData([
                            'customer_id' => $customerId,
                            'device_type' => $devicetype,
                            'device_token' => $devicetoken,
                            'device_id' => $deviceid
                        ]);
                $tblDev->save();
            }

            /* device entry end */

        } catch (\Exception $e) {
            $this->getRequestThrottler()->logAuthenticationFailure($username, RequestThrottler::USER_TYPE_CUSTOMER);
            throw new AuthenticationException(
                __('You did not sign in correctly or your account is temporarily disabled.')
            );
        }
        $this->getRequestThrottler()->resetAuthenticationFailuresCount($username, RequestThrottler::USER_TYPE_CUSTOMER);
        return $this->tokenModelFactory->create()->createCustomerToken($customerDataObject->getId())->getToken();
    }

    /**
     * Revoke token by customer id.
     *
     * The function will delete the token from the oauth_token table.
     *
     * @param string $email
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function revokeCustomerAccessToken($email,$devicetoken, $devicetype, $deviceid)
    {

        $websiteID = $this->_storemanager->getStore()->getWebsiteId();
        $customer = $this->_customer->create()->setWebsiteId($websiteID)->loadByEmail($email);
        $customerId = $customer->getId();

        $tokenCollection = $this->tokenModelCollectionFactory->create()->addFilterByCustomerId($customerId);
        try {
            foreach ($tokenCollection as $token) {
                $token->delete();
            }
                /* device entry */
                /* check device ID */
                $devObj = $this->customdeviceFactory->create()->getCollection();
                $devObj->addFieldToFilter("device_id",$deviceid);

                if(!empty($devObj->getData()))
                {
                    //device found then update token with deviceId
                    foreach($devObj as $item)
                    {
                        $item->setCustomerId(0);
                        $item->setDeviceType($devicetype);
                        $item->setDeviceToken($devicetoken);
                    }
                    $devObj->save();
                }

            /* device entry end */

        } catch (\Exception $e) {
            throw new LocalizedException(__('The tokens could not be revoked.'));
        }
        return true;
    }

    /**
     * Get request throttler instance
     *
     * @return RequestThrottler
     * @deprecated
     */
    private function getRequestThrottler()
    {
        if (!$this->requestThrottler instanceof RequestThrottler) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(RequestThrottler::class);
        }
        return $this->requestThrottler;
    }
}
