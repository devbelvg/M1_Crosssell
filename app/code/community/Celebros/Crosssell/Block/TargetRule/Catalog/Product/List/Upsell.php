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
class Celebros_Crosssell_Block_TargetRule_Catalog_Product_List_Upsell extends Enterprise_TargetRule_Block_Catalog_Product_List_Upsell
{
    protected $_maxItemCount = 4;
    
    protected $_items;
    
    protected $_itemCollection;
    
    /**
     * Get crosssell items
     *
     * @return array
     */
    public function getItemCollection()
    {
        if (!Mage::getStoreConfigFlag('celebros_crosssell/crosssell_settings/upsell_enabled')) {
            return parent::getItemCollection();
        }
        
        if (empty($this->_itemCollection)) {
            $productSku = null;
            if ($this->getProduct() != null) {
                $productSku = $this->getProduct()->getSku();
                $upSellSkus = Mage::helper('celebros_crosssell')->getSalespersonCrossSellApi()->getRecommendationsIds($productSku);
                if (!empty($upSellSkus)) {
                    $this->_maxItemCount = Mage::getStoreConfig('celebros_crosssell/crosssell_settings/upsell_limit');
                    $this->_itemCollection = $this->_getCollection()->addFieldToFilter('sku', array('in' => $upSellSkus));
                    $this->_itemCollection->getSelect()->order("FIELD('sku', '" . implode("','", $upSellSkus) . "') ASC");
                } else {
                    $this->_itemCollection = new Varien_Data_Collection();
                }
            }
        }
       
        return $this->_itemCollection;
    }
	
	public function hasItems()
	{
		return (count($this->getItemCollection()));
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
