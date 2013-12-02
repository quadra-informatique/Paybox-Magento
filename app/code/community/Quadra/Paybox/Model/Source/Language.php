<?php

/**
 * 1997-2013 Quadra Informatique
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is available
 * through the world-wide-web at this URL: http://www.opensource.org/licenses/OSL-3.0
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to modules@quadra-informatique.fr so we can send you a copy immediately.
 *
 * @author Quadra Informatique <modules@quadra-informatique.fr>
 * @copyright 1997-2013 Quadra Informatique
 * @license http://www.opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
class Quadra_Paybox_Model_Source_Language
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'FRA', 'label' => Mage::helper('paybox')->__('FRA (French)')),
            array('value' => 'GBR', 'label' => Mage::helper('paybox')->__('GBR (English)')),
            array('value' => 'ESP', 'label' => Mage::helper('paybox')->__('ESP (Spanish)')),
            array('value' => 'ITA', 'label' => Mage::helper('paybox')->__('ITA (Italian)')),
            array('value' => 'DEU', 'label' => Mage::helper('paybox')->__('DEU (German)')),
            array('value' => 'NLD', 'label' => Mage::helper('paybox')->__('NLD (Dutch)')),
            array('value' => 'SWE', 'label' => Mage::helper('paybox')->__('SWE (Swedish)')),
        );
    }

}
