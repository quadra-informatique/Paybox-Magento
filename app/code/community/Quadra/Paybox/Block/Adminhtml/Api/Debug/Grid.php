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
class Quadra_Paybox_Block_Adminhtml_Api_Debug_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('paybox_api_debug_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('debug_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('paybox/api_debug_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('debug_at', array(
            'header' => Mage::helper('paybox')->__('Debug at'),
            'width' => '50px',
            'index' => 'debug_at',
            'type' => 'datetime'
        ));

        $this->addColumn('real_order_id', array(
            'header' => Mage::helper('paybox')->__('Real Order ID'),
            'width' => '50px',
            'index' => 'real_order_id'
        ));

        $this->addColumn('request_body', array(
            'header' => Mage::helper('paybox')->__('Request'),
            'index' => 'request_body'
        ));

        $this->addColumn('response_body', array(
            'header' => Mage::helper('paybox')->__('Response'),
            'index' => 'response_body'
        ));

        $this->addColumn('pbx_response_body', array(
            'header' => Mage::helper('paybox')->__('Paybox Server Response'),
            'index' => 'pbx_response_body'
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('paybox')->__('Action'),
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('paybox')->__('View'),
                    'url' => array('base' => '*/*/view'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }

}
