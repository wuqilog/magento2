<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that comment about voided amount exists in Comments History section on order page in Admin.
 */
class AssertVoidInCommentsHistory extends AbstractConstraint
{
    /**
     * Message about voided amount in order.
     */
    const VOIDED_AMOUNT = 'Voided authorization. Amount: $';

    /**
     * Assert that comment about voided amount exist in Comments History section on order page in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param string $orderId
     * @param array $prices
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        $orderId,
        array $prices
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);

        \PHPUnit_Framework_Assert::assertContains(
            self::VOIDED_AMOUNT . $prices['grandTotal'],
            $salesOrderView->getOrderHistoryBlock()->getVoidedAmount(),
            'Incorrect voided amount value for the order #' . $orderId
        );
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Message about voided amount is available in Comments History section.";
    }
}
