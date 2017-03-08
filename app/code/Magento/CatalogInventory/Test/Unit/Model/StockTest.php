<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model;

/**
 * Class StockTest
 */
class StockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\Context
     */
    private $context;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var Magento\Framework\Api\ExtensionAttributesFactory
     */
    private $extensionFactory;

    /**
     * @var \Magento\Framework\Model\ExtensionAttributesFactory
     */
    private $customAttributeFactory;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource
     */
    private $resource;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb
     */
    private $resourceCollection;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $eventDispatcher;

    /**
     * @var \Magento\CatalogInventory\Model\Stock
     */
    private $stockModel;

    public function setUp()
    {
        /** @var  PHPUnit_Framework_MockObject_MockObject */
        $this->eventDispatcher = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMock();
        
        $this->context = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEventDispatcher'])
            ->getMock();
        $this->context->expects($this->any())->method('getEventDispatcher')->willReturn($this->eventDispatcher);
        
        $this->registry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->extensionFactory = $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttributesFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->customAttributeFactory = $this->getMockBuilder(\Magento\Framework\Api\AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->resource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\AbstractResource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIdFieldName'])
            ->getMockForAbstractClass();
        
        $this->resourceCollection = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->stockModel = new \Magento\CatalogInventory\Model\Stock(
            $this->context,
            $this->registry,
            $this->extensionFactory,
            $this->customAttributeFactory,
            $this->resource,
            $this->resourceCollection
        );
    }

    /**
     * We wan't to ensure that property $_eventPrefix used during event dispatching
     *
     * @param $eventName
     * @param $methodName
     *
     * @dataProvider eventsDataProvider
     */
    public function testDispatchEvents($eventName, $methodName)
    {
        $isCalledWithRightPrefix = 0;
        $this->eventDispatcher->expects($this->any())->method('dispatch')->with(
            $this->callback(function ($arg) use (&$isCalledWithRightPrefix, $eventName) {
                $isCalledWithRightPrefix |= ($arg === $eventName);
                return true;
            }),
            $this->anything()
        );
            
        $this->stockModel->$methodName();
        $this->assertEquals(
            1,
            (int) $isCalledWithRightPrefix,
            sprintf("Event %s doesn't dispatched", $eventName)
        );
    }
    
    public function eventsDataProvider()
    {
        return [
            ['cataloginventory_stock_save_before', 'beforeSave'],
            ['cataloginventory_stock_save_after', 'afterSave'],
        ];
    }
}
