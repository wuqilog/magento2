<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model\TaxClass;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider createDataProvider
     *
     * @param string $classType
     * @param string $className
     * @param \PHPUnit_Framework_MockObject_MockObject $classTypeMock
     */
    public function testCreate($classType, $className, $classTypeMock)
    {
        $classMock = $this->getMock(
            \Magento\Tax\Model\ClassModel::class,
            ['getClassType', 'getId', '__wakeup'],
            [],
            '',
            false
        );
        $classMock->expects($this->once())->method('getClassType')->will($this->returnValue($classType));
        $classMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo($className),
            $this->equalTo(['data' => ['id' => 1]])
        )->will(
            $this->returnValue($classTypeMock)
        );

        $taxClassFactory = new \Magento\Tax\Model\TaxClass\Factory($objectManager);
        $this->assertEquals($classTypeMock, $taxClassFactory->create($classMock));
    }

    public function createDataProvider()
    {
        $customerClassMock = $this->getMock(\Magento\Tax\Model\TaxClass\Type\Customer::class, [], [], '', false);
        $productClassMock = $this->getMock(\Magento\Tax\Model\TaxClass\Type\Product::class, [], [], '', false);
        return [
            [
                \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER,
                \Magento\Tax\Model\TaxClass\Type\Customer::class,
                $customerClassMock,
            ],
            [
                \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT,
                \Magento\Tax\Model\TaxClass\Type\Product::class,
                $productClassMock
            ]
        ];
    }

    public function testCreateWithWrongClassType()
    {
        $wrongClassType = 'TYPE';
        $classMock = $this->getMock(
            \Magento\Tax\Model\ClassModel::class,
            ['getClassType', 'getId', '__wakeup'],
            [],
            '',
            false
        );
        $classMock->expects($this->once())->method('getClassType')->will($this->returnValue($wrongClassType));

        $objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);

        $taxClassFactory = new \Magento\Tax\Model\TaxClass\Factory($objectManager);

        $this->setExpectedException(
            \Magento\Framework\Exception\LocalizedException::class,
            sprintf('Invalid type of tax class "%s"', $wrongClassType)
        );
        $taxClassFactory->create($classMock);
    }
}
