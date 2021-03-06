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
class Celebros_Crosssell_Block_Catalog_Product_List_Upsell extends Mage_Catalog_Block_Product_List_Upsell
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
        
        $items = $this->_items;
        if (is_null($items)) {
            reset($this->_itemCollection);
            $productSku = null;
            if ($this->getProduct() != null) {
                $productSku = $this->getProduct()->getSku();
                $upSellSkus = Mage::helper('celebros_crosssell')->getSalespersonCrossSellApi()->getRecommendationsIds($productSku);
                $this->_maxItemCount = Mage::getStoreConfig('celebros_crosssell/crosssell_settings/upsell_limit');
                $this->_itemCollection = $this->_getCollection()->addFieldToFilter('sku', array('in' => $upSellSkus));
            }
        }
        
        return $this->_itemCollection;
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
