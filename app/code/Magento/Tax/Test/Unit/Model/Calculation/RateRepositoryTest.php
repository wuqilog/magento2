<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model\Calculation;

use Magento\Framework\Api\SortOrder;
use \Magento\Tax\Model\Calculation\RateRepository;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\AlreadyExistsException;

/**
 * Class RateRepositoryTest
 * @package Magento\Tax\Test\Unit\Model\Calculation
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RateRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RateRepository
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $rateConverterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $rateRegistryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $rateFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $countryFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $regionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $rateResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $joinProcessorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    protected function setUp()
    {
        $this->rateConverterMock = $this->getMock(
            \Magento\Tax\Model\Calculation\Rate\Converter::class,
            [],
            [],
            '',
            false
        );
        $this->rateRegistryMock = $this->getMock(
            \Magento\Tax\Model\Calculation\RateRegistry::class,
            [],
            [],
            '',
            false
        );
        $this->searchResultFactory = $this->getMock(
            \Magento\Tax\Api\Data\TaxRuleSearchResultsInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->searchResultMock = $this->getMock(
            \Magento\Tax\Api\Data\TaxRuleSearchResultsInterface::class,
            [],
            [],
            '',
            false
        );
        $this->rateFactoryMock = $this->getMock(
            \Magento\Tax\Model\Calculation\RateFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->countryFactoryMock = $this->getMock(
            \Magento\Directory\Model\CountryFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->regionFactoryMock = $this->getMock(
            \Magento\Directory\Model\RegionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->rateResourceMock = $this->getMock(
            \Magento\Tax\Model\ResourceModel\Calculation\Rate::class,
            [],
            [],
            '',
            false
        );
        $this->joinProcessorMock = $this->getMock(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::class,
            [],
            [],
            '',
            false
        );
        $this->collectionProcessor = $this->getMock(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class,
            [],
            [],
            '',
            false
        );
        $this->model = new RateRepository(
            $this->rateConverterMock,
            $this->rateRegistryMock,
            $this->searchResultFactory,
            $this->rateFactoryMock,
            $this->countryFactoryMock,
            $this->regionFactoryMock,
            $this->rateResourceMock,
            $this->joinProcessorMock,
            $this->collectionProcessor
        );
    }

    public function testSave()
    {
        $countryCode = 'US';
        $countryMock = $this->getMock(\Magento\Directory\Model\Country::class, [], [], '', false);
        $countryMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $countryMock->expects($this->any())->method('loadByCode')->with($countryCode)->will($this->returnSelf());
        $this->countryFactoryMock->expects($this->once())->method('create')->will($this->returnValue($countryMock));

        $regionId = 2;
        $regionMock = $this->getMock(\Magento\Directory\Model\Region::class, [], [], '', false);
        $regionMock->expects($this->any())->method('getId')->will($this->returnValue($regionId));
        $regionMock->expects($this->any())->method('load')->with($regionId)->will($this->returnSelf());
        $this->regionFactoryMock->expects($this->once())->method('create')->will($this->returnValue($regionMock));

        $rateTitles = [
            'Label 1',
            'Label 2',
        ];
        $rateMock = $this->getTaxRateMock([
            'id' => null,
            'tax_country_id' => $countryCode,
            'tax_region_id' => $regionId,
            'region_name' => null,
            'tax_postcode' => null,
            'zip_is_range' => true,
            'zip_from' => 90000,
            'zip_to' => 90005,
            'rate' => 7.5,
            'code' => 'Tax Rate Code',
            'titles' => $rateTitles,
        ]);
        $this->rateConverterMock->expects($this->once())->method('createTitleArrayFromServiceObject')
            ->with($rateMock)->will($this->returnValue($rateTitles));
        $this->rateResourceMock->expects($this->once())->method('save')->with($rateMock);
        $rateMock->expects($this->once())->method('saveTitles')->with($rateTitles);
        $this->rateRegistryMock->expects($this->once())->method('registerTaxRate')->with($rateMock);

        $this->model->save($rateMock);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No such entity with id 9999
     */
    public function testSaveThrowsExceptionIfTargetTaxRateDoesNotExist()
    {
        $rateTitles = [
            'Label 1',
            'Label 2',
        ];
        $rateId = 9999;
        $rateMock = $this->getTaxRateMock([
            'id' => $rateId,
            'tax_country_id' => 'US',
            'tax_region_id' => 1,
            'region_name' => null,
            'tax_postcode' => null,
            'zip_is_range' => true,
            'zip_from' => 90000,
            'zip_to' => 90005,
            'rate' => 7.5,
            'code' => 'Tax Rate Code',
            'titles' => $rateTitles,
        ]);
        $this->rateRegistryMock->expects($this->once())->method('retrieveTaxRate')->with($rateId)
            ->willThrowException(new \Exception('No such entity with id ' . $rateId));
        $this->rateResourceMock->expects($this->never())->method('save')->with($rateMock);
        $this->rateRegistryMock->expects($this->never())->method('registerTaxRate')->with($rateMock);

        $this->model->save($rateMock);
    }

    public function testGet()
    {
        $rateId = 1;
        $this->rateRegistryMock->expects($this->once())->method('retrieveTaxRate')->with($rateId);
        $this->model->get($rateId);
    }

    public function testDelete()
    {
        $rateMock = $this->getTaxRateMock(['id' => 1]);
        $this->rateResourceMock->expects($this->once())->method('delete')->with($rateMock);
        $this->model->delete($rateMock);
    }

    public function testDeleteById()
    {
        $rateId = 1;
        $rateMock = $this->getTaxRateMock(['id' => $rateId]);
        $this->rateRegistryMock->expects($this->once())->method('retrieveTaxRate')->with($rateId)
            ->will($this->returnValue($rateMock));
        $this->rateResourceMock->expects($this->once())->method('delete')->with($rateMock);
        $this->model->deleteById($rateId);
    }

    public function testGetList()
    {
        $searchCriteriaMock = $this->getMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $searchCriteriaMock = $this->getMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $rateMock = $this->getTaxRateMock([]);

        $objectManager = new ObjectManager($this);
        $items = [$rateMock];
        $collectionMock = $objectManager->getCollectionMock(
            \Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection::class,
            $items
        );
        $collectionMock->expects($this->once())->method('joinRegionTable');
        $collectionMock->expects($this->once())->method('getSize')->will($this->returnValue(count($items)));

        $this->rateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($rateMock));
        $rateMock->expects($this->any())->method('getCollection')->will($this->returnValue($collectionMock));

        $this->searchResultMock->expects($this->once())->method('setItems')->with($items)->willReturnSelf();
        $this->searchResultMock->expects($this->once())->method('setTotalCount')->with(count($items))
            ->willReturnSelf();
        $this->searchResultMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);
        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($this->searchResultMock);

        $this->joinProcessorMock->expects($this->once())->method('process')->with($collectionMock);

        $this->model->getList($searchCriteriaMock);
    }

    /**
     * Retrieve tax rate mock
     *
     * @param array $taxRateData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getTaxRateMock(array $taxRateData)
    {
        $taxRateMock = $this->getMock(\Magento\Tax\Model\Calculation\Rate::class, [], [], '', false);
        foreach ($taxRateData as $key => $value) {
            // convert key from snake case to upper case
            $taxRateMock->expects($this->any())
                ->method('get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))))
                ->will($this->returnValue($value));
        }

        return $taxRateMock;
    }

    /**
     * @dataProvider saveThrowsExceptionIfCannotSaveTitlesDataProvider
     * @param LocalizedException $expectedException
     * @param string $exceptionType
     * @param string $exceptionMessage
     * @throws LocalizedException
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function testSaveThrowsExceptionIfCannotSaveTitles($expectedException, $exceptionType, $exceptionMessage)
    {
        $countryCode = 'US';
        $countryMock = $this->getMock(\Magento\Directory\Model\Country::class, [], [], '', false);
        $countryMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $countryMock->expects($this->any())->method('loadByCode')->with($countryCode)->will($this->returnSelf());
        $this->countryFactoryMock->expects($this->once())->method('create')->will($this->returnValue($countryMock));

        $regionId = 2;
        $regionMock = $this->getMock(\Magento\Directory\Model\Region::class, [], [], '', false);
        $regionMock->expects($this->any())->method('getId')->will($this->returnValue($regionId));
        $regionMock->expects($this->any())->method('load')->with($regionId)->will($this->returnSelf());
        $this->regionFactoryMock->expects($this->once())->method('create')->will($this->returnValue($regionMock));

        $rateTitles = ['Label 1', 'Label 2'];
        $rateMock = $this->getTaxRateMock(
            [
                'id' => null,
                'tax_country_id' => $countryCode,
                'tax_region_id' => $regionId,
                'region_name' => null,
                'tax_postcode' => null,
                'zip_is_range' => true,
                'zip_from' => 90000,
                'zip_to' => 90005,
                'rate' => 7.5,
                'code' => 'Tax Rate Code',
                'titles' => $rateTitles,
            ]
        );
        $this->rateConverterMock->expects($this->once())->method('createTitleArrayFromServiceObject')
            ->with($rateMock)->will($this->returnValue($rateTitles));
        $this->rateResourceMock->expects($this->once())->method('save')->with($rateMock);
        $rateMock
            ->expects($this->once())
            ->method('saveTitles')
            ->with($rateTitles)
            ->willThrowException($expectedException);
        $this->rateRegistryMock->expects($this->never())->method('registerTaxRate')->with($rateMock);
        $this->setExpectedException($exceptionType, $exceptionMessage);
        $this->model->save($rateMock);
    }

    public function saveThrowsExceptionIfCannotSaveTitlesDataProvider()
    {
        return [
            'entity_already_exists' => [
                new AlreadyExistsException(__('Entity already exists')), \Magento\Framework\Exception\AlreadyExistsException::class,
                'Entity already exists'
            ],
            'cannot_save_title' => [
                new LocalizedException(__('Cannot save titles')), \Magento\Framework\Exception\LocalizedException::class,
                'Cannot save titles'
            ]
        ];
    }

    public function testGetListWhenFilterGroupExists()
    {
        $searchCriteriaMock = $this->getMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $objectManager = new ObjectManager($this);
        $rateMock = $this->getTaxRateMock([]);
        $items = [$rateMock];
        $collectionMock = $objectManager->getCollectionMock(
            \Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection::class,
            $items
        );
        $rateMock = $this->getTaxRateMock([]);
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);
        $collectionMock->expects($this->once())->method('joinRegionTable');
        $collectionMock->expects($this->once())->method('getSize')->will($this->returnValue(count($items)));

        $this->rateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($rateMock));
        $rateMock->expects($this->any())->method('getCollection')->will($this->returnValue($collectionMock));

        $this->searchResultMock->expects($this->once())->method('setItems')->with($items)->willReturnSelf();
        $this->searchResultMock->expects($this->once())->method('setTotalCount')->with(count($items))
            ->willReturnSelf();
        $this->searchResultMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($this->searchResultMock);

        $this->joinProcessorMock->expects($this->once())->method('process')->with($collectionMock);

        $this->model->getList($searchCriteriaMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage One or more input exceptions have occurred.
     */
    public function testValidate()
    {
        $regionId = 2;
        $rateTitles = ['Label 1', 'Label 2'];
        $regionMock = $this->getMock(\Magento\Directory\Model\Region::class, [], [], '', false);
        $regionMock->expects($this->any())->method('getId')->will($this->returnValue(''));
        $regionMock->expects($this->any())->method('load')->with($regionId)->will($this->returnSelf());
        $this->regionFactoryMock->expects($this->once())->method('create')->will($this->returnValue($regionMock));
        $rateMock = $this->getTaxRateMock(
            [
                'id' => null,
                'tax_country_id' => '',
                'tax_region_id' => $regionId,
                'region_name' => null,
                'tax_postcode' => null,
                'zip_is_range' => true,
                'zip_from' => -90000,
                'zip_to' => '',
                'rate' => '',
                'code' => '',
                'titles' => $rateTitles,
            ]
        );
        $this->model->save($rateMock);
    }
}
