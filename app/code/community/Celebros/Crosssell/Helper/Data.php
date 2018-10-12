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
class Celebros_Crosssell_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getSalespersonCrossSellApi()
    {
        return Mage::getModel('celebros_crosssell/salespersonCrossSellApi');
    }
}