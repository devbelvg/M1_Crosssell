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
class Celebros_Crosssell_Block_Checkout_Cart_Crosssell extends Mage_Checkout_Block_Cart_Crosssell
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
    public function getItems()
    {
        if (!Mage::getStoreConfigFlag('celebros_crosssell/crosssell_settings/crosssell_enabled')) {
            return parent::getItems();
        }
        
        $items = $this->getData('items');
        if (is_null($items)) {
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
            $items = $this->_getCollection()
                ->addFieldToFilter('sku', array('in' => $crossSellSkus));
        }
        
        $this->setData('items', $items);
        $this->_itemCollection = $items;
        return $items;
    }
}