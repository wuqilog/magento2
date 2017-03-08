<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Layer\Catalog;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ItemCollectionProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCollection()
    {
        $categoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false);

        $collectionMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class,
            [],
            [],
            '',
            false
        );
        $collectionMock->expects($this->once())->method('addCategoryFilter')->with($categoryMock);

        $collectionFactoryMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $collectionFactoryMock->expects($this->any())->method('create')->will($this->returnValue($collectionMock));

        $objectManager = new ObjectManagerHelper($this);
        $provider = $objectManager->getObject(
            \Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider::class,
            ['collectionFactory' => $collectionFactoryMock]
        );

        $provider->getCollection($categoryMock);
    }
}
