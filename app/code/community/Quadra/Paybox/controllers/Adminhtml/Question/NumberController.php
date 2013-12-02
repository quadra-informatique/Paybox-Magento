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
class Quadra_Paybox_Adminhtml_Question_NumberController extends Mage_Adminhtml_Controller_Action
{

    protected function _initPaybox($idFieldName = 'id')
    {
        $this->_title($this->__('Paybox'))->_title($this->__('Question Number'));

        $numberId = (int) $this->getRequest()->getParam($idFieldName);
        $number = Mage::getModel('paybox/question_number');

        if ($numberId) {
            $number->load($numberId);
        }

        Mage::register('current_number', $number);
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Paybox'))->_title($this->__('Question Number'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->loadLayout();

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('paybox/question_number');

        /**
         * Append numbers block to content
         */
        $this->_addContent(
                $this->getLayout()->createBlock('paybox/adminhtml_question_number', 'paybox_question_number')
        );

        /**
         * Add breadcrumb item
         */
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Paybox'), Mage::helper('adminhtml')->__('Paybox'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Question Number'), Mage::helper('adminhtml')->__('Question Number'));

        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('paybox/adminhtml_question_number_grid')->toHtml()
        );
    }

}
