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
class Quadra_Paybox_Block_Adminhtml_Api_Debug_View extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'paybox';
        $this->_controller = 'adminhtml_api_debug';
        $this->_mode = 'view';

        $this->_removeButton('reset');
        $this->_removeButton('save');
        $this->_removeButton('edit');
        $this->_removeButton('delete');
    }

    public function getHeaderText()
    {
        return Mage::helper('paybox')->__('Paybox Api Debug View');
    }

}