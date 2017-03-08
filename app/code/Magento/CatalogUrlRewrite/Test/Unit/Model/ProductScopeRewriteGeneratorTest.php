<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProductScopeRewriteGeneratorTest
 * @package Magento\CatalogUrlRewrite\Test\Unit\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductScopeRewriteGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $canonicalUrlRewriteGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $currentUrlRewritesRegenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $categoriesUrlRewriteGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $anchorUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService|\PHPUnit_Framework_MockObject_MockObject */
    private $storeViewService;

    /** @var \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $objectRegistryFactory;

    /** @var  StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $storeManager;

    /** @var  ProductScopeRewriteGenerator */
    private $productScopeGenerator;

    public function setUp()
    {
        $this->currentUrlRewritesRegenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->canonicalUrlRewriteGenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->categoriesUrlRewriteGenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->anchorUrlRewriteGenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->objectRegistryFactory = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory::class
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->storeViewService = $this->getMockBuilder(\Magento\CatalogUrlRewrite\Service\V1\StoreViewService::class)
            ->disableOriginalConstructor()->getMock();
        $this->storeManager = $this->getMock(StoreManagerInterface::class);

        $this->productScopeGenerator = (new ObjectManager($this))->getObject(
            \Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator::class,
            [
                'canonicalUrlRewriteGenerator' => $this->canonicalUrlRewriteGenerator,
                'categoriesUrlRewriteGenerator' => $this->categoriesUrlRewriteGenerator,
                'currentUrlRewritesRegenerator' => $this->currentUrlRewritesRegenerator,
                'anchorUrlRewriteGenerator' => $this->anchorUrlRewriteGenerator,
                'objectRegistryFactory' => $this->objectRegistryFactory,
                'storeViewService' => $this->storeViewService,
                'storeManager' => $this->storeManager,
            ]
        );
    }

    public function testGenerationForGlobalScope()
    {
        $product = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $product->expects($this->any())->method('getStoreId')->will($this->returnValue(null));
        $product->expects($this->any())->method('getStoreIds')->will($this->returnValue([1]));
        $this->storeViewService->expects($this->once())->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->will($this->returnValue(false));
        $categoryMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryMock->expects($this->once())
            ->method('getParentId')
            ->willReturn(1);
        $this->initObjectRegistryFactory([]);
        $canonical = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $canonical->setTargetPath('category-1')
            ->setStoreId(1);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$canonical]));
        $categories = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $categories->setTargetPath('category-2')
            ->setStoreId(2);
        $this->categoriesUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$categories]));
        $current = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $current->setTargetPath('category-3')
            ->setStoreId(3);
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$current]));
        $anchorCategories = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $anchorCategories->setTargetPath('category-4')
            ->setStoreId(4);
        $this->anchorUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$anchorCategories]));

        $this->assertEquals(
            [
                'category-1-1' => $canonical,
                'category-2-2' => $categories,
                'category-3-3' => $current,
                'category-4-4' => $anchorCategories
            ],
            $this->productScopeGenerator->generateForGlobalScope([$categoryMock], $product)
        );
    }

    public function testGenerationForSpecificStore()
    {
        $product = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $product->expects($this->any())->method('getStoreId')->will($this->returnValue(1));
        $product->expects($this->never())->method('getStoreIds');
        $storeRootCategoryId = 'root-for-store-id';
        $category = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false);
        $category->expects($this->any())->method('getParentIds')
            ->will($this->returnValue(['root-id', $storeRootCategoryId]));
        $category->expects($this->any())->method('getParentId')->will($this->returnValue('parent_id'));
        $category->expects($this->any())->method('getId')->will($this->returnValue('category_id'));
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $store->expects($this->any())->method('getRootCategoryId')->will($this->returnValue($storeRootCategoryId));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $this->initObjectRegistryFactory([$category]);
        $canonical = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $canonical->setTargetPath('category-1')
            ->setStoreId(1);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$canonical]));
        $this->categoriesUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));
        $this->anchorUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));

        $this->assertEquals(
            ['category-1-1' => $canonical],
            $this->productScopeGenerator->generateForSpecificStoreView(1, [$category], $product)
        );
    }

    /**
     * Test method
     */
    public function testSkipGenerationForGlobalScope()
    {
        $product = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $product->expects($this->any())->method('getStoreIds')->will($this->returnValue([1, 2]));
        $this->storeViewService->expects($this->exactly(2))->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->will($this->returnValue(true));

        $this->assertEquals([], $this->productScopeGenerator->generateForGlobalScope([], $product));
    }

    /**
     * @param array $entities
     */
    protected function initObjectRegistryFactory($entities)
    {
        $objectRegistry = $this->getMockBuilder(\Magento\CatalogUrlRewrite\Model\ObjectRegistry::class)
            ->disableOriginalConstructor()->getMock();
        $this->objectRegistryFactory->expects($this->any())->method('create')
            ->with(['entities' => $entities])
            ->will($this->returnValue($objectRegistry));
    }
}
