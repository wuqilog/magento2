<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor;

use Magento\Framework\Data\Collection\AbstractDb;

/**
 * @api
 */
interface CustomJoinInterface
{
    /**
     * Make custom joins to collection
     *
     * @param AbstractDb $collection
     * @return bool
     */
    public function apply(AbstractDb $collection);
}
