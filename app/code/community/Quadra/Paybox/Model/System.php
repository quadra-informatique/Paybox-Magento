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
class Quadra_Paybox_Model_System extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Paybox const variables
     */

    const PBX_FORM_HTML_METHOD = 1;
    const PBX_COMMAND_LINE_METHOD = 4;
    const PBX_METHOD_CALL = 'POST';
    const PBX_PAYMENT_ACTION_ATHORIZE = 'O';
    const PBX_PAYMENT_ACTION_ATHORIZE_CAPTURE = 'N';
    const PBX_PAYMENT_TYPE_CARTE = 'CARTE';
    const PBX_PAYMENT_TYPE_SYMPASS = 'SYMPASS';
    const PBX_PAYMENT_TYPE_PAYNOVA = 'PAYNOVA';
    const PBX_PAYMENT_TYPE_TERMINEO = 'TERMINEO';
    const PBX_PAYMENT_TYPE_PAYPAL = 'PAYPAL';
    const PBX_PAYMENT_TYPE_PREPAYEE = 'PREPAYEE';
    const PBX_CARTE_TYPE_CB = 'CB';
    const PBX_CARTE_TYPE_VISA = 'VISA';
    const PBX_CARTE_TYPE_EUROCARDMASTERCARD = 'EUROCARD_MASTERCARD';
    const PBX_CARTE_TYPE_ECARD = 'E_CARD';
    const PBX_CARTE_TYPE_AMEX = 'AMEX';
    const PBX_CARTE_TYPE_DINERS = 'DINERS';
    const PBX_CARTE_TYPE_JCB = 'JCB';
    const PBX_CARTE_TYPE_AURORE = 'AURORE';
    const PBX_CARTE_TYPE_PAYNOVA = 'PAYNOVA';
    const PBX_CARTE_TYPE_TERMINEO = 'TERMINEO';
    const PBX_CARTE_TYPE_PAYPAL = 'PAYPAL';
    const PBX_CARTE_TYPE_MAXICHEQUE = 'MAXICHEQUE';

    protected $_code = 'paybox_system';
    protected $_authorized_ips = array('194.2.122.158', '195.101.99.76', '195.25.7.166');
    protected $_isGateway = false;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_formBlockType = 'paybox/system_form';
    protected $_quote;
    protected $_order;
    protected $_cartTypes;
    protected $_currenciesNumbers;
    protected $_backupservers = array();
    protected $_backuptimeouts = array();

    /**
     * Get quote model
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (!$this->_quote) {
            $quoteId = Mage::getSingleton('checkout/session')->getLastQuoteId();
            $this->_quote = Mage::getModel('sales/quote')->load($quoteId);
        }
        return $this->_quote;
    }

    /**
     * Get order model
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->_order) {
            $paymentInfo = $this->getInfoInstance();
            $this->_order = Mage::getModel('sales/order')->loadByIncrementId($paymentInfo->getOrder()->getRealOrderId());
        }
        return $this->_order;
    }

    /**
     * Get real order ids
     *
     * @return string
     */
    public function getOrderList()
    {
        if ($this->getQuote()->getIsMultiShipping())
            return Mage::getSingleton('checkout/session')->getRealOrderIds();
        else
            return $this->getOrder()->getRealOrderId();
    }

    /**
     * Set order
     *
     * @param Mage_Sales_Model_Order $order
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    public function getAuthorizedIps()
    {
        return $this->_authorized_ips;
    }

    /**
     * Retrieve information from paybox design configuration
     *
     * @param   string $field
     * @return  mixed
     */
    public function getDesignConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        $path = 'paybox_design/intermediate_page/' . $field;
        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * Get cart types for all payment types
     * or for given payment type
     *
     * @param string $paymentType
     * @return array
     */
    protected function _getCartTypes($paymentType = null)
    {
        if (!$this->_cartTypes) {
            $this->_cartTypes = array(
                self::PBX_PAYMENT_TYPE_CARTE => array(
                    'none' => Mage::helper('paybox')->__('Customer Choise'),
                    self::PBX_CARTE_TYPE_CB => Mage::helper('paybox')->__('CB'),
                    self::PBX_CARTE_TYPE_VISA => Mage::helper('paybox')->__('VISA'),
                    self::PBX_CARTE_TYPE_EUROCARDMASTERCARD => Mage::helper('paybox')->__('EUROCARD & MASTERCARD'),
                    self::PBX_CARTE_TYPE_ECARD => Mage::helper('paybox')->__('E CARD'),
                    self::PBX_CARTE_TYPE_AMEX => Mage::helper('paybox')->__('AMEX'),
                    self::PBX_CARTE_TYPE_DINERS => Mage::helper('paybox')->__('DINERS'),
                    self::PBX_CARTE_TYPE_JCB => Mage::helper('paybox')->__('JCB'),
                    self::PBX_CARTE_TYPE_AURORE => Mage::helper('paybox')->__('AURORE'),
                ),
                self::PBX_PAYMENT_TYPE_SYMPASS => array(
                    'none' => Mage::helper('paybox')->__('Customer Choise'),
                    self::PBX_CARTE_TYPE_CB => Mage::helper('paybox')->__('CB'),
                    self::PBX_CARTE_TYPE_VISA => Mage::helper('paybox')->__('VISA'),
                    self::PBX_CARTE_TYPE_EUROCARDMASTERCARD => Mage::helper('paybox')->__('EUROCARD & MASTERCARD'),
                    self::PBX_CARTE_TYPE_ECARD => Mage::helper('paybox')->__('E CARD'),
                    self::PBX_CARTE_TYPE_AMEX => Mage::helper('paybox')->__('AMEX'),
                    self::PBX_CARTE_TYPE_DINERS => Mage::helper('paybox')->__('DINERS'),
                    self::PBX_CARTE_TYPE_JCB => Mage::helper('paybox')->__('JCB'),
                    self::PBX_CARTE_TYPE_AURORE => Mage::helper('paybox')->__('AURORE'),
                ),
                self::PBX_PAYMENT_TYPE_PAYNOVA => array(
                    self::PBX_CARTE_TYPE_PAYNOVA => Mage::helper('paybox')->__('PAYNOVA'),
                ),
                self::PBX_PAYMENT_TYPE_TERMINEO => array(
                    self::PBX_CARTE_TYPE_TERMINEO => Mage::helper('paybox')->__('TERMINEO'),
                ),
                self::PBX_PAYMENT_TYPE_PAYPAL => array(
                    self::PBX_CARTE_TYPE_PAYPAL => Mage::helper('paybox')->__('PAYPAL'),
                ),
                self::PBX_PAYMENT_TYPE_PREPAYEE => array(
                    self::PBX_CARTE_TYPE_MAXICHEQUE => Mage::helper('paybox')->__('MAXICHEQUE'),
                )
            );
        }

        if (!is_null($paymentType)) {
            if (isset($this->_cartTypes[$paymentType])) {
                return $this->_cartTypes[$paymentType];
            }
        }

        return $this->_cartTypes;
    }

    /**
     * Get cart types by given payment
     *
     * @param string $paymentType
     * @return array
     */
    public function getCartTypesByPayment($paymentType)
    {
        if ($paymentType == '') {
            return array();
        }
        return $this->_getCartTypes($paymentType);
    }

    /**
     * Get all cart types in JSON format
     *
     * @return string
     */
    public function getJsonCartTypes()
    {
        return Zend_Json::encode($this->_getCartTypes());
    }

    /**
     * Get payment method
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->getConfigData('pbx_mode');
    }

    /**
     * Get name of executable file
     *
     * @return string
     */
    public function getPayboxFile()
    {
        return $this->getConfigData('pbx_file');
    }

    /**
     * Get Payment type
     *
     * @return string
     */
    public function getPaymentType()
    {
        return $this->getConfigData('pbx_typepaiement');
    }

    /**
     * Get Payment Action of Paybox System changed to Paybox specification
     *
     * @return string
     */
    public function getPaymentAction()
    {
        $paymentAction = $this->getConfigData('pbx_autoseule');
        switch ($paymentAction) {
            case self::ACTION_AUTHORIZE:
                return self::PBX_PAYMENT_ACTION_ATHORIZE;
                break;
            case self::ACTION_AUTHORIZE_CAPTURE:
                return self::PBX_PAYMENT_ACTION_ATHORIZE_CAPTURE;
                break;
            default:
                return self::PBX_PAYMENT_ACTION_ATHORIZE;
                break;
        }
    }

    /**
     * Get cart type
     *
     * @return string
     */
    public function getCartType()
    {
        return $this->getConfigData('pbx_typecarte');
    }

    /**
     * Get Site number (TPE)
     *
     * @return string
     */
    public function getSiteNumber()
    {
        return $this->getConfigData('pbx_site');
    }

    /**
     * Get Rang number
     *
     * @return string
     */
    public function getRang()
    {
        return $this->getConfigData('pbx_rang');
    }

    /**
     * Get Identifiant number
     *
     * @return string
     */
    public function getIdentifiant()
    {
        return $this->getConfigData('pbx_identifiant');
    }

    /**
     * Get currency number in ISO4217 format
     *
     * @return string
     */
    public function getCurrencyNumber()
    {
        $currencyCode = $this->getOrder()->getBaseCurrencyCode();
        if (!$this->_currenciesNumbers) {
            $this->_currenciesNumbers = simplexml_load_file(Mage::getBaseDir() . '/app/code/community/Quadra/Paybox/etc/currency.xml');
        }
        if ($this->_currenciesNumbers->$currencyCode) {
            return (string) $this->_currenciesNumbers->$currencyCode;
        }
    }

    /**
     * Get language of interface of payment defined in config
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->getConfigData('pbx_langue');
    }

    /**
     * Get api urls and timeouts if they defined in config
     *
     * QI Marine
     */
    public function getBackupServer()
    {
        $bpserversConfigPath = 'payment/paybox_system/pbx_bpserver';
        $configValueSerialized = Mage::getStoreConfig($bpserversConfigPath, $this->getStore());

        foreach ($this->_backupservers as $var => $value) {
            unset($var);
        }

        foreach ($this->_backuptimeouts as $var => $value) {
            unset($var);
        }

        if ($configValueSerialized) {
            $bpservers = @unserialize($configValueSerialized);
            if (!empty($bpservers)) {
                foreach ($bpservers as $rule) {
                    if (trim($rule['bpserver']) != '') {
                        $this->_backupservers[] = $rule['bpserver'];
                        $this->_backuptimeouts[] = $rule['bptimeout'];
                    }
                }
            }
        }
    }

    /**
     * Get api urls if they defined in config
     *
     * @return unknown
     */
    public function getApiUrls()
    {
        $fielldsArr = array();
        if (($primary = trim($this->getConfigData('pbx_paybox'))) != '') {
            $fielldsArr['PBX_PAYBOX'] = $primary;
        }

        if (count($this->_backupservers) == 0)
            $this->getBackupServer();

        if (count($this->_backupservers) > 0) {
            foreach ($this->_backupservers as $key => $server) {
                $nServer = $key + 1;
                if ($nServer > 4)
                    break;
                $fielldsArrKey = 'PBX_BACKUP' . $nServer;
                $fielldsArr[$fielldsArrKey] = $server;
            }
        }

        return $fielldsArr;
    }

    /**
     * Get timeouts for api urls if timeouts diferent from default
     *
     * @return array
     */
    public function getTimeouts()
    {
        $fielldsArr = array();
        if (($timeout = trim($this->getConfigData('pbx_timeout'))) != '') {
            $fielldsArr['PBX_TIMEOUT'] = $timeout;
        }

        if (count($this->_backuptimeouts) == 0)
            $this->getBackupServer();

        if (count($this->_backuptimeouts) > 0) {
            foreach ($this->_backuptimeouts as $key => $timeout) {
                $nTimeout = $key + 1;
                $fielldsArrKey = 'PBX_TIMEOUT' . $nTimeout;
                $fielldsArr[$fielldsArrKey] = $timeout;
            }
        }

        return $fielldsArr;
    }

    /**
     * Get params from config for HTML form mode
     *
     * @return array
     */
    public function getManagementMode()
    {
        $fieldsArr = array();
        if (($text = trim($this->getDesignConfigData('pbx_txt'))) != '') {
            $fieldsArr['PBX_TXT'] = utf8_encode($text);
        }

        if (($wait = trim($this->getDesignConfigData('pbx_wait'))) != '') {
            $fieldsArr['PBX_WAIT'] = $wait;
        }

        if (($boutpi = trim($this->getDesignConfigData('pbx_boutpi')))) {
            $fieldsArr['PBX_BOUTPI'] = $boutpi;
        }

        if (($bkgd = trim($this->getDesignConfigData('pbx_bkgd'))) != '') {
            $fieldsArr['PBX_BKGD'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'payment/paybox/bkgd/' . $bkgd;
        } else {
            if (($color = trim($this->getDesignConfigData('pbx_bkgd_color'))) != '') {
                $fieldsArr['PBX_BKGD'] = $color;
            }
        }

        $fieldsArr['PBX_OUTPUT'] = $this->getDesignConfigData('pbx_output');

        return $fieldsArr;
    }

    /**
     * Get ping flag (commandline mode)
     *
     * @return unknown
     */
    public function getPingFlag()
    {
        return $this->getConfigData('pbx_ping');
    }

    /**
     * Get ping port (commandline mode)
     *
     * @return string
     */
    public function getPingPort()
    {
        return $this->getConfigData('pbx_port');
    }

    /**
     * Get debug flag
     *
     * @return string
     */
    public function getDebugFlag()
    {
        return $this->getConfigData('debug_flag');
    }

    public function getOrderPlaceRedirectUrl()
    {
        if ($this->getPaymentMethod() == self::PBX_FORM_HTML_METHOD) {
            return Mage::getUrl('paybox/system/redirect', array('_secure' => true));
        } else {
            return Mage::getUrl('paybox/system/commandline', array('_secure' => true));
        }
    }

    /**
     * Building array of params to send
     *
     * @return array
     */
    public function getFormFields()
    {
        $fieldsArr = array();

        $fieldsArr = array(
            'PBX_MODE' => $this->getPaymentMethod(),
            'PBX_SITE' => $this->getSiteNumber(), //'1999888',
            'PBX_RANG' => $this->getRang(), //'99',
            'PBX_IDENTIFIANT' => $this->getIdentifiant(), //'2',
            'PBX_TOTAL' => (int) ($this->getOrder()->getBaseGrandTotal() * 100),
            'PBX_DEVISE' => $this->getCurrencyNumber(),
            'PBX_CMD' => $this->getOrderList(),
            'PBX_PORTEUR' => $this->getOrder()->getCustomerEmail(),
            'PBX_RETOUR' => 'amount:M;ref:R;auto:A;trans:T;error:E;sign:K',
            'PBX_EFFECTUE' => Mage::getUrl('paybox/system/success', array('_secure' => true)),
            'PBX_REFUSE' => Mage::getUrl('paybox/system/refuse', array('_secure' => true)),
            'PBX_ANNULE' => Mage::getUrl('paybox/system/decline', array('_secure' => true)),
            'PBX_REPONDRE_A' => Mage::getUrl('paybox/system/notify', array('_secure' => true)),
            'PBX_AUTOSEULE' => $this->getPaymentAction(),
            'PBX_LANGUE' => $this->getLanguage(),
            'PBX_ERREUR' => Mage::getUrl('paybox/system/error', array('_secure' => true)),
            'PBX_RUF1' => self::PBX_METHOD_CALL,
        );

        if ($fieldsArr['PBX_TOTAL'] < ((int) $this->getConfigData('min_order_total_3ds') * 100))
            $fieldsArr['PBX_3DS'] = 'N';

        if ($this->getQuote()->getIsMultiShipping()) {
            $fieldsArr['PBX_TOTAL'] = (int) ($this->getQuote()->getBaseGrandTotal() * 100);
        }

        if ($this->getCartType() != 'none') {
            $fieldsArr['PBX_TYPEPAIEMENT'] = $this->getPaymentType();
            $fieldsArr['PBX_TYPECARTE'] = $this->getCartType();
        }

        if (count($apiUrls = $this->getApiUrls())) {
            $fieldsArr = array_merge($fieldsArr, $this->getApiUrls());
        }
        if (count($timeouts = $this->getTimeouts())) {
            $fieldsArr = array_merge($fieldsArr, $this->getTimeouts());
        }

        if ($this->getPaymentMethod() == self::PBX_FORM_HTML_METHOD) {
            $fieldsArr = array_merge($fieldsArr, $this->getManagementMode());
        }

        if ($this->getPaymentMethod() == self::PBX_COMMAND_LINE_METHOD && $this->getPingFlag()) {
            $tmpFieldsArr['PBX_PING'] = '1';
            if (($pingPort = trim($this->getPingPort())) != '') {
                $tmpFieldsArr['PING_PORT'] = $pingPort;
            }

            $fieldsArr = array_merge($fieldsArr, $tmpFieldsArr);
        }

        if ($this->getDebugFlag()) {
            Mage::getSingleton('paybox/api_debug')
                    ->setRealOrderId($this->getOrderList())
                    ->setRequestBody(serialize($fieldsArr))
                    ->save();
        }

        return $fieldsArr;
    }

    /**
     * Checking response
     *
     * @param array $response
     * @return bool
     */
    public function checkResponse($response)
    {
        if ($this->getDebugFlag()) {
            $debug = Mage::getSingleton('paybox/api_debug')
                    ->load($response['ref'], 'real_order_id')
                    ->setResponseBody(serialize($response))
                    ->save();
        }

        if (isset($response['error'], $response['amount'], $response['ref'], $response['trans'])
        ) {
            return true;
        }
        return false;
    }

    public function capture(Varien_Object $payment, $amount)
    {
        $payment->setStatus(self::STATUS_APPROVED)
                ->setLastTransId($this->getTransactionId());
        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @param   Varien_Object $info
     * @return  Mage_Payment_Model_Abstract
     */
    public function validate()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $quote->setCustomerNoteNotify(false);
        parent::validate();
    }

    public function authorize(Varien_Object $payment, $amount)
    {
        $payment->setStatus(self::STATUS_APPROVED)
                ->setLastTransId($this->getTransactionId());

        return $this;
    }

    public function cancel(Varien_Object $payment)
    {
        $payment->setStatus(self::STATUS_DECLINED);
        return $this;
    }

    /**
     *  Return response for Paybox bas response
     *
     *  @param    none
     *  @return	  string Failure response string
     */
    public function getErrorResponse()
    {
        $response = array(
            'Pragma: no-cache',
            'Content-type : text/plain',
            'Version: 1',
            'Document falsifie'
        );
        return implode("\n", $response) . "\n";
    }

}
