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
class Quadra_Paybox_Block_Adminhtml_Api_Debug_View_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('paybox_api_debug_view_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Paybox'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('request_section', array(
            'label' => $this->__('Request'),
            'title' => $this->__('Request'),
            'content' => $this->getLayout()->createBlock('paybox/adminhtml_api_debug_view_tab_request')->toHtml()
        ));
        $this->addTab('response_section', array(
            'label' => $this->__('Response'),
            'title' => $this->__('Response'),
            'content' => $this->getLayout()->createBlock('paybox/adminhtml_api_debug_view_tab_response')->toHtml()
        ));
        $this->addTab('responseserver_section', array(
            'label' => $this->__('Server Response'),
            'title' => $this->__('Server Response'),
            'content' => $this->getLayout()->createBlock('paybox/adminhtml_api_debug_view_tab_responseServer')->toHtml()
        ));
        return parent::_beforeToHtml();
    }

}