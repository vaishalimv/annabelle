<?php
namespace Drc\Mobileapp\Model\Customer;
use Drc\Mobileapp\Api\Customer\CustomAddressInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Directory\Model\RegionFactory;

class Address implements CustomAddressInterface{   
    protected $_storeManager;
    protected $addressFactory;
    protected $addressRepository;
    protected $customerRepository;
    protected $_customer;
    protected $directoryHelper;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Customer $customer,
        DirectoryHelper $directoryHelper,
        RegionFactory $regionFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_addressFactory = $addressFactory;
        $this->addressRepository = $addressRepository;
        $this->customerRepository = $customerRepository;
        $this->_customer = $customer;
        $this->directoryHelper = $directoryHelper;
        $this->regionFactory = $regionFactory;
    }

    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function name($name) {
        return "Hello, " . $name;
    }

    public function addressUpdate($params) {
        $addressId = $params['id'];    
        $customerId = $params['customer_id'];
        
        $allow_countries = $this->getAllowedCountries();
        if(!in_array($params['country_id'], $allow_countries)){
             $response[] = ['status'=> '400', 'success'=>false, 'message'=>'Given country_id is not active.']; 
            return $response;
        }    

        if(empty($customerId)){
            $response[] = ['status'=> '400','success'=>false,'message'=>__('Please specify customer_id')];
            return $response;
        }else{  
            $customer  =  $this->_customer->load($params['customer_id']);
            if($customer->getId()){

                $firstname = $params['firstname'];
                $lastname = $params['lastname'];
                $street = $params['street'];                 
                $city = $params['city'];   
                $country = $params['country_id'];
                $postcode = $params['postcode'];
                $telephone = $params['telephone'];
                $default_shipping = "0";
                $default_billing = "0";
                if(isset($params['default_shipping']) && !empty($params['default_shipping'])){                
                    $default_shipping = ($params['default_shipping'] == true) ? "1" : "0";
                }
                if(isset($params['default_billing']) && !empty($params['default_billing'])){
                    $default_billing = ($params['default_billing'] == true) ? "1" : "0";
                }
                try{
                    if(isset($addressId) && $addressId !=''){
                        $address = $this->_addressFactory->create()->load($addressId);

                        $address->setCustomerId($customerId);
                        $address->setFirstname($firstname);
                        $address->setLastname($lastname);
                        $address->setStreet($street);
                        if(isset($params['region_id']) && !empty($params['region_id'])){
                            $address->setRegionId($params['region_id']);
                        } else {
                            $address->setRegion($params['region']);
                        }
                        $address->setCountryId($country);
                        $address->setPostcode($postcode);
                        $address->setCity($city);
                        $address->setTelephone($telephone);
                        $address->setIsDefaultShipping($default_shipping);
                        $address->setIsDefaultBilling($default_billing);
                        $address->save();

                        $response[] = ['status'=> '200','success'=>true,'message'=>__("Address updated successfully.")];
                        return $response;
                    }else{
                        $response[] = ['status'=> '400','success'=>false,'message'=>__('Please specify address_id')];
                        return $response;
                    }                 
                }catch(\Exception $e){
                    $response[] = ['status'=> '400','success'=>false,'message'=>__($e->getMessage())];
                    return $response;
                }
            }else{
                $response[] = ['status'=> '400','success'=>false,'message'=>__('Customer not exists!')];
                    return $response;
            } 
        }
    }

    public function getRegionId($regionCode, $countryCode)
    {
        $region = $this->regionFactory->create();
        $regionId = $region->loadByCode($regionCode, $countryCode)->getId();
        return $regionId;
    }

    public function getAllowedCountries()
    {
        $countries = [];
        /* @var Country $country */
        foreach ($this->directoryHelper->getCountryCollection() as $country) {
            $countries[] = $country->getId();
        }
        return $countries;
    }
}