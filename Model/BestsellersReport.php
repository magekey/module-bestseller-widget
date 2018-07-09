<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\BestsellerWidget\Model;

class BestsellersReport
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var string
     */
    protected $reportType;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $reportType
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $reportType
    ) {
        $this->_objectManager = $objectManager;
        $this->reportType = $reportType;
    }

    /**
     * Refresh bestsellers statistics
     *
     * @return void
     * @throws \Exception
     */
    public function refresh()
    {
        $this->_objectManager
            ->create($this->reportType)
            ->aggregate();
    }
}
