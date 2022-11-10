<?php

namespace Drc\Mobileapp\Model\MobileOtp;

use Drc\Mobileapp\Api\WebApiMobileVerifyOtpInterface;

class VerifyOtp implements WebApiMobileVerifyOtpInterface
{

    /**
     * @var \Fledge\MobileOtpLogin\Model\MobileOtp
     */
    protected $mobileOtp;

    /**
     * @var \Fledge\MobileOtpLogin\Helper\Data
     */
    protected $helper;

    /**
     * @var \Fledge\MobileOtpLogin\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezoneInterface;

    protected $_tokenModelFactory;

      /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * VerifyOtp constructor.
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Fledge\MobileOtpLogin\Model\MobileOtp $mobileOtp
     * @param \Fledge\MobileOtpLogin\Helper\Data $helper
     * @param \Fledge\MobileOtpLogin\Helper\Config $configHelper
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Fledge\MobileOtpLogin\Model\MobileOtp $mobileOtp,
        \Fledge\MobileOtpLogin\Helper\Data $helper,
        \Fledge\MobileOtpLogin\Helper\Config $configHelper,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenModelFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
    )
    {
        $this->mobileOtp = $mobileOtp;
        $this->resultJsonFactory = $jsonFactory;
        $this->helper = $helper;
        $this->configHelper = $configHelper;
        $this->sessionFactory = $sessionFactory;
        $this->session = $session;
        $this->timezoneInterface = $timezoneInterface;
        $this->_tokenModelFactory = $tokenModelFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * Returns verify otp
     *
     * @api 
     * @param mixed $verifyMobileOtp
     * @return array[]
     */ 
    public function mobileVerifyOtp($verifyMobileOtp)
    {
        $post = $verifyMobileOtp;
        $response = [];
        if (!isset($post['otp']) || empty($post['otp'])) {
            $response = 'Please enter OTP to verify.';
        }

        $transactionType = (isset($post['transactionType']) && !empty($post['transactionType'])) ? $post['transactionType']: '';

        if (!empty($transactionType) && $transactionType == 'login') {
            $response = $this->checkLogin($post);
        }

        if (!empty($transactionType) && $transactionType == 'forgot' && (!isset($post['emailPhone']) || empty($post['emailPhone']))) {
            $response = 'Please enter valid Email ID/Mobile number';
        } else {
            if (!empty($transactionType) && $transactionType == 'forgot' && isset($post['emailPhone']) && !empty($post['emailPhone'])) {
                $response = $this->verifyForgotPasswordOtp($post);
            }
        }
        if (!empty($transactionType) && $transactionType == 'registration') {
            $mobileOtp = $this->mobileOtp->load($verifyMobileOtp['otp'], 'mobile_otp');
            if ($mobileOtp && $mobileOtp->getId() && $verifyMobileOtp['mobileNumber'] == $mobileOtp->getMobileNumber() && $mobileOtp->getStatus() == 0) {
                return $this->isValidOtp($mobileOtp);
            }else{
                 $response = 'Please Enter valid OTP';
            }
        }

        if (!empty($transactionType) && $transactionType == 'update-number') {
            $mobileOtp = $this->mobileOtp->load($verifyMobileOtp['otp'], 'mobile_otp');
            if ($mobileOtp && $mobileOtp->getId() && $verifyMobileOtp['mobileNumber'] == $mobileOtp->getMobileNumber() && $mobileOtp->getStatus() == 0) {
                return $this->isValidOtp($mobileOtp);
            }else{
                 $response = 'Please Enter valid OTP';
            }
        }

        return $response;
    }

    /**
     * @param $post
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Validate_Exception
     */
    public function checkLogin($verifyMobileOtp)
    {
        $post = $verifyMobileOtp;
        $response = [];
        $mobileNumber = $email = '';
        $mobileOtp = $this->mobileOtp->load($verifyMobileOtp['otp'], 'mobile_otp');
        if(!empty($verifyMobileOtp['email'])) {
            if ($mobileOtp && $mobileOtp->getId() && $email == $mobileOtp->getEmail() && $mobileOtp->getStatus() == 0) {

                $expiryTime = (!empty($this->configHelper->getOtpExpiryTime())) ? (int)$this->configHelper->getOtpExpiryTime() : 5;
                $createdAt = $mobileOtp->getCreatedAt();

                $today = $this->timezoneInterface->date()->format('m/d/y H:i:s');
                $dateTimeAsTimeZone = $this->timezoneInterface
                    ->date(new \DateTime($createdAt))
                    ->format('m/d/y H:i:s');

                $otpSendingTime = strtotime($dateTimeAsTimeZone);
                $currentTime = strtotime(date($today));
                $otpTime = round(abs($otpSendingTime - $currentTime) / 60);

                if (1) 
                {
                    $mobileOtp->setStatus(1);
                    $response = 'email OTP Verified successfully';
                }
            }
        } elseif(!empty($verifyMobileOtp['mobileNumber'])) {
            if ($mobileOtp && $mobileOtp->getId() && $mobileNumber == $mobileOtp->getMobileNumber() && $mobileOtp->getStatus() == 0) {
               $expiryTime = (!empty($this->configHelper->getOtpExpiryTime())) ? (int)$this->configHelper->getOtpExpiryTime() : 5;
                $createdAt = $mobileOtp->getCreatedAt();

                $today = $this->timezoneInterface->date()->format('m/d/y H:i:s');
                $dateTimeAsTimeZone = $this->timezoneInterface
                    ->date(new \DateTime($createdAt))
                    ->format('m/d/y H:i:s');

                $otpSendingTime = strtotime($dateTimeAsTimeZone);
                $currentTime = strtotime(date($today));
                $otpTime = round(abs($otpSendingTime - $currentTime) / 60);

                if (1) 
                {
                    $mobileOtp->setStatus(1);
                    $response = 'Mobile OTP Verified successfully';
                }
            } 
        }

        if($verifyMobileOtp['otp'] == $mobileOtp->getMobileOtp()) {

            if(!empty($verifyMobileOtp['mobileNumber'])) {
                $customers = $this->customerCollectionFactory->create()->addAttributeToFilter('mobile_number', ltrim($verifyMobileOtp['mobileNumber'], '0'));
            }

            if(!empty($verifyMobileOtp['email'])) {
                $customers = $this->customerCollectionFactory->create()
                    ->addAttributeToFilter('email', trim($verifyMobileOtp['email']));
            }
            foreach($customers as $customer){
                if($customer->getEntityId()) {
                    $sessionManager = $this->sessionFactory->create();
                    $sessionManager->setCustomerAsLoggedIn($customer);
                    $sessionManager->regenerateId();
                    $customerToken = $this->_tokenModelFactory->create();
                    $tokenKey = $customerToken->createCustomerToken($customer->getEntityId())->getToken();
                }
            }
            $response = 'You have successfully logged in.';
        } else {
            $response = '';
        }
        
        if (empty($response)) {
            $response_data = ['0' =>['status'=> '200', 'message' => 'Invalid OTP. Please enter a valid OTP']];
        }else{
        $response_data = ['0' =>['status'=> '200', 'message' => $response, 'customer_id'=> $customer->getId(), 'customerEmail' => $customer->getEmail(),'token'=>$tokenKey]];
        }
        return $response_data;
    }

    /**
     * @param $post
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Validate_Exception
     */
    public function verifyForgotPasswordOtp($post)
    {
        $response = [];
        $mobileNumber = $email = '';
        try {

            if(!is_numeric($post['emailPhone'])) {
                if(\Zend_Validate::is(trim($post['emailPhone']), 'EmailAddress')) {
                    $email = $post['emailPhone'];
                } else {
                    $response = 'Please enter a valid email address (Ex: johndoe@domain.com).';
                }
            } elseif (is_numeric($post['emailPhone'])) {
                $mobileNumber = $post['emailPhone'];
            }

            $mobileOtp = $this->mobileOtp->load($post['otp'], 'mobile_otp');
            if(!empty($email)) {
                if ($mobileOtp && $mobileOtp->getId() && $email == $mobileOtp->getEmail() && $mobileOtp->getStatus() == 0) {
                    $response = $this->isValidOtp($mobileOtp);
                }
            } elseif(!empty($mobileNumber)) {
                if ($mobileOtp && $mobileOtp->getId() && $mobileNumber == $mobileOtp->getMobileNumber() && $mobileOtp->getStatus() == 0) {
                    $response = $this->isValidOtp($mobileOtp);
                }
            }

            if(!empty($response) && isset($response['error']) && !$response['error']) {

                $customer = $this->helper->isMobileNumberAlreadyRegistered($mobileNumber, $email, '');
                if($customer && $customer->getId()) {
                    $this->session->setCustId($customer->getId());
                    $this->session->setType('forgotpassword');
                    $response = 'OTP Verified successfully.';
                }
            }
        } catch (Exception $e) {
            $response = $e->getMessage();
        }

        // $response = 'Invalid email id/mobile number and OTP.';
        return $response;
    }

    /**
     * @param $mobileOtp
     * @return array
     * @throws \Exception
     */
    private function isValidOtp($mobileOtp)
    {
        $expiryTime = (!empty($this->configHelper->getOtpExpiryTime())) ? (int)$this->configHelper->getOtpExpiryTime() : 5;
        $createdAt = $mobileOtp->getCreatedAt();

        $today = $this->timezoneInterface->date()->format('m/d/y H:i:s');
        $dateTimeAsTimeZone = $this->timezoneInterface
            ->date(new \DateTime($createdAt))
            ->format('m/d/y H:i:s');

        $otpSendingTime = strtotime($dateTimeAsTimeZone);
        $currentTime = strtotime(date($today));
        $otpTime = round(abs($otpSendingTime - $currentTime) / 60);

        if (1) 
        {
            $mobileOtp->setStatus(1);
            $response = 'OTP Verified successfully.';
        }
        return $response;
    }


    public function isMobileNumberAlreadyRegistereds($mobileNumber, $email, $countryCode)
    {
        if(!empty($mobileNumber)) {
            $collection = $this->customerCollectionFactory->create()
                ->addAttributeToFilter(InstallData::MOBILE_NUMBER, ltrim($mobileNumber, '0'));
        }

        if(!empty($email)) {
            $collection = $this->customerCollectionFactory->create()
                ->addAttributeToFilter('email', trim($email));
        }

        if(!empty($countryCode)) {
            $collection->addAttributeToFilter(InstallData::COUNTRY_CODE, trim($countryCode));
        }

        $collection->addAttribuTeToFilter('website_id', (int) $this->storeManager->getStore()->getWebsiteId());
        if ($collection->getSize() > 0) {
            return $collection->getFirstItem();
        }

        return false;
    }
}

