<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\ModuleStatusCommand;
use Symfony\Component\Console\Tester\CommandTester;

class ModuleStatusCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $objectManagerProvider = $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, [], [], '', false);
        $objectManager = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerProvider->expects($this->any())
            ->method('get')
            ->will($this->returnValue($objectManager));
        $moduleList = $this->getMock(\Magento\Framework\Module\ModuleList::class, [], [], '', false);
        $fullModuleList = $this->getMock(\Magento\Framework\Module\FullModuleList::class, [], [], '', false);
        $objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap([
                [\Magento\Framework\Module\ModuleList::class, [], $moduleList],
                [\Magento\Framework\Module\FullModuleList::class, [], $fullModuleList],
            ]));
        $moduleList->expects($this->any())
            ->method('getNames')
            ->will($this->returnValue(['Magento_Module1', 'Magento_Module2']));
        $fullModuleList->expects($this->any())
            ->method('getNames')
            ->will($this->returnValue(['Magento_Module1', 'Magento_Module2', 'Magento_Module3']));
        $commandTester = new CommandTester(new ModuleStatusCommand($objectManagerProvider));
        $commandTester->execute([]);
        $this->assertStringMatchesFormat(
            'List of enabled modules%aMagento_Module1%aMagento_Module2%a'
            . 'List of disabled modules%aMagento_Module3%a',
            $commandTester->getDisplay()
        );
    }
}
