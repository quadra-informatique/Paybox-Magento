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
class Quadra_Paybox_Adminhtml_Api_DebugController extends Mage_Adminhtml_Controller_Action
{

    protected function _initPaybox($idFieldName = 'id')
    {
        $this->_title($this->__('Paybox'))->_title($this->__('Api Debug'));

        $debugId = (int) $this->getRequest()->getParam($idFieldName);
        $debug = Mage::getModel('paybox/api_debug');

        if ($debugId) {
            $debug->load($debugId);
        }

        Mage::register('current_debug', $debug);
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Paybox'))->_title($this->__('Api Debug'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->loadLayout();

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('paybox/api_debug');

        /**
         * Append debugs block to content
         */
        $this->_addContent(
                $this->getLayout()->createBlock('paybox/adminhtml_api_debug', 'paybox_api_debug')
        );

        /**
         * Add breadcrumb item
         */
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Paybox'), Mage::helper('adminhtml')->__('Paybox'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Api Debug'), Mage::helper('adminhtml')->__('Api Debug'));

        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('paybox/adminhtml_api_debug_grid')->toHtml()
        );
    }

    /**
     * Paybox api debug view action
     */
    public function viewAction()
    {
        $this->loadLayout();
        $this->_initPaybox('id');

        $this->_setActiveMenu('paybox/api_debug');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Paybox'), Mage::helper('adminhtml')->__('Paybox'), $this->getUrl('*/*'));

        if ($this->getRequest()->getParam('id')) {
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit Paybox'), Mage::helper('adminhtml')->__('Edit Paybox'));
        }

        $this->_addContent($this->getLayout()->createBlock('paybox/adminhtml_api_debug_view', 'paybox_api_debug_view'))
                ->_addLeft($this->getLayout()->createBlock('paybox/adminhtml_api_debug_view_tabs'));

        $this->renderLayout();
    }

}
