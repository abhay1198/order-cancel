<?php
/**
 * Abhay
 * 
 * PHP version 7
 * 
 * @category  Abhay
 * @package   Abhay_OrderCancel
 * @author    Abhay Agrawal <abhay@gmail.com>
 * @copyright 2022 Copyright © Abhay
 * @license   See COPYING.txt for license details.
 * @link      https://github.com/abhay1198/order-cancel
 */
namespace Abhay\OrderCancel\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Order Cancel Helper Class
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }
    
    /**
     * Get Module Status
     *
     * @return bool|0|1
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            'order_cancel/general/enable',
            ScopeInterface::SCOPE_STORE
        );
    }
}
