<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command;

use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\Util\Protocol\CurlTransport;

/**
 * Perform bin/magento commands from command line for functional tests executions.
 */
class Cli
{
    /**
     * Url to command.php.
     */
    const URL = 'dev/tests/functional/utils/command.php';

    /**
     * Curl transport protocol.
     *
     * @var CurlTransport
     */
    private $transport;

    /**
     * @constructor
     * @param CurlTransport $transport
     */
    public function __construct(CurlTransport $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Run command.
     *
     * @param string $command
     * @param array $options [optional]
     * @return void
     */
    protected function execute($command, $options = [])
    {
        $curl = $this->transport;
        $curl->write($this->prepareUrl($command, $options), [], CurlInterface::GET);
        $curl->read();
        $curl->close();
    }

    /**
     * Prepare url.
     *
     * @param string $command
     * @param array $options
     * @return string
     */
    private function prepareUrl($command, array $options)
    {
        $command .= ' ' . implode(' ', $options);
        return $_ENV['app_frontend_url'] . Cli::URL . '?command=' . urlencode($command);
    }
}
