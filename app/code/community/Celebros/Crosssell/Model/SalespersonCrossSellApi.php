<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality.
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_Crosssell
 */
class Celebros_Crosssell_Model_SalespersonCrossSellApi extends Mage_Core_Model_Abstract
{
    
    protected $_serverAddress;
    
    protected $_siteKey;
    
    protected $_requestHandle;
    protected $_simulateResults = false;  // Simulate results from the product's upsell list in Magento
    
    /**
     * Init resource model
     *
     */
    protected function _construct()
    {
        $this->_init('celebros_crosssell/SalespersonCrossSellApi');
        if (Mage::getStoreConfig('celebros_crosssell/crosssell_settings/crosssell_customer_name') != '' && Mage::getStoreConfig('celebros_crosssell/crosssell_settings/crosssell_address') != '' && Mage::getStoreConfig('celebros_crosssell/crosssell_settings/crosssell_request_handle') != ''){
            $this->_serverAddress = Mage::getStoreConfig('celebros_crosssell/crosssell_settings/crosssell_address');
            if (preg_match('/http:\/\//',$this->_serverAddress)){
                $this->_serverAddress = preg_replace('/http::\/\//','', $this->_serverAddress);
            }
            $this->_siteKey = Mage::getStoreConfig('celebros_crosssell/crosssell_settings/crosssell_customer_name');
            $this->_requestHandle = Mage::getStoreConfig('celebros_crosssell/crosssell_settings/crosssell_request_handle');
        }
    }
    
    public function getRecommendationsIds($id)
    {
        $arrIds = array();
        
        if ($this->_simulateResults) {
            Mage::log('Simulating upsell/crosssel results from product\'s upsell data', null, 'celebros.log',true);
            $product = Mage::getModel('catalog/product')->load($id);
            $upsell_products = $product->getUpSellProductCollection()->addAttributeToSort('position', Varien_Db_Select::SQL_ASC)->addStoreFilter();
            foreach ($upsell_products as $upsell) {
                $arrIds[] = $upsell->getId();
            }
            return $arrIds;
        }
       
        $url = "https://{$this->_serverAddress}/JsonEndPoint/ProductsRecommendation.aspx?siteKey={$this->_siteKey}&RequestHandle={$this->_requestHandle}&RequestType=1&SKU={$id}&Encoding=utf-8";
        $jsonData =  $this->_get_data($url);
        $obj = json_decode($jsonData);
        for ($i=0; isset($obj->Items) && $i < count($obj->Items); $i++) {
            $arrIds[(int)$obj->Items[$i]->Fields->Rank] = $obj->Items[$i]->Fields->SKU;
        }
        
        ksort($arrIds);        
        
        return $arrIds; 
    }
    
    protected function _get_data($url)
    {
        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(array(
            'timeout' => 15
        ));

        $curl->write(Zend_Http_Client::GET, $url, '1.0');
        $response = $curl->read();
       
        $code = Zend_Http_Response::extractCode($response);
        if ($code == '200') {
            $response = preg_split('/^\\r?$/m', $response, 2);
            $response = trim($response[1]);
        } else {
            $response = null;
        }

        $curl->close();
        
        return $response;
    }
}