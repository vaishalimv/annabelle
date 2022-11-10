<?php
namespace Drc\Mobileapp\Api\Currency;
 
interface WebApiCurrencyInterface{
	 /**
     * Returns attributes
     *
     * @param string $code
     * @return mixed
     */
    public function change($code);

    /**
     * get current currecy code
     *
     * @return string
     */
    public function get();
    
    /**
     * get currecy list
     *
     * @return string
     */
    public function getCurrencyList();
}