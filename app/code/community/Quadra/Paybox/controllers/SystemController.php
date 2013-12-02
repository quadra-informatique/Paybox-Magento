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
class Quadra_Paybox_SystemController extends Mage_Core_Controller_Front_Action
{

    protected $_payboxResponse = null;
    protected $_responseStatus = false;
    protected $_realOrderIds;
    protected $_quote;

    public function testAction()
    {
        $model = Mage::getModel('paybox/direct')->setRang(10)->setSiteNumber(999988);
        echo "test " . $model->getQuestionNumberModel()->getNextQuestionNumber();

        $model->getQuestionNumberModel()
                ->increaseQuestionNumber();
    }

    /**
     * Get quote model
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = Mage::getModel('sales/quote')->load($this->getCheckout()->getPayboxQuoteId());

            if (!$this->_quote->getId()) {
                $realOrderIds = $this->getRealOrderIds();
                if (count($realOrderIds)) {
                    $order = Mage::getModel('sales/order')->loadByIncrementId($realOrderIds[0]);
                    $this->_quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
                }
            }
        }
        return $this->_quote;
    }

    /**
     * Get real order ids
     *
     * @return array
     */
    public function getRealOrderIds()
    {
        if (!$this->_realOrderIds) {
            if ($this->_payboxResponse) {
                $this->_realOrderIds = explode(',', $this->_payboxResponse['ref']);
            } else {
                return array();
            }
        }
        return $this->_realOrderIds;
    }

    public function getBaseGrandTotal()
    {
        if ($this->getQuote()->getIsMultiShipping())
            return $this->getQuote()->getBaseGrandTotal();
        else {
            $realOrderIds = $this->getRealOrderIds();
            if (count($realOrderIds)) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($realOrderIds[0]);
                return $order->getBaseGrandTotal();
            } else {
                return 0;
            }
        }
    }

    /**
     * seting response after returning from paybox
     *
     * @param array $response
     * @return object $this
     */
    protected function setPayboxResponse($response)
    {
        if (count($response)) {
            $this->_payboxResponse = $response;
        }
        return $this;
    }

    /**
     * Get System Model
     *
     * @return Quadra_Paybox_Model_System
     */
    public function getModel()
    {
        return Mage::getSingleton('paybox/system');
    }

    /**
     * Get Checkout Singleton
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Redirect action. Redirect customer to Paybox
     *
     */
    public function redirectAction()
    {
        $session = $this->getCheckout();
        $session->setPayboxQuoteId($session->getLastQuoteId());

        if ($this->getQuote()->getIsMultiShipping())
            $realOrderIds = explode(',', $session->getRealOrderIds());
        else
            $realOrderIds = array($session->getLastRealOrderId());

        foreach ($realOrderIds as $realOrderId) {
            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($realOrderId);
            $order->addStatusToHistory($order->getStatus(), $this->__('The customer was redirected to Paybox'));
            $order->save();
        }

        $session->setPayboxOrderId(Mage::helper('core')->encrypt(implode(',', $realOrderIds)));
        $session->setPayboxPaymentAction(
                $order->getPayment()
                        ->getMethodInstance()
                        ->getPaymentAction()
        );

        $this->getResponse()->setBody(
                $this->getLayout()
                        ->createBlock('paybox/system_redirect')
                        ->setOrder($order)
                        ->toHtml()
        );

        $session->unsQuoteId();
    }

    /**
     * Customer returning to this action if payment was successe
     */
    public function successAction()
    {
        $model = $this->getModel();
        $this->setPayboxResponse($this->getRequest()->getParams());

        if ($this->_checkResponse()) {
            if (Mage::helper('core')->decrypt($this->getCheckout()->getPayboxOrderId()) != $this->_payboxResponse['ref']) {
                Mage::throwException($this->__('Order is not match.'));
            }
            $this->getCheckout()->unsPayboxOrderId();

            if ((int) ($this->getBaseGrandTotal() * 100) != (int) $this->_payboxResponse['amount']) {
                $erreur = $this->__('Amount is not match.');
                $erreur .= (int) ($model->getOrder()->getBaseGrandTotal() * 100) . ' != ' . $this->_payboxResponse['amount'];
            }

            if ($this->getQuote()->getIsMultiShipping())
                $orderIds = array();

            foreach ($this->getRealOrderIds() as $realOrderId) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($realOrderId);

                if (!$order->getId()) {
                    Mage::throwException($this->__('There are no order.'));
                }

                if (isset($erreur)) {
                    $order->addStatusToHistory($order->getStatus(), $erreur);
                    $order->save();
                } else {
                    $order->addStatusToHistory($order->getStatus(), $this->__('Customer successfully returned from Paybox'));
                    $order->save();
                }

                if ($this->getQuote()->getIsMultiShipping())
                    $orderIds[$order->getId()] = $realOrderId;
            }

            if (isset($erreur)) {
                Mage::throwException($this->__('Amount is not match.'));
            }

            if ($this->getQuote()->getIsMultiShipping()) {
                Mage::getSingleton('checkout/type_multishipping')
                        ->getCheckoutSession()
                        ->setDisplaySuccess(true)
                        ->setPayboxResponseCode('success');

                Mage::getSingleton('core/session')->setOrderIds($orderIds);
                Mage::getSingleton('checkout/session')->setIsMultishipping(true);
            }

            $session = $this->getCheckout();
            $session->setQuoteId($session->getPayboxQuoteId(true));
            $session->getQuote()->setIsActive(false)->save();
            $session->unsPayboxQuoteId();
            $session->setCanRedirect(false);

            $this->_redirect($this->_getSuccessRedirect());
        } else {
            $this->norouteAction();
            return;
        }
    }

    /**
     * Action when payment was refused by Paybox
     */
    public function refuseAction()
    {
        $model = $this->getModel();

        $this->setPayboxResponse($this->getRequest()->getParams());
        if ($this->_checkResponse()) {
            $this->getCheckout()->unsPayboxQuoteId();
            $this->getCheckout()->setPayboxErrorMessage($this->__('Order was canceled by Paybox'));

            if ($model->getConfigData('order_status_payment_refused') == Mage_Sales_Model_Order::STATE_CANCELED) {
                foreach ($this->getRealOrderIds() as $realOrderId) {
                    $order = Mage::getModel('sales/order')->loadByIncrementId($realOrderId);

                    if ($order->canUnhold()) {
                        $order->unhold();
                    }

                    if ($order->canCancel()) {
                        $order->addStatusToHistory(
                                $model->getConfigData('order_status_payment_canceled'), $this->__('The order was canceled.')
                        );
                        $order->cancel();
                        $order->save();
                    }
                }
            }

            if (!$model->getConfigData('empty_cart')) {
                $this->_reorder();
            }

            $this->_redirect('*/*/failure');
        } else {
            $this->norouteAction();
            return;
        }
    }

    /**
     * Action when customer cancels payment or press button to back to shop
     */
    public function declineAction()
    {
        $model = $this->getModel();
        $this->setPayboxResponse($this->getRequest()->getParams());

        if ($this->_checkResponse()) {

            foreach ($this->getRealOrderIds() as $realOrderId) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($realOrderId);

                $order->addStatusToHistory(
                        $model->getConfigData('order_status_payment_canceled'), $this->__('The order was canceled by the customer.')
                );

                if ($model->getConfigData('order_status_payment_canceled') == Mage_Sales_Model_Order::STATE_CANCELED && $order->canCancel()) {
                    $order->cancel();
                } else if ($model->getConfigData('order_status_payment_canceled') == Mage_Sales_Model_Order::STATE_HOLDED && $order->canHold()) {
                    $order->hold();
                }

                $order->save();
            }

            if (!$model->getConfigData('empty_cart')) {
                $this->_reorder();
            }

            $session = $this->getCheckout();
            $session->addNotice($this->__('The payment was canceled.'));

            if ($model->getConfigData('empty_cart')) {
                $session->setQuoteId($session->getPayboxQuoteId(true));
                $session->getQuote()->setIsActive(false)->save();
                $session->unsPayboxQuoteId();
            }

            $this->_redirect('checkout/cart');
        } else {
            $this->norouteAction();
            return;
        }
    }

    /**
     * Redirect action. Redirect to Paybox using commandline mode
     *
     */
    public function commandlineAction()
    {
        $session = $this->getCheckout();
        $session->setPayboxQuoteId($session->getQuoteId());

        $order = Mage::getModel('sales/order');

        if ($this->getQuote()->getIsMultiShipping())
            $orderIds = array();

        foreach ($this->getRealOrderIds() as $realOrderId) {
            $order->loadByIncrementId($realOrderId);
            $order->addStatusToHistory(
                    $order->getStatus(), $this->__('The customer was redirected to Paybox using \'command line\' mode')
            );
            $order->save();

            if ($this->getQuote()->getIsMultiShipping())
                $orderIds[$order->getId()] = $realOrderId;
        }

        if ($this->getQuote()->getIsMultiShipping()) {

            Mage::getSingleton('checkout/type_multishipping')
                    ->getCheckoutSession()
                    ->setDisplaySuccess(true)
                    ->setPayboxResponseCode('commandLine');

            Mage::getSingleton('core/session')->setOrderIds($orderIds);
            Mage::getSingleton('checkout/session')->setIsMultishipping(true);
        }


        $session->setPayboxOrderId(Mage::helper('core')->encrypt($session->getLastRealOrderId()));
        $session->setPayboxPaymentAction($order->getPayment()->getMethodInstance()->getPaymentAction());

        $session->unsQuoteId();

        $payment = $order->getPayment()->getMethodInstance();
        $fieldsArr = $payment->getFormFields();
        $paramStr = '';
        foreach ($fieldsArr as $k => $v) {
            $paramStr .= $k . '=' . $v . ' ';
        }

        $paramStr = str_replace(';', '\;', $paramStr);
        $result = shell_exec(Mage::getBaseDir() . '/' . $this->getModel()->getPayboxFile() . ' ' . $paramStr);

        if (isset($fieldsArr['PBX_PING']) && $fieldsArr['PBX_PING'] == '1') {
            $fieldsArr['PBX_PING'] = '0';
            $fieldsArr['PBX_PAYBOX'] = trim(substr($result, strpos($result, 'http')));
            $paramStr = '';
            foreach ($fieldsArr as $k => $v) {
                $paramStr .= $k . '=' . $v . ' ';
            }

            $paramStr = str_replace(';', '\;', $paramStr);
            $result = shell_exec(Mage::getBaseDir() . '/' . $this->getModel()->getPayboxFile() . ' ' . $paramStr);
        }

        $this->loadLayout(false);
        $this->getResponse()->setBody($result);
        $this->renderLayout();
    }

    /**
     * Error action. If request params to Paybox has mistakes
     *
     */
    public function errorAction()
    {
        if (!$this->getCheckout()->getPayboxQuoteId()) {
            $this->norouteAction();
            return;
        }

        $model = $this->getModel();
        if ($model->getConfigData('empty_cart')) {
            $session = $this->getCheckout();
            $session->setQuoteId($session->getPayboxQuoteId(true));
            $session->getQuote()->setIsActive(false)->save();
            $session->unsPayboxQuoteId();
        }

        if (!$this->getRequest()->getParam('NUMERR')) {
            $this->norouteAction();
            return;
        }

        $this->loadLayout();

        $this->getCheckout()
                ->setPayboxErrorNumber($this->getRequest()->getParam('NUMERR'));

        $this->renderLayout();
    }

    /**
     * Failure action.
     * Displaying information if customer was redirecting to cancel or decline actions
     *
     */
    public function failureAction()
    {
        if (!$this->getCheckout()->getPayboxErrorMessage()) {
            $this->norouteAction();
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Checking response and Paybox session variables
     *
     * @return unknown
     */
    protected function _checkResponse()
    {
        if (!$this->getCheckout()->getPayboxQuoteId()) {
            $this->norouteAction();
            return;
        }

        if (!$this->getCheckout()->getPayboxOrderId()) {
            $this->norouteAction();
            return;
        }

        if (!$this->getCheckout()->getPayboxPaymentAction()) {
            $this->norouteAction();
            return;
        }

        if (!$this->_payboxResponse) {
            return false;
        }

        //check for valid response
        if ($this->getModel()->checkResponse($this->_payboxResponse)) {
            return true;
        }

        return true;
    }

    /**
     * Creating invoice
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    protected function _createInvoice(Mage_Sales_Model_Order $order)
    {
        if ($order->canInvoice()) {
            $invoice = $order->prepareInvoice();
            $invoice->register()->capture();
            Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();
            return true;
        }
        return false;
    }

    protected function _getSuccessRedirect()
    {
        if ($this->getQuote()->getIsMultiShipping())
            return 'checkout/multishipping/success';
        else
            return 'checkout/onepage/success';
    }

    /**
     *  Paybox response router
     *
     *  @param    none
     *  @return	  void
     */
    public function notifyAction()
    {
        $model = $this->getModel();
        $params = $this->getRequest()->getParams();
        $this->setPayboxResponse($params);

        if ($model->getDebugFlag()) {
            Mage::getSingleton('paybox/api_debug')
                    ->load($params['ref'], 'real_order_id')
                    ->setPbxResponseBody(serialize($params))
                    ->save();
        }

        // Vérification des adresses IP des serveurs Paybox
        $unauthorized_server = false;
        if ($model->getConfigData('pbx_check_ip')) {
            $authorized_ips = $model->getAuthorizedIps();
            $unauthorized_server = true;
            foreach ($authorized_ips as $authorized_ip) {
                if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && $_SERVER['HTTP_X_FORWARDED_FOR'] == $authorized_ip) {
                    $unauthorized_server = false;
                    break;
                }
                if ($_SERVER['REMOTE_ADDR'] == $authorized_ip) {
                    $unauthorized_server = false;
                    break;
                }
            }
        }

        if (!$unauthorized_server) {
            try {
                foreach ($this->getRealOrderIds() as $realOrderId) {
                    $order = Mage::getModel('sales/order')->loadByIncrementId($realOrderId);

                    if (!$order->getId()) {
                        Mage::throwException($this->__('There are no order.'));
                    }

                    if ((int) ($this->getBaseGrandTotal() * 100) != (int) $this->_payboxResponse['amount']) {
                        Mage::throwException($this->__('Amount is not match.'));
                    }
                }

                if ($this->_payboxResponse['error'] == '00000') {
                    // Aucune erreur = paiement paybox accepté
                    foreach ($this->getRealOrderIds() as $realOrderId) {
                        $order = Mage::getModel('sales/order')->loadByIncrementId($realOrderId);
                        $this->_updateOrderState($order, $model);
                    }

                    $session = $this->getCheckout();
                    $session->setQuoteId($session->getPayboxQuoteId(true));
                    $session->getQuote()->setIsActive(false)->save();
                    $session->unsPayboxQuoteId();

                    // On exit car en cas de réponse valide une page blanche doit être retournée
                    exit();
                } else {
                    foreach ($this->getRealOrderIds() as $realOrderId) {
                        $order = Mage::getModel('sales/order')->loadByIncrementId($realOrderId);
                        $this->_updateOrderState($order, $model);
                    }
                }
            } catch (Exception $e) {
                foreach ($this->getRealOrderIds() as $realOrderId) {
                    $order = Mage::getModel('sales/order')->loadByIncrementId($realOrderId);
                    $order->addStatusToHistory($order->getStatus(), $this->__('Error in order validation %s', $e->getMessage()))
                            ->save();
                }
            }
        } else {
            foreach ($this->getRealOrderIds() as $realOrderId) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($realOrderId);
                $order->addStatusToHistory($order->getStatus(), $this->__('Error bad IP : %s', $_SERVER['REMOTE_ADDR']))
                        ->save();
            }
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    protected function _updateOrderState($order, $model)
    {
        if ($this->_payboxResponse['error'] == '00000') {
            // Aucune erreur = paiement paybox accepté
            if ($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED) {
                $order->unhold();
            }

            if ($model->getConfigData('order_status_payment_accepted') == Mage_Sales_Model_Order::STATE_PROCESSING) {
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, $model->getConfigData('order_status_payment_accepted'), $this->__('Payment accepted by Paybox'));
            } else {
                $order->addStatusToHistory(
                        $model->getConfigData('order_status_payment_accepted'), $this->__('Payment accepted by Paybox'), true
                );
            }

            if ($order->getPayment()->getMethodInstance()->getPaymentAction() == Quadra_Paybox_Model_System::PBX_PAYMENT_ACTION_ATHORIZE_CAPTURE) {
                $order->getPayment()
                        ->getMethodInstance()
                        ->setTransactionId($this->_payboxResponse['trans']);

                // Faut-il créer la facture
                if ($model->getConfigData('invoice_create')) {
                    if ($this->_createInvoice($order)) {
                        $order->addStatusToHistory($order->getStatus(), $this->__('Invoice was create successfully'));
                    } else {
                        $order->addStatusToHistory($order->getStatus(), $this->__('Cann\'t create invoice'));
                    }
                }
            }

            if (!$order->getEmailSent()) {
                $order->sendNewOrderEmail();
            }
            $order->save();
        } else {
            // Si le client a déjà payé on ne fait aucun traitement
            if ($order->getStatus() == $model->getConfigData('order_status_payment_accepted') && $this->getCheckout()->getQuote()->getIsActive() == false) {
                $order->addStatusToHistory($order->getStatus(), $this->__('Attempt to return to Paybox, action ignored'));
                exit();
            }

            // Erreur = paiement paybox refusé
            $messageError = $this->__('Customer was rejected by Paybox');
            if (array_key_exists('error', $this->_payboxResponse)) {
                $messageError .= ' - Code Erreur : ' . $this->_payboxResponse['error'];
            }

            $order->setState(Mage_Sales_Model_Order::STATE_HOLDED, Mage_Sales_Model_Order::STATE_HOLDED, $messageError);
            $order->save();
        }
    }

    protected function _reorder()
    {
        $cart = Mage::getSingleton('checkout/cart');
        /* @var $cart Mage_Checkout_Model_Cart */

        foreach ($this->getRealOrderIds() as $realOrderId) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($realOrderId);

            if ($order->getId()) {
                $items = $order->getItemsCollection();
                foreach ($items as $item) {
                    try {
                        $cart->addOrderItem($item);
                    } catch (Mage_Core_Exception $e) {
                        if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                            Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                        } else {
                            Mage::getSingleton('checkout/session')->addError($e->getMessage());
                        }
                    } catch (Exception $e) {
                        Mage::getSingleton('checkout/session')->addException($e, Mage::helper('checkout')->__('Cannot add the item to shopping cart.'));
                    }
                }
            }
        }

        $cart->save();
    }

}
