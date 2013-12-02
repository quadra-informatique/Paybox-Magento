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
class Quadra_Paybox_Block_System_Redirect extends Mage_Core_Block_Abstract
{

    protected function _toHtml()
    {
        $system = $this->getOrder()->getPayment()->getMethodInstance();

        $form = new Varien_Data_Form();
        $form->setAction(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $system->getPayboxFile())
                ->setId('paybox_system_checkout')
                ->setName('paybox_system_checkout')
                ->setMethod('POST')
                ->setUseContainer(true);
        foreach ($system->getFormFields() as $field => $value) {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }
        $html = '<html><body>';
        $html.= $this->__('You will be redirected to Paybox in a few seconds.');
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("paybox_system_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }

}
