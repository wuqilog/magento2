<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Backend;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;

class BaseurlTest extends \PHPUnit_Framework_TestCase
{
    public function testSaveMergedJsCssMustBeCleaned()
    {

        $context = (new ObjectManager($this))->getObject(\Magento\Framework\Model\Context::class);

        $resource = $this->getMock(\Magento\Config\Model\ResourceModel\Config\Data::class, [], [], '', false);
        $resource->expects($this->any())->method('addCommitCallback')->will($this->returnValue($resource));
        $resourceCollection = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mergeService = $this->getMock(\Magento\Framework\View\Asset\MergeService::class, [], [], '', false);
        $coreRegistry = $this->getMock(\Magento\Framework\Registry::class, [], [], '', false);
        $coreConfig = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $cacheTypeListMock = $this->getMockBuilder(\Magento\Framework\App\Cache\TypeListInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $model = $this->getMock(
            \Magento\Config\Model\Config\Backend\Baseurl::class,
            ['getOldValue'],
            [$context, $coreRegistry, $coreConfig, $cacheTypeListMock, $mergeService, $resource, $resourceCollection]
        );
        $cacheTypeListMock->expects($this->once())
            ->method('invalidate')
            ->with(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER)
            ->willReturn($model);
        $mergeService->expects($this->once())->method('cleanMergedJsCss');

        $model->setValue('http://example.com/')->setPath(Store::XML_PATH_UNSECURE_BASE_URL);
        $model->afterSave();
    }
}
