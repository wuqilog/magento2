<?php
/**
 * \Magento\Wishlist\Block\Item\Configure
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Block\Item;

class ConfigureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Wishlist\Block\Item\Configure
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlistDataMock;

    protected function setUp()
    {
        $this->wishlistDataMock = $this->getMockBuilder(
            \Magento\Wishlist\Helper\Data::class
        )->disableOriginalConstructor()->getMock();
        $this->contextMock = $this->getMockBuilder(
            \Magento\Framework\View\Element\Template\Context::class
        )->disableOriginalConstructor()->getMock();
        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaperMock = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaperMock->method('escapeHtml')
            ->willReturnCallback(
                function ($string) {
                    return 'escapeHtml' . $string;
                }
            );
        $this->contextMock->expects($this->once())
            ->method('getEscaper')
            ->willReturn($escaperMock);

        $this->model = new \Magento\Wishlist\Block\Item\Configure(
            $this->contextMock,
            $this->wishlistDataMock,
            $this->registryMock
        );
    }

    public function testGetWishlistOptions()
    {
        $typeId = 'simple';
        $product = $this->getMockBuilder(
            \Magento\Catalog\Model\Product::class
        )->disableOriginalConstructor()->getMock();
        $product->expects($this->once())->method('getTypeId')->willReturn($typeId);
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with($this->equalTo('product'))
            ->willReturn($product);

        $this->assertEquals(['productType' => 'escapeHtml' . $typeId], $this->model->getWishlistOptions());
    }

    public function testGetProduct()
    {
        $product = 'some test product';
        $this->registryMock->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            $this->equalTo('product')
        )->willReturn(
            $product
        );

        $this->assertEquals($product, $this->model->getProduct());
    }

    public function testSetLayout()
    {
        $layoutMock = $this->getMock(
            \Magento\Framework\View\LayoutInterface::class,
            [],
            [],
            '',
            false
        );

        $blockMock = $this->getMock(
            \Magento\Framework\View\Element\AbstractBlock::class,
            ['setCustomAddToCartUrl'],
            [],
            '',
            false
        );
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.info')
            ->will($this->returnValue($blockMock));

        $itemMock = $this->getMock(
            \Magento\Wishlist\Model\Item::class,
            [],
            [],
            '',
            false
        );

        $this->registryMock->expects($this->exactly(2))
            ->method('registry')
            ->with('wishlist_item')
            ->willReturn($itemMock);

        $this->wishlistDataMock->expects($this->once())
            ->method('getAddToCartUrl')
            ->with($itemMock)
            ->willReturn('some_url');

        $blockMock->expects($this->once())
            ->method('setCustomAddToCartUrl')
            ->with('some_url');

        $this->assertEquals($this->model, $this->model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->model->getLayout());
    }

    public function testSetLayoutWithNoItem()
    {
        $layoutMock = $this->getMock(
            \Magento\Framework\View\LayoutInterface::class,
            [],
            [],
            '',
            false
        );

        $blockMock = $this->getMock(
            \Magento\Framework\View\Element\AbstractBlock::class,
            ['setCustomAddToCartUrl'],
            [],
            '',
            false
        );
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.info')
            ->willReturn($blockMock);

        $this->registryMock->expects($this->exactly(1))
            ->method('registry')
            ->with('wishlist_item')
            ->willReturn(null);

        $this->wishlistDataMock->expects($this->never())
            ->method('getAddToCartUrl');

        $blockMock->expects($this->never())
            ->method('setCustomAddToCartUrl');

        $this->assertEquals($this->model, $this->model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->model->getLayout());
    }

    public function testSetLayoutWithNoBlockAndItem()
    {
        $layoutMock = $this->getMock(
            \Magento\Framework\View\LayoutInterface::class,
            [],
            [],
            '',
            false
        );

        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.info')
            ->willReturn(null);

        $this->registryMock->expects($this->never())
            ->method('registry');

        $this->wishlistDataMock->expects($this->never())
            ->method('getAddToCartUrl');

        $this->assertEquals($this->model, $this->model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->model->getLayout());
    }
}
