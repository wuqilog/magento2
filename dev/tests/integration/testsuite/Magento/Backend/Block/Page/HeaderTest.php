<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Page;

/**
 * Test \Magento\Backend\Block\Page\Header
 * @magentoAppArea adminhtml
 */
class HeaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Page\Header
     */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Backend\Block\Page\Header::class
        );
    }

    public function testGetHomeLink()
    {
        $expected = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Backend\Helper\Data::class
        )->getHomePageUrl();
        $this->assertEquals($expected, $this->_block->getHomeLink());
    }
}
