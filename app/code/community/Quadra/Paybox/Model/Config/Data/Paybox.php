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
class Quadra_Paybox_Model_Config_Data_Paybox extends Mage_Core_Model_Config_Data
{

    public function _beforeSave()
    {
        $filename = BP . DS . 'app' . DS . 'code' . DS . 'core' . DS . 'Mage' . DS . 'Paybox' . DS . 'etc' . DS . 'config.xml';

        if (file_exists($filename)) {
            Mage::getSingleton('adminhtml/session')->addError('Warning: the module Mage_Paybox must be uninstalled.');
        }

        return $this;
    }

}
