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
class Quadra_Paybox_Model_Source_CartType
{

    /**
     * Enter description here...
     *
     * @return Quadra_Paybox_Model_System
     */
    public function getModel()
    {
        return Mage::getModel('paybox/system');
    }

    public function toOptionArray()
    {
        $cartTypesArr = array();
        $tmpArr = $this->getModel()->getCartTypesByPayment($this->getModel()->getPaymentType());

        foreach ($tmpArr as $code => $name) {
            $cartTypesArr[] = array(
                'value' => $code,
                'label' => $name
            );
        }

        return $cartTypesArr;
    }

}