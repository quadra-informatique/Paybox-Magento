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
class Quadra_Paybox_Block_Adminhtml_Question_Number_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('paybox_question_number_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('account_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('paybox/question_number_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('account_id', array(
            'header' => Mage::helper('paybox')->__('Account ID'),
            'width' => '50px',
            'index' => 'account_id'
        ));

        $this->addColumn('account_hash', array(
            'header' => Mage::helper('paybox')->__('Account Hash'),
            'index' => 'account_hash'
        ));

        $this->addColumn('increment_value', array(
            'header' => Mage::helper('paybox')->__('Increment Value'),
            'width' => '50px',
            'index' => 'increment_value'
        ));

        $this->addColumn('reset_date', array(
            'header' => Mage::helper('paybox')->__('Reset Date'),
            'width' => '50px',
            'index' => 'reset_date',
            'type' => 'datetime'
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
        return false;
    }

}
