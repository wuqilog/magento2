<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model;

use Magento\Framework\DataObject;
use Magento\Eav\Model\Config;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\Cache\Type as Cache;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeFactoryMock;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Type\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheStateMock;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $universalFactoryMock;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var Type|\PHPUnit_Framework_MockObject_MockObject
     */
    private $typeMock;

    protected function setUp()
    {
        $this->cacheMock = $this->getMock(\Magento\Framework\App\CacheInterface::class);
        $this->typeFactoryMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\TypeFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactoryMock =
            $this->getMockBuilder(\Magento\Eav\Model\ResourceModel\Entity\Type\CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheStateMock = $this->getMock(\Magento\Framework\App\Cache\StateInterface::class);
        $this->universalFactoryMock = $this->getMockBuilder(\Magento\Framework\Validator\UniversalFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializerMock = $this->getMock(SerializerInterface::class);

        $this->typeMock = $this->getMock(Type::class, [], [], '', false);

        $this->config = new Config(
            $this->cacheMock,
            $this->typeFactoryMock,
            $this->collectionFactoryMock,
            $this->cacheStateMock,
            $this->universalFactoryMock,
            $this->serializerMock
        );
    }

    /**
     * @param boolean $cacheEnabled
     * @param int $loadCalls
     * @param int $cachedValue
     * @param int $unserializeCalls
     * @dataProvider getAttributeCacheDataProvider
     * @return void
     */
    public function testGetAttributeCache($cacheEnabled, $loadCalls, $unserializeCalls, $cachedValue)
    {
        $attributeData = [
            [
                'attribute_code' => 'attribute_code_1',
                'attribute_id' => 1
            ]
        ];
        $attributeCollectionMock = $this->getMockBuilder(
            Collection::class
        )->disableOriginalConstructor()
            ->setMethods(['getData', 'setEntityTypeFilter'])
            ->getMock();
        $attributeCollectionMock
            ->expects($this->any())
            ->method('setEntityTypeFilter')
            ->will($this->returnSelf());
        $attributeCollectionMock
            ->expects($this->any())
            ->method('getData')
            ->willReturn([]);
        $entityAttributeMock = $this->getMockBuilder(Attribute::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $factoryCalls = [
            [
                Collection::class,
                [],
                $attributeCollectionMock
            ],
            [
                Attribute::class,
                [],
                $entityAttributeMock
            ],
        ];

        $this->cacheStateMock
            ->expects($this->atLeastOnce())
            ->method('isEnabled')
            ->with(Cache::TYPE_IDENTIFIER)
            ->willReturn($cacheEnabled);
        $this->cacheMock
            ->expects($this->exactly($loadCalls))
            ->method('load')
            ->with(Config::ATTRIBUTES_CACHE_ID)
            ->willReturn($cachedValue);
        $this->serializerMock
            ->expects($this->exactly($unserializeCalls))
            ->method('unserialize')
            ->with($cachedValue)
            ->willReturn($attributeData);

        $collectionStub = new DataObject([$attributeData]);
        $this->collectionFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($collectionStub);

        $this->typeFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn(new DataObject(['id' => 101]));

        $this->universalFactoryMock
            ->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValueMap($factoryCalls));

        $entityType = $this->getMockBuilder(Type::class)
            ->setMethods(['getEntity', 'setData', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->config->getAttribute($entityType, 'attribute_code_1');
    }

    /**
     * @return array
     */
    public function getAttributeCacheDataProvider()
    {
        return [
            'cache-disabled' => [
                false,
                0,
                0,
                false,
            ],
            'cache-miss' => [
                true,
                1,
                0,
                false,
            ],
            'cached' => [
                true,
                1,
                1,
                'attribute serialzied data',
            ],
        ];
    }

    public function testClear()
    {
        $this->cacheMock->expects($this->once())
            ->method('clean')
            ->with(
                $this->equalTo(
                    [
                        Cache::CACHE_TAG,
                        Attribute::CACHE_TAG,
                    ]
                )
            );
        $this->config->clear();
    }

    public function testGetEntityTypeInstanceOfTypePassed()
    {
        $this->assertEquals(
            $this->typeMock,
            $this->config->getEntityType($this->typeMock)
        );
    }

    public function testGetEntityTypeCacheExists()
    {
        $entityTypeCode = 'catalog_product';
        $data = [
            $entityTypeCode => [
                'entity_type_id' => 1
            ]
        ];
        $serializedData = 'serialized data';
        $this->cacheStateMock->expects($this->once())
            ->method('isEnabled')
            ->with(Cache::TYPE_IDENTIFIER)
            ->willReturn(true);
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with(Config::ENTITIES_CACHE_ID)
            ->willReturn($serializedData);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($data);
        $this->typeMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($data[$entityTypeCode]['entity_type_id']);
        $this->typeMock->expects($this->once())
            ->method('getEntityTypeCode')
            ->willReturn($entityTypeCode);
        $this->typeFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $data[$entityTypeCode]])
            ->willReturn($this->typeMock);
        $this->assertInstanceOf(
            Type::class,
            $this->config->getEntityType($entityTypeCode)
        );
    }

    public function testGetEntityTypeCacheDoesNotExist()
    {
        $entityTypeCode = 'catalog_product';
        $collectionData = [
            [
                'entity_type_id' => 1,
                'entity_type_code' => $entityTypeCode
            ]
        ];
        $data = [
            $entityTypeCode => [
                'entity_type_id' => 1,
                'entity_type_code' => $entityTypeCode,
                'attribute_model' => Attribute::class
            ]
        ];
        $serializedData = 'serialized data';
        $this->cacheStateMock->expects($this->once())
            ->method('isEnabled')
            ->with(Cache::TYPE_IDENTIFIER)
            ->willReturn(true);
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with(Config::ENTITIES_CACHE_ID)
            ->willReturn(false);
        $this->serializerMock->expects($this->never())
            ->method('unserialize');
        $attributeCollectionMock = $this->getMock(Collection::class, [], [], '', false);
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($attributeCollectionMock);
        $attributeCollectionMock->expects($this->once())
            ->method('getData')
            ->willReturn($collectionData);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($data)
            ->willReturn($serializedData);
        $this->cacheMock->expects($this->once())
            ->method('save')
            ->with(
                $serializedData,
                Config::ENTITIES_CACHE_ID,
                [
                    Cache::CACHE_TAG,
                    Attribute::CACHE_TAG
                ]
            );
        $this->typeMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($data[$entityTypeCode]['entity_type_id']);
        $this->typeMock->expects($this->once())
            ->method('getEntityTypeCode')
            ->willReturn($entityTypeCode);
        $this->typeFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $data[$entityTypeCode]])
            ->willReturn($this->typeMock);
        $this->assertInstanceOf(
            Type::class,
            $this->config->getEntityType($entityTypeCode)
        );
    }
}
