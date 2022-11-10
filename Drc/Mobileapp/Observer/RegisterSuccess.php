<?php

declare(strict_types=1);

namespace Drc\Mobileapp\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Api\CustomerRepositoryInterface;

class RegisterSuccess implements ObserverInterface
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
     */
    public function __construct(
        \Fledge\MobileOtpLogin\Helper\Data $helper,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->helper = $helper;
        $this->customerRepository = $customerRepository;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $post['mobile_number'] = $customer['mobile_number'];
        $post['mobile_country_code'] = $customer['mobile_country_code'];
        $post['is_mobile_number_verified'] = $customer['is_mobile_number_verified'];
        $post['country_code'] = $customer['country_code'];
        $post['opted_loyalty_program'] = $customer['opted_loyalty_program'];
        $post['password'] = $customer['password_hash'];
        $post['firstname'] = $customer['firstname'];
        $post['lastname'] = $customer['lastname'];

        $mobileNumber = (isset($post['mobile_number']) && !empty($post['mobile_number'])) ? ltrim($post['mobile_number'], "0"): '';
        $mobileCountryCode = (isset($post['mobile_country_code']) && !empty($post['mobile_country_code'])) ? $post['mobile_country_code']: '';

        if($this->helper->getConfigValue('loyalty/general/active')) {
            $loyaltyHelper = $this->objectManager->create(\Fledge\Loyalty\Helper\Data::class);

            $password = $customer['password_hash'];
            $firstname = $customer['firstname'];
            $lastname = $customer['lastname'];

            $gender = (isset($fields['gender']) && !empty($fields['gender'])) ? $fields['gender']: '';
            $dateOfBirth = (isset($fields['dob']) && !empty($fields['dob'])) ? $fields['dob']: '';
           
            $optedLoyaltyProgram = (isset($post['opted_loyalty_program']) && !empty($post['opted_loyalty_program'])) ? $post['opted_loyalty_program']: 0;
            if($customer && $customer->getId() && $optedLoyaltyProgram) {
                $mobileCountryCode = str_replace("+","",$mobileCountryCode);
                $fields = [
                    'email' => $customer->getEmail(),
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'gender' => $customer->getGender(),
                    'dateOfBirth' => $customer->getDob(),
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
}
