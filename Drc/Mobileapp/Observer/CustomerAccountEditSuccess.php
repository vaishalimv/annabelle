<?php

declare (strict_types = 1);

namespace Drc\Mobileapp\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Api\CustomerRepositoryInterface;

class CustomerAccountEditSuccess implements ObserverInterface
{
    /**
     * @var \Fledge\MobileOtpLogin\Helper\Data
     */
    protected $helper;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Customer register success
     *
     * @param \Fledge\MobileOtpLogin\Helper\Data $helper
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Fledge\MobileOtpLogin\Helper\Data $helper,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->customerRepository = $customerRepository;
        $this->request = $request;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $email = $customer->getEmail();
        $post['is_mobile_number_verified'] = $customer->getIsMobileNumberVerified();
        $post['country_code'] = $customer->getCountryCode();
        $post['mobile_number'] = $customer->getMobileNumber();
        $post['mobile_country_code'] = $customer->getMobileCountryCode();

        if(isset($post['is_mobile_number_verified']) && !empty($post['is_mobile_number_verified'])) {            
            $countryCode = (isset($post['country_code']) && !empty($post['country_code'])) ? $post['country_code']: '';
            $mobileNumber = (isset($post['mobile_number']) && !empty($post['mobile_number'])) ? ltrim($post['mobile_number'], "0"): '';
            $mobileCountryCode = (isset($post['mobile_country_code']) && !empty($post['mobile_country_code'])) ? $post['mobile_country_code']: '';

            $customerDialCode = $customer->getMobileCountryCode();
            $customerMobileNumber = $customer->getMobileNumber();

            $isMobileNumberVerified = $customer->getIsMobileNumberVerified();
            $isOptedLoyaltyProgram = $customer->getOptedLoyaltyProgram();


            if(!empty($countryCode) && !empty($mobileNumber) && !empty($mobileCountryCode) && $customerMobileNumber != $mobileNumber) {
                $customer->setCustomAttribute(\Fledge\MobileOtpLogin\Setup\InstallData::MOBILE_NUMBER, $mobileNumber);
                $customer->setCustomAttribute(\Fledge\MobileOtpLogin\Setup\InstallData::COUNTRY_CODE, $countryCode);
                $customer->setCustomAttribute(\Fledge\MobileOtpLogin\Setup\InstallData::MOBILE_COUNTRY_CODE, $mobileCountryCode);


                if(!$isMobileNumberVerified) {
                    $isMobileNumberVerified = 1;
                    $customer->setCustomAttribute(\Fledge\MobileOtpLogin\Setup\UpgradeData::IS_MOBILE_NUMBER_VERIFIED, $isMobileNumberVerified);
                }

                if($isMobileNumberVerified && (!$isOptedLoyaltyProgram || empty($isOptedLoyaltyProgram))) {
                    $optedLoyaltyProgram = (isset($post['opted_loyalty_program']) && !empty($post['opted_loyalty_program'])) ? $post['opted_loyalty_program']: 0;
                    $customer->setCustomAttribute(\Fledge\MobileOtpLogin\Setup\UpgradeData::OPTED_LOYALTY_PROGRAM, $optedLoyaltyProgram);

                    if($this->helper->getConfigValue('loyalty/general/active')) {
                        $loyaltyHelper = $this->objectManager->create(\Fledge\Loyalty\Helper\Data::class);

                        $password = $post['password'];
                        $firstname = $post['firstname'];
                        $lastname = $post['lastname'];
                        $gender = (isset($fields['gender']) && !empty($fields['gender'])) ? $fields['gender']: '';
                        $dateOfBirth = (isset($fields['dob']) && !empty($fields['dob'])) ? $fields['dob']: '';
                    
                        if($customer && $customer->getId() && $optedLoyaltyProgram) {
                            $mobileCountryCode = str_replace("+","",$mobileCountryCode);
                            $fields = [
                                'email' => $customer->getEmail(),
                                'firstname' => $firstname,
                                'lastname' => $lastname,
                                'gender' => $gender,
                                'dateOfBirth' => $dateOfBirth,
                                'phoneNumber' => $mobileCountryCode.$mobileNumber,
                                'password' => $password,
                                'appDeviceKey' => '',
                                'loginDevice' => '',
                                'countryId' => $loyaltyHelper->getCountryId()
                            ];
                            
                            $loyaltyHelper->registerUser($fields, $customer);
                        }
                    }
                }

                if($isMobileNumberVerified && $isOptedLoyaltyProgram) {

                    if($this->helper->getConfigValue('loyalty/general/active')) {
                        $loyaltyHelper = $this->objectManager->create(\Fledge\Loyalty\Helper\Data::class);
                        $newMobileCountryCode = str_replace("+","",$mobileCountryCode);
                        $oldMobileCountryCode = str_replace("+","",$customerDialCode);
                        $newMobileNumber = $newMobileCountryCode.$mobileNumber;
                        $oldMobileNumber = $oldMobileCountryCode.$customerMobileNumber;

                        $loyaltyHelper->updateUserMobileNumber($oldMobileNumber, $newMobileNumber);
                    }

                }
                
                $this->customerRepository->save($customer);
            }
            
        }
        

        if(isset($post['change_email']) && $post['change_email'] && $this->helper->getConfigValue('loyalty/general/active')) {
            $oldEmail = (isset($post['old_email']) && !empty($post['old_email'])) ? $post['old_email']: '';
            if(!empty($oldEmail) && !empty($email) && $oldEmail != $email) {
                $loyaltyHelper = $this->objectManager->create(\Fledge\Loyalty\Helper\Data::class);
                $loyaltyHelper->updateUserEmail($oldEmail, $email);
            }
        }
    }
}
