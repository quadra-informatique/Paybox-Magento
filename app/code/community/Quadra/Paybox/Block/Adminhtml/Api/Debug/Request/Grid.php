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
class Quadra_Paybox_Block_Adminhtml_Api_Debug_Request_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('paybox_api_debug_request_grid');
        $this->_filterVisibility = false;
        $this->_pagerVisibility = false;
    }

    protected function _prepareGrid()
    {
        $this->_prepareCollection();
        $this->_prepareColumns();
        $this->_prepareMassactionBlock();
        return $this;
    }

    protected function _prepareCollection()
    {
        $this->_collection = new Varien_Data_Collection();
        $this->_loadCollection();
        $this->setCollection($this->_collection);
        return parent::_prepareCollection();
    }

    protected function _loadCollection()
    {
        $collection = Mage::getResourceModel('paybox/api_debug_collection')
                ->addFieldToFilter('debug_id', $this->getRequest()->getParam('id'));
        $object = new Varien_Object();
        foreach ($collection->getItems() as $item) {
            $item = $item->getData();
            if ($item['real_order_id'] > 0) {
                // Paybox System
                foreach (unserialize($item['request_body']) as $key => $value)
                    $object->setData($key, $value);
            } else {
                // Paybox Direct
                $params = explode('&', $item['request_body']);
                foreach ($params as $p) {
                    $tmp = explode('=', $p);
                    $object->setData($tmp[0], $tmp[1]);
                }
            }

            $this->_collection->addItem($object);
        }
    }

    protected function _prepareColumns()
    {
        foreach ($this->getCollection()->getItems() as $item) {
            foreach (array_keys($item->getData()) as $key) {
                $this->addColumn($key, array(
                    'header' => Mage::helper('paybox')->__($key),
                    'index' => $key
                ));
            }
        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        return $this;
    }

    public function getGridUrl()
    {
        return false;
    }

    public function getRowUrl($row)
    {
        return false;
    }

}