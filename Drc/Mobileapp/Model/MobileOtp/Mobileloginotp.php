<?php
namespace Drc\Mobileapp\Model\MobileOtp;

use Drc\Mobileapp\Api\WebApiMobileLoginOtpInterface;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Mobileloginotp implements WebApiMobileLoginOtpInterface
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
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    protected $customerRepository;

    /**
     * SendOtp constructor.
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Fledge\MobileOtpLogin\Model\MobileOtp $mobileOtp
     * @param \Fledge\MobileOtpLogin\Helper\Data $helper
     * @param \Fledge\MobileOtpLogin\Helper\Config $configHelper
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Fledge\MobileOtpLogin\Model\MobileOtp $mobileOtp,
        \Fledge\MobileOtpLogin\Helper\Data $helper,
        \Fledge\MobileOtpLogin\Helper\Config $configHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        CustomerRepositoryInterface $customerRepository

    ) {
        $this->mobileOtp = $mobileOtp;
        $this->resultJsonFactory = $jsonFactory;
        $this->helper = $helper;
        $this->configHelper = $configHelper;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->customerRepository = $customerRepository;
    }

    
      /**
     * Returns otp
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException
     * @api 
     * @param mixed $requestMobileOtp
      * @return array[]
     */ 
    public function sendMobileLoginOtp($requestMobileOtp)
    {
        $post = [];
        $response = [];
        $post = $requestMobileOtp;
        
        if(!isset($requestMobileOtp['transactionType']) || empty($requestMobileOtp['transactionType']))
        {
            throw new InvalidTransitionException(__('Transaction type is a required field.'));
        }

        $otp = $this->helper->generateOtp();
        if($requestMobileOtp['transactionType'] == 'registration') {
            $response = $this->sendNewRegistrationOtp($otp, $post);
        }

        if($requestMobileOtp['transactionType'] == 'login') {
            $response = $this->sendLoginOtp($otp, $post);
        }

        if($requestMobileOtp['transactionType'] == 'update-number') {
            $response = $this->sendUpdateMobileOtp($otp, $post);
        }
        return $response;
    }

    /**
     * @param $otp
     * @param $post
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException
     */
    private function sendUpdateMobileOtp($otp, $requestMobileOtp)
    {
        $post = $requestMobileOtp;
        $countryCode = $mobileNumber = $mobileCountryCode = '';
        if(!isset($post['countryCode']) || empty($post['countryCode'])) {
            $response = 'Country code not selected. Please first select country code.';
            return $response;
        } else {
            $countryCode = $post['countryCode'];
        }

        if(!isset($post['mobileCountryCode']) || empty($post['mobileCountryCode'])) {
            $response = 'Country code not selected. Please first select country code.';
            return $response;
        } else {
            $mobileCountryCode = $post['mobileCountryCode'];
        }

        if(!isset($post['mobileNumber']) || empty($post['mobileNumber'])) {
            $response = 'Mobile number is a required field. Please enter mobile number.';
            return $response;
        } else {
            $mobileNumber = ltrim($post['mobileNumber'], "0");
        }

        $isloyaltyModuleEnabled = $this->helper->getConfigValue('loyalty/general/active');
        $userAlreadyExit = false;
        if($isloyaltyModuleEnabled) {
            $loyaltyHelper = $this->objectManager->create(\Fledge\Loyalty\Helper\Data::class);
            if($loyaltyHelper->isUserAlreadyRegistered($mobileNumber)) {
                $userAlreadyExit = true;
            }
        }

        if($this->helper->isMobileNumberAlreadyRegistered($mobileNumber, '', $countryCode) || $userAlreadyExit) {
            $response = 'A customer with the same mobile number already exists.';
            return $response;
        }

        if(!empty($otp) && !empty($mobileCountryCode)) {
            $expiryTime = $this->configHelper->getOtpExpiryTime();
            $body = "Your mobile number update request OTP is $otp. It will expired in $expiryTime minutes";
            $registrationOtpText = $this->configHelper->getUpdateMobileNumberText();

            if(!empty($registrationOtpText)) {
                $body = str_replace(
                    array('{{otp}}', '{{expiry_time}}'),
                    array($otp, $expiryTime),
                    $registrationOtpText
                );
            }

            $messageSID = $this->helper->sendOtp($mobileCountryCode, $mobileNumber, $body);
            if(empty($messageSID)) {
                $response = 'OTP sending failed. Please try after some time.';
                return $response;
            }
            $send_otp = $this->sendOtp($otp, $mobileNumber, null, $post['transactionType'], $messageSID);
        }

        if (!empty($response)) {
            $response_data = ['0' =>['status'=> '200', 'message' => $response]];
        }else{
            $response_data = ['0' =>['status'=> '200', 'data' => $send_otp,  'otp'=>$otp,'mobileNumber'=>$mobileNumber,'transactionType'=>$post['transactionType']]];
        }
        return $response_data;
    }

    /**
     * @param $otp
     * @param $requestMobileOtp
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function sendNewRegistrationOtp($otp, $requestMobileOtp)
    {
        $post = $requestMobileOtp;
        $email = (isset($post['email']) && !empty($post['email'])) ? $post['email']: '';
        if(!empty($email) && $this->helper->isEmailAlreadyRegistered($email)) {
            $response = 'There is already an account with this email address.';
            return $response;
        }

        $countryCode = $mobileNumber = $mobileCountryCode = '';
        if(!isset($post['countryCode']) || empty($post['countryCode'])) {
            $response = 'Country code not selected. Please first select country code.';
            return $response;
        } else {
            $countryCode = $post['countryCode'];
        }

        if(!isset($post['mobileCountryCode']) || empty($post['mobileCountryCode'])) {
            $response = 'Country code not selected. Please first select country code.';
            return $response;
        } else {
            $mobileCountryCode = $post['mobileCountryCode'];
        }

        if(!isset($post['mobileNumber']) || empty($post['mobileNumber'])) {
            $response = 'Mobile number is a required field. Please enter mobile number.';
            return $response;
        } else {
            $mobileNumber = ltrim($post['mobileNumber'], "0");
        }

        $isloyaltyModuleEnabled = $this->helper->getConfigValue('loyalty/general/active');
        $userAlreadyExit = false;
        if($isloyaltyModuleEnabled) {
            $loyaltyHelper = $this->objectManager->create(\Fledge\Loyalty\Helper\Data::class);
            if($loyaltyHelper->isUserAlreadyRegistered($mobileNumber)) {
                $userAlreadyExit = true;
            }
        }

        if($this->helper->isMobileNumberAlreadyRegistered($mobileNumber, '', $countryCode) || $userAlreadyExit) {
            $clickHereHtml = "<a href='javascript:void(0)' id='login-link'>".__('click here')."</a>";
            $response = 'A customer with the same mobile number already exists. Please '.$mobileNumber.' to access your account';
            return $response;
        }

        if(!empty($otp) && !empty($mobileCountryCode)) {
            $expiryTime = $this->configHelper->getOtpExpiryTime();
            $body = "Your registration OTP is $otp. It will expired in $expiryTime minutes";
            $registrationOtpText = $this->configHelper->getRegistrationOtpText();

            if(!empty($registrationOtpText)) {
                $body = str_replace(
                    array('{{otp}}', '{{expiry_time}}'),
                    array($otp, $expiryTime),
                    $registrationOtpText
                );
            }

            $messageSID = $this->helper->sendOtp($mobileCountryCode, $mobileNumber, $body);
            if(empty($messageSID)) {
                $response = 'OTP sending failed. Please try after some time.';
                return $response;
            }
            $send_otp = $this->sendOtp($otp, $mobileNumber, null, $post['transactionType'], $messageSID);
        }
        if (!empty($response)) {
            $response_data = ['0' =>['status'=> '200', 'message' => $response]];
        }else{
            $response_data = ['0' =>['status'=> '200', 'data' => $send_otp,  'otp'=>$otp,'mobileNumber'=>$mobileNumber,'email'=> $email,'transactionType'=>$post['transactionType']]];
        }
        return $response_data;
        
    }

    /**
     * @param $otp
     * @param $requestMobileOtp
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Validate_Exception
     */
    private function sendLoginOtp($otp, $requestMobileOtp)
    {
        $response = [];
        $post = $requestMobileOtp;
        $emailPhone = $email = $mobileNumber = $mobileCountryCode = '';

        if(!isset($post['emailPhone']) || empty($post['emailPhone'])) {
            $response = 'Email/Mobile Number is a required field.';
            return $response;
        } else {
            $emailPhone = $post['emailPhone'];
        }

        if(is_numeric($emailPhone) && (!isset($post['mobileCountryCode']) || empty($post['mobileCountryCode']))) {
            $response = 'Country code not selected. Please first select country code.';
            return $response;
        } else {
            $mobileCountryCode = $post['mobileCountryCode'];
        }

        if(!is_numeric($emailPhone)) {
            if(\Zend_Validate::is(trim($post['emailPhone']), 'EmailAddress')) {
                $email = $emailPhone;
            } else {
                $response = 'Please enter a valid email address (Ex: johndoe@domain.com).';
                return $response;
            }
        } elseif (is_numeric($emailPhone)) {
            $mobileNumber = ltrim($emailPhone, "0");
        }

        $customer = $this->helper->isMobileNumberAlreadyRegistered($mobileNumber, $email, '');

        $isMobileNumberVerified = 0;
        $isOptedLoyaltyProgram = 0;

        if($customer && $customer->getId()) {
            $countryCodes = $this->helper->getCustomerCountryCode($customer->getId());
            $mobileCountryCode = (isset($countryCodes['mobileCountryCode']) && !empty($countryCodes['mobileCountryCode'])) ? $countryCodes['mobileCountryCode']: $mobileCountryCode;
            $customer = $this->helper->getCustomerById($customer->getId());
            
            // $isMobileNumberVerified = $customer->getIsMobileNumberVerified();
            // $isOptedLoyaltyProgram = $customer->getOptedLoyaltyProgram();
            
        } else {
            $response = 'Entered Email or Mobile Number is not registered.';
            return $response;
        }

        if(!empty($otp)) {
            $expiryTime = $this->configHelper->getOtpExpiryTime();
            $body = "Your login OTP is $otp. It will expired in $expiryTime minutes";
            $loginOtpText = $this->configHelper->getLoginOtpText();

            if(!empty($loginOtpText)) {
                $body = str_replace(
                    array('{{otp}}', '{{expiry_time}}'),
                    array($otp, $expiryTime),
                    $loginOtpText
                );
            }
            
            $messageSID = '';
            if(!empty($email)) {
                $this->helper->sendOtpOnEmail($otp, $email, 'login');
            } else {
                if(!empty($mobileCountryCode)) {
                    $messageSID = $this->helper->sendOtp($mobileCountryCode, $mobileNumber, $body);
                    if(empty($messageSID)) {
                        $response = 'OTP sending failed. Please try after some time.';
                        return $response;
                    }
                } else {
                    $response = 'Mobile dial code not found.';
                    return $response;
                }
                
            }

            $send_otp = $this->sendOtp($otp, $mobileNumber, $email, $post['transactionType'],$messageSID);

        }

        if (!empty($response)) {
            $response_data = ['0' =>['status'=> '200', 'message' => $response]];
        }else{
            $response_data = ['0' =>['status'=> '200', 'data' =>$send_otp,  'otp'=>$otp,'mobileNumber'=>$mobileNumber,'email'=> $email,'transactionType'=>$post['transactionType']]];
        }
        return $response_data;
    }
 
    /**
     * @param $otp
     * @param null $mobileNumber
     * @param null $email
     * @param $transactionType
     * @param $messageSID
     * @return array
     * @throws \Exception
     */
    private function sendOtp($otp, $mobileNumber = null, $email = null, $transactionType, $messageSID)
    {
        $response = [];
        if(!empty($otp)) {
            try {
                $model = $this->mobileOtp;
                $model->setMobileNumber($mobileNumber);
                $model->setMobileOtp($otp);
                $model->setMessageSid($messageSID);
                $model->setTransactionType($transactionType);
                $model->setEmail($email);
                $model->setStatus(0);
                $model->save();

                $response = [
                    'error' => false,
                    'message' => __('OTP has been send.')
                ];
            } catch (Exception $e) {
                $response = [
                    'error' => true,
                    'message' => __($e->getMessage())
                ];
            }
        }

        return $response;
    }
}
