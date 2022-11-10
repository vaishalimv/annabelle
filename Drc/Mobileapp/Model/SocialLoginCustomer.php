<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Drc\Mobileapp\Model;

use Drc\Mobileapp\Api\SocialLoginCustomerInterface;
use Magento\Framework\App\RequestFactory;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Store\Model\ScopeInterface;

class SocialLoginCustomer implements SocialLoginCustomerInterface
{

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_general/email';
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    /**
     * @var EncryptorInterface
     */
    protected $_encryptor;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var Config\Source\BrandOptions
     */
    protected $brandOptions;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     *
     */
    protected $customerRepository;
    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $addressFactory;
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected $customerCollection;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    protected $_storemanager;

    protected $customerSession;

    /**
    * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    protected $scopeConfig;
    /**
     * CreateCustomer constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param EncryptorInterface $encryptor
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param RequestFactory $requestFactory
     * @param CustomerExtractor $customerExtractor
     * @param AccountManagementInterface $customerAccountManagement
     * @param Config\Source\MotherTongue $brandOptions
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        RequestFactory $requestFactory,
        CustomerExtractor $customerExtractor,
        AccountManagementInterface $customerAccountManagement,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\ResourceModel\Customer\Collection $customerCollection,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenModelFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_request = $request;
        $this->_encryptor = $encryptor;
        $this->customerFactory = $customerFactory;
        $this->requestFactory = $requestFactory;
        $this->customerExtractor = $customerExtractor;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerRepository = $customerRepository;
        $this->addressFactory = $addressFactory;
        $this->customerCollection = $customerCollection;
        $this->_resource = $resource;
        $this->_storemanager = $storemanager;
        $this->_tokenModelFactory = $tokenModelFactory;
        $this->customerSession = $customerSession;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return false|string
     */
    public function socialLogin()
    {
        $customerInfo = $this->_request->getContent();
        if($customerInfo){
            $customerInfo = (array) json_decode($customerInfo);
        }
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('bss_sociallogin');
        $data = array();
        $diff = array_diff_key(['firstname' => 1, 'lastname' => 1, 'email' => 1, 'token_id' => 1], $customerInfo);
        if ($customerInfo && count($diff) == 0) {

            $result = $connection->fetchOne("SELECT `customer_id` FROM ".$tableName." WHERE `token_id`='".$customerInfo['token_id']."'");
            if($result){
                $customerObjModel = $this->customerFactory->create()->getCollection()->addAttributeToFilter('entity_id',$result)->getFirstItem();
                $customerObj = (object)$customerObjModel->getData();

                /* Get customer Token */
                $customer = $this->customerFactory->create()->load($result);
                if(!$customer->getId()) {
                    return 'Not Found';
                } else {
                    // Load customer session
                    $this->customerSession->setCustomerAsLoggedIn($customer);
                    $customerToken = $this->_tokenModelFactory->create();
                    $tokenKey = $customerToken->createCustomerToken($customer->getId())->getToken();
                }

                /* Return respone */
                $customerDetails = array(
                    'customer_id' => $customerObj->entity_id,
                    'firstname' => $customerObj->firstname,
                    'lastname' =>  $customerObj->lastname,
                    'email' => $customerObj->email,
                    'customer_token' => $tokenKey
                );
                $data['status'] = "true";
                $data['msg'] = 'Successfully logged in.';
                $data['customer_info'] =$customerDetails;
            }else{
                $customerData = [
                    'firstname' => $customerInfo['firstname'],
                    'lastname' => $customerInfo['lastname'],
                    'email' => $customerInfo['email']
                ];

                $request = $this->requestFactory->create();
                $request->setParams($customerData);

                try {
                    $websiteID = $this->_storemanager->getStore()->getWebsiteId();
                    $customer = $this->customerFactory->create()->setWebsiteId($websiteID)->loadByEmail($customerInfo['email']);
                    $customer_result = $customer->getData();

                    $customerID = "";
                    $customerFirstName = "";
                    $customerLastName = "";
                    $customerEmail = "";
                    if(!empty($customer_result) && count($customer_result) > 0) {

                        $userData = $customer_result;
                        $customerID = $userData['entity_id'];
                        $customerFirstName = $userData['firstname'];
                        $customerLastName = $userData['lastname'];
                        $customerEmail = $userData['email'];
                        
                        /* Get customer Token */
                        $customer = $this->customerFactory->create()->load($customerID);
                        if(!$customer->getId()) {
                            return 'Not Found';
                        } else {
                            /* Load customer session*/
                            $this->customerSession->setCustomerAsLoggedIn($customer);
                            $customerToken = $this->_tokenModelFactory->create();
                            $tokenKey = $customerToken->createCustomerToken($customer->getId())->getToken();
                        }

                    } else {
                        $customer = $this->customerExtractor->extract('customer_account_create', $request);
                        $customer->setWebsiteId(1);
                        $customerModel = $this->customerRepository->save($customer);

                        $customerID = $customerModel->getId();
                        $customerFirstName = $customerModel->getFirstname();
                        $customerLastName = $customerModel->getLastname();
                        $customerEmail = $customerModel->getEmail();
                        
                        /* Get customer Token */
                        $customer = $this->customerFactory->create()->load($customerModel->getId());
                        if(!$customer->getId()) {
                            return 'Not Found';
                        } else {
                            // Load customer session
                            $this->customerSession->setCustomerAsLoggedIn($customer);
                            $customerToken = $this->_tokenModelFactory->create();
                            $tokenKey = $customerToken->createCustomerToken($customer->getId())->getToken();
                        }
                    }
                    $customerDetails = array(
                        'customer_id' => $customerID,
                        'firstname' => $customerFirstName,
                        'lastname' =>  $customerLastName,
                        'email' => $customerEmail,
                        'customer_token' => $tokenKey
                    );

                    $result = $connection->query("INSERT INTO ".$tableName." (`customer_id`, `type`, `token_id`) VALUES (".$customerID.",'".$customerInfo['type']."','".$customerInfo['token_id']."')");
                    $data['status'] = "true";
                    $data['msg'] = 'Successfully Registered With Us.';
                    $data['customer_info'] =$customerDetails;

                    /* Send Email */
                    $this->inlineTranslation->suspend();
                    $recipientMail = $customerEmail;
                    $templateVars = [
                        'msg' => 'Customer Registered'
                    ];

                    $error = false;
                    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                    $transport = $this->_transportBuilder
                        ->setTemplateIdentifier('customer_create_account_email_template') 
                        ->setTemplateOptions(
                            [
                                'area' => \Magento\Framework\App\Area::AREA_FRONTEND, 
                                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                            ]
                        )
                        ->setTemplateVars($templateVars)
                        ->setFrom(['email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, ScopeInterface::SCOPE_STORE),
                        'name' => $this->scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE)])
                        ->addTo($recipientMail)
                        ->getTransport();

                    $transport->sendMessage();
                    $this->inlineTranslation->resume();
                    /* End Mail */
                } catch (\Exception $e) {
                    $data['status'] = "false";
                    $data['msg'] = $e->getMessage();

                }
        }

        } else {
            $data['status'] = "false";
            $data['msg'] = 'Missing Params';
        }
        
        /* As per our requirement, we have to send social login response to mobile application to print/ process output we have to use echo statement to reflect value. If we use return it just send response without reflecting to mobile app. Also, once we send response to application we have to stop execution of the function we have terminated with exit statement.*/

        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
        exit();
    }
}