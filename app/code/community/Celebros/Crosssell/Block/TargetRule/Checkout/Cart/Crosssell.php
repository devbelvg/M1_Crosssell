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
class Celebros_Crosssell_Block_TargetRule_Checkout_Cart_Crosssell extends Enterprise_TargetRule_Block_Checkout_Cart_Crosssell
{
	/**
     * Items quantity will be capped to this value
     *
     * @var int
     */
    protected $_maxItemCount = 4;
	
	/**
	 * Get crosssell items
	 *
	 * @return array
	 */
	public function getItemCollection()
	{
		if (!Mage::getStoreConfigFlag('celebros_crosssell/crosssell_settings/crosssell_enabled')) {
			return parent::getItemCollection();
		}
		
        if (is_null($this->_items)) {
            $cartProductIds = $this->_getCartProductIds();
            $lastAdded = null;
            for ($i = count($cartProductIds)-1; $i >=0 ; $i--) {
                $id =  $cartProductIds[$i];
                $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($id);
                if (empty($parentIds)) {
                    $lastAdded = $id;
                    break;
                }
            }
                
            $productSku = Mage::getModel('catalog/product')->load($lastAdded)->getSku();
            $crossSellSkus = Mage::helper('celebros_crosssell')->getSalespersonCrossSellApi()->getRecommendationsIds($productSku);
            $this->_maxItemCount = Mage::getStoreConfig('celebros_crosssell/crosssell_settings/crosssell_limit');
            $this->_items = $this->_getCollection()
                ->addFieldToFilter('sku', array('in' => $crossSellSkus));
        }

		return $this->_items;
	}

	/**
	 * Get crosssell products collection
	*/
	protected function _getCollection()
	{
		$collection = Mage::getModel('catalog/product')
			->getCollection()
			->setStoreId(Mage::app()->getStore()->getId())
			->addStoreFilter()
			->setPageSize($this->_maxItemCount);
		$this->_addProductAttributesAndPrices($collection);
	
		Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
		Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
	
		return $collection;
	}

}