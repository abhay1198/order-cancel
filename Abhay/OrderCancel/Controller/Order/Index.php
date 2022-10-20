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

namespace Abhay\OrderCancel\Controller\Order;

use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultFactory;
use Abhay\OrderCancel\Helper\Email;

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
     * @param \Abhay\OrderCancel\Helper\Email            $email
     */    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        LoggerInterface $logger,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Model\Order $order,
        Email $email,
        \Magento\Framework\Pricing\Helper\Data $priceHelper
    ) {
        $this->logger = $logger;
        $this->order = $order;
        $this->resultPageFactory = $resultPageFactory;
        $this->email = $email;
        $this->priceHelper = $priceHelper;
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
                $this->sendEmail(
                    $order->getCustomerFirstname(),
                    $order->getCustomerEmail(),
                    $order->getStoreId(),
                    $order->getIncrementId(),
                );
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


    /**
     * Send Notification Email
     *
     * @param  [string] $customerName
     * @param  [string] $customerEmail
     * @param  [int]    $storeId
     * @param  [int]    $orderId
     * @return void
     */
    public function sendEmail($customerName, $customerEmail, $storeId, $orderId)
    {
        $senderInfo = [
            'name' =>$this->email->getConfig('trans_email/ident_general/name'),
            'email' => $this->email->getConfig('trans_email/ident_general/email'),
        ];
        $receiverInfo = [
            'name' => $customerName,
            'email' => $customerEmail,
        ];

        $emailTemplateVariables = [];
        $orderId = $this->getRequest()->getParam('orderid');
        $order = $this->order->load($orderId);
        $emailTemplateVariables['order_id'] = $orderId;
        $emailTemplateVariables['customer_name'] = $customerName;
        $emailTemplateVariables['sender_name'] = $senderInfo['name'];
        $emailTemplateVariables['store_name'] = $order->getStore()->getName();
        $emailTemplateVariables['entity_id'] = $order->getEntity_id();
        $emailTemplateVariables['created_at'] = $order->getCreated_at();
        $emailTemplateVariables['base_grand_total'] = $this->priceHelper->currency($order->getBase_grand_total(), true, false);

        $this->email->sendOrderCancelMail(
            $emailTemplateVariables,
            $senderInfo,
            $receiverInfo,
            $storeId
        );
    }
}
