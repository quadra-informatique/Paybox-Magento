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
class Quadra_Paybox_Model_Source_PaymentType
{

    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => Mage::helper('paybox')->__('--Please Select--')),
            array('value' => Quadra_Paybox_Model_System::PBX_PAYMENT_TYPE_CARTE, 'label' => Mage::helper('paybox')->__('CARTE')),
            array('value' => Quadra_Paybox_Model_System::PBX_PAYMENT_TYPE_SYMPASS, 'label' => Mage::helper('paybox')->__('SYMPASS')),
            array('value' => Quadra_Paybox_Model_System::PBX_PAYMENT_TYPE_PAYNOVA, 'label' => Mage::helper('paybox')->__('PAYNOVA')),
            array('value' => Quadra_Paybox_Model_System::PBX_PAYMENT_TYPE_TERMINEO, 'label' => Mage::helper('paybox')->__('TERMINEO')),
            array('value' => Quadra_Paybox_Model_System::PBX_PAYMENT_TYPE_PAYPAL, 'label' => Mage::helper('paybox')->__('PAYPAL')),
            array('value' => Quadra_Paybox_Model_System::PBX_PAYMENT_TYPE_PREPAYEE, 'label' => Mage::helper('paybox')->__('PREPAYEE'))
        );
    }

}