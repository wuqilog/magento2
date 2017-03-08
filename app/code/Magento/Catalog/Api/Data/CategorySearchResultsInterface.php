<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface CategorySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get categories
     *
     * @return \Magento\Catalog\Api\Data\CategoryInterface[]
     */
    public function getItems();

    /**
     * Set categories
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
