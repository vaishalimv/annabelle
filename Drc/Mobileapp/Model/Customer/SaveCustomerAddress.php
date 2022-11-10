<?php
namespace Drc\Mobileapp\Model\Customer;

use Drc\Mobileapp\Api\Customer\WebApiAddressInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Directory\Model\RegionFactory;

class SaveCustomerAddress implements WebApiAddressInterface
{
    /**
     * @var Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;
	private $storeManager;
    private $customerFactory;
    protected $addressFactory;
    protected $directoryHelper;
    protected $regionFactory;
    
    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;
    public function __construct(
         \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
        DirectoryHelper $directoryHelper,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        RegionFactory $regionFactory
    ) {
        $this->addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
		$this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->directoryHelper = $directoryHelper;
        $this->_countryFactory = $countryFactory;
        $this->regionFactory = $regionFactory;
    }
    
    /**
     * Returns 
     * @api
     * @param mixed $address
     * @return array
     */
     
    public function saveAddress($address)
    {
        if(isset($address['customer_id']) && $address['customer_id'] != ''){
                
            $cusotmer = $this->customerFactory->create()->load($address['customer_id']);

            $allow_countries = $this->getAllowedCountries();
            if(!in_array($address['country_id'], $allow_countries)){
                 $response[] = ['status'=> '400', 'success'=>false, 'message'=>'Given country_id is not active.', 'data' =>array()]; 
                return $response;
            }

            if($cusotmer->getId()){

                $street[] = (isset($address['street'][0]) && $address['street'][0] != '') ? $address['street'][0] : '';
                $street[] = (isset($address['street'][1]) && $address['street'][1] != '') ? $address['street'][1] : '';
                $default_shipping = "0";
                $default_billing = "0";
                if(isset($address['default_shipping']) && !empty($address['default_shipping'])){                
                    $default_shipping = ($address['default_shipping'] == true) ? "1" : "0";
                }
                if(isset($address['default_billing']) && !empty($address['default_billing'])){
                    $default_billing = ($address['default_billing'] == true) ? "1" : "0";
                }

                $_address = $this->addressFactory->create();
                $_address->setCustomerId($address['customer_id']);
                $_address->setFirstname($address['firstname']);
                $_address->setLastname($address['lastname']);
                $_address->setCountryId($address['country_id']);
                if(isset($address['region_id']) && !empty($address['region_id'])){
                    $_address->setRegionId($address['region_id']);
                } else {
                    $_address->setRegion($address['region']);
                }
                $_address->setCity($address['city']);
                $_address->setPostcode($address['postcode']);
                $_address->setCustomerId($address['customer_id']);
                $_address->setStreet($street);
                $_address->setTelephone($address['telephone']);
                $_address->setIsDefaultShipping($default_shipping);
                $_address->setIsDefaultBilling($default_billing);

                try {
                    $_address->save();
                    if($_address->getId() != ''){
                        $addressData = $this->addressRepository->getById($_address->getId());
                        $record['address'] = $addressData->__toArray();        
                        
                        $response[] = ['status'=> '200', 'success'=>true,'message'=>'successfully save customer Address.', 'data' =>$record];
                    }else{
                        
                        $response[] = ['status'=> '400', 'success'=>false, 'message'=>'Address not saved.', 'data' =>array()];
                    }   
                } catch (Exception $e) {
                    $response[] = ['status'=> '400', 'success'=>false, 'message'=>__($e->getMessage()), 'data' =>array()];
                }
            }else{
                $response[] = ['status'=> '400', 'success'=>false, 'message'=>__('Customer not exist!'), 'data' =>array()];
            }
        }else{
           $response[] = ['status'=> '400', 'success'=>false, 'message'=>'Please Enter Customer Id For save Address !!', 'data' =>array()]; 
        }
        
        return $response;
    }
    
	/**
     * Returns 
     * @api
     * @param int $customer_id
     * @return array
     */
    
    public function getCustomerAddress($customer_id){
        
        $customer = $this->customerFactory->create();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customer->setWebsiteId($websiteId);
        $customerModel = $customer->load($customer_id);        
        $defaultBillingID =  $customerModel->getDefaultBilling();
        $defaultShippingID =  $customerModel->getDefaultShipping();

        $customerAddress = [];
        if ($customerModel->getAddresses() != null)
        {
            $a = 0;
            foreach ($customerModel->getAddresses() as $address) {
                $countryCode = ($address->getCountryId() !='') ? $address->getCountryId() : ''; 
                $country = $this->_countryFactory->create()->load($countryCode)->getName();
                
                $customerAddress[$a]['entity_id'] = ($address->getEntityId() !='') ? $address->getEntityId() : '';
                $customerAddress[$a]['is_active'] = ($address->getIsActive() !='') ? $address->getIsActive() : '';
                $customerAddress[$a]['firstname'] = ($address->getFirstname() !='') ? $address->getFirstname() : '';
                $customerAddress[$a]['lastname'] = ($address->getLastname() !='') ? $address->getLastname() : '';
                $customerAddress[$a]['middlename'] = ($address->getMiddlename() !='') ? $address->getMiddlename() : '';
                $customerAddress[$a]['company'] = ($address->getCompany() !='') ? $address->getCompany() : '';
                $customerAddress[$a]['street'] = ($address->getStreet() !='') ? $address->getStreet() : '';
                $customerAddress[$a]['country_id'] = ($address->getCountryId() !='') ? $address->getCountryId() : '';
                $customerAddress[$a]['region'] = ($address->getRegion() !='') ? $address->getRegion() : '';
                $customerAddress[$a]['region_id'] = ($address->getRegion() !='') ? $address->getRegionId() : '';
                $customerAddress[$a]['region_code'] = ($address->getRegionCode() !='') ? $address->getRegionCode() : '';
                $customerAddress[$a]['city'] = ($address->getCity() !='') ? $address->getCity() : '';
                $customerAddress[$a]['postcode'] = ($address->getPostcode() !='') ? $address->getPostcode() : '';
                $customerAddress[$a]['telephone'] = ($address->getTelephone() !='') ? $address->getTelephone() : ''; 
                $customerAddress[$a]['country_name'] = $country;

                $customerAddress[$a]['default_shipping'] = false;
                $customerAddress[$a]['default_billing'] = false;
                if($defaultBillingID==$address->getEntityId())
                {
                    $customerAddress[$a]['default_billing'] = true;
                }
                if($defaultShippingID==$address->getEntityId())
                {
                    $customerAddress[$a]['default_shipping'] = true;
                }
                $a++;
            }
        }
        
        $record['customer_id'] = $customer_id;
        $record['addresses'] = $customerAddress;
        $response[] = ['status'=> '200','message'=>'customer address load successfully', 'data' =>$record];
        return $response;
    }

    public function getRegionId($regionCode, $countryCode){

        $region = $this->regionFactory->create();
        $regionId = $region->loadByCode($regionCode, $countryCode)->getId();
        return $regionId;
    }

    public function getAllowedCountries(){
        
        $countries = [];
        /* @var Country $country */
        foreach ($this->directoryHelper->getCountryCollection() as $country) {
            $countries[] = $country->getId();
                
        }
        return $countries;
    }
}
