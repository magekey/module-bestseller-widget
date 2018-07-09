<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\BestsellerWidget\Model\ResourceModel;

class BestsellersCollection extends \Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection
{
    /**
     * Load select
     *
     * @return  $this
     */
    public function loadSelect()
    {
        $this->_beforeLoad();
        $this->_renderFilters()->_renderOrders()->_renderLimit();

        return $this;
    }
}
