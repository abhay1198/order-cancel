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
 * @link      https://github.com/abhay1198/
 */

namespace Abhay\OrderCancel\Controller\Order;

use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Controller Index Class
 */
class Index extends \Magento\Framework\App\Action\Action
{
    protected $order;

     /**
      * @var PageFactory
      */
    protected $resultPageFactory;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Framework\App\Action\Context      $context
     * @param \Psr\Log\LoggerInterface                   $logger
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Sales\Model\Order                 $order
     */    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        LoggerInterface $logger,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Model\Order $order
    ) {
        $this->logger = $logger;
        $this->order = $order;
        $this->resultPageFactory = $resultPageFactory;
        return parent::__construct($context);
    }

    /**
     * Execute Method
     * 
     * @return resultRedirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $orderId = $this->getRequest()->getParam('orderid');
            $order = $this->order->load($orderId);
            if ($order->canCancel()) {
                $order->cancel();
                $order->save();
                $this->messageManager->addSuccess(
                    __('Your Order has been canceled successfully.')
                );
            } else {
                $this->messageManager->addError(__('Your Order can`t be canceled.'));
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
        }    
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;   
    }
}
