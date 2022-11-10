<?php

namespace Drc\Mobileapp\Model\Currency;

use Drc\Mobileapp\Api\Currency\WebApiCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Locale\CurrencyInterface;

class WebApiCurrency implements WebApiCurrencyInterface
{
    protected $storeManager;

    protected $localeCurrency;
    
    protected $currency;

    protected $modelCurrency;

    /**
     * change currency
     *
     * @return void
     */

     /**
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     */
    public function __construct(
        \Magento\Directory\Block\Currency $currency,
        StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $modelCurrency,
        CurrencyInterface $localeCurrency
    ) {
        $this->storeManager = $storeManager;    
        $this->localeCurrency = $localeCurrency;
        $this->modelCurrency = $modelCurrency;
        $this->_currency = $currency;
    }
    public function change($code) 
    {
        $currency = (string)$code;
        $response = array();
        try {
            if ($currency) {
                $this->storeManager->getStore()->setCurrentCurrencyCode($currency);
                $currenct_code = $this->storeManager->getStore()->getCurrentCurrencyCode();
                $response[] = ['success'=>true,'code'=>$currenct_code,'message'=>__('Currency changed successfully.')];
            }else{
                $currenct_code = $storeManager->getStore()->getCurrentCurrencyCode();
                $response[] = ['success'=>false,'code'=>$currenct_code,'message'=>__('Currency not changed.')];
            }
        } catch (Exception $e) {
            $currenct_code = $this->storeManager->getStore()->getCurrentCurrencyCode();
            $response[] = ['success'=>false,'code'=>$currenct_code,'message'=>__($e->getMessage())];
        }
        return $response;
    }
    /**
     * get currency
     *
     * @return void
     */
    public function getCurrencyList() 
    {
        $currencies = $this->_currency->getCurrencies();
        $currencyData = [];
        foreach ($currencies as $code => $currencyName) {
            $currencyData[] =[
                    "code"=> $code,
                    "name" => $currencyName
            ];
        }
        $currencyDatanew = ['0' =>['status'=> '200','message'=>'currency loading successfully.', 'data' =>$currencyData]];        
        return $currencyDatanew;  
    }  
    public function get() 
    {   
        $currenct_code = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $current_currency = $this->localeCurrency->getCurrency($currenct_code)->getName();
        $current_currencyData = [];
        $current_currencyData = [
                "code" => $currenct_code,
                "name" => $current_currency  
        ];
        $currenctDatanew = ['0' =>['status'=> '200','message'=>'currency loading successfully.', 'data' =>$current_currencyData]];        
        return $currenctDatanew;
    }
}