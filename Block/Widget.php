<?php
/**
 * Copyright © MageKey. All rights reserved.
 */
namespace MageKey\BestsellerWidget\Block;

use Magento\Widget\Block\BlockInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection as BestsellersCollection;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestsellersCollectionFactory;

class Widget extends \Magento\Catalog\Block\Product\AbstractProduct implements BlockInterface
{
    /**
     * Default value for products count that will be shown
     */
    const DEFAULT_PRODUCTS_COUNT = 4;

    /**
     * @var BestsellersCollectionFactory
     */
    protected $_bestsellersCollectionFactory;

    /**
     * @var ProductCollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var array
     */
    protected $_items = [];

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param BestsellersCollectionFactory $bestsellersCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        BestsellersCollectionFactory $bestsellersCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->_bestsellersCollectionFactory = $bestsellersCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Get value of widgets' title parameter
     *
     * @return mixed|string
     */
    public function getTitle()
    {
        return $this->getData('title');
    }

    /**
     * Retrieve available periods
     *
     * @return array
     */
    public function getAvailablePeriods()
    {
        return [
            'weekly' => __('Weekly'),
            'monthly' => __('Monthly'),
            'yearly' => __('Yearly')
        ];
    }

    /**
     * Retrieve periods
     *
     * @return array
     */
    public function getPeriods()
    {
        if (!$this->hasData('periods')) {
            $periods = [];
            foreach ($this->getAvailablePeriods() as $code => $title) {
                if ($this->getData($code)) {
                    $periods[] = $code;
                }
            }
            $this->setData('periods', $periods);
        }
        return $this->getData('periods');
    }

    /**
     * Retrieve how many products should be displayed
     *
     * @return int
     */
    public function getProductsCount()
    {
        if ($this->hasData('products_count')) {
            return $this->getData('products_count');
        }

        if (null === $this->getData('products_count')) {
            $this->setData('products_count', self::DEFAULT_PRODUCTS_COUNT);
        }

        return $this->getData('products_count');
    }

    /**
     * Retrieve items count
     *
     * @return int
     */
    public function getItemsCount()
    {
        $counter = 0;
        foreach ($this->getPeriods() as $period) {
            $collection = $this->getItemsCollection($period);
            if ($collection && $collection->count()) {
                $counter++;
            }
        }
        return $counter;
    }

    /**
     * Retrieve items collection
     *
     * @param string $period
     * @return ProductCollection|null
     */
    public function getItemsCollection($period)
    {
        if (!array_key_exists($period, $this->_items)) {
            $reportCollection = $this->_createReportCollection($period);
            $productIds = [];
            foreach ($reportCollection as $item) {
                $productIds[] = $item->getProductId();
            }
            if (!empty($productIds)) {
                $productCollection = $this->_createProductCollection($productIds);
            } else {
                $productCollection = null;
            }
            $this->_items[$period] = $productCollection;
        }
        return $this->_items[$period];
    }

    /**
     * Create report collection
     *
     * @param string $period
     * @return BestsellersCollection
     */
    protected function _createReportCollection($period)
    {
        $collection = $this->_bestsellersCollectionFactory->create()
            ->setPeriod(
                $period
            )
            ->addStoreFilter(
                $this->_storeManager->getStore()->getId()
            )
            ->setPageSize(
                $this->getProductsCount()
            );
        $intervals = $this->_getReportIntervals();
        if (isset($intervals[$period])) {
            $collection->setDateRange(
                $intervals[$period]->getStart()->format('Y-m-d'),
                $intervals[$period]->getEnd()->format('Y-m-d')
            );
        }
        return $collection;
    }

    /**
     * Get report intervals
     *
     * @return \Magento\Framework\DataObject
     */
    protected function _getReportIntervals()
    {
        if (!$this->hasData('report_intervals')) {
            $intervals = [];
            foreach ($this->getPeriods() as $code => $title) {
                switch ($code) {
                    case 'weekly':
                        $interval = [
                            'start' => $this->_localeDate->scopeDate(null, 'monday this week', false),
                            'end' => $this->_localeDate->scopeDate(null, 'sunday this week', false),
                        ];
                        break;
                    case 'monthly':
                        $interval = [
                            'start' => $this->_localeDate->scopeDate(null, 'first day of this month', false),
                            'end' => $this->_localeDate->scopeDate(null, 'last day of this month', false),
                        ];
                        break;
                    case 'yearly':
                    $interval = [
                        'start' => $this->_localeDate->scopeDate(null, 'first day of this year', false),
                        'end' => $this->_localeDate->scopeDate(null, 'last day of this year', false),
                    ];
                        break;
                    default:
                        break 2;
                }
                $intervals[$code] = new \Magento\Framework\DataObject($interval);
            }
            $this->setData('report_intervals', $intervals);
        }
        return $this->getData('report_intervals');
    }

    /**
     * Create product collection
     *
     * @param array $productIds
     * @return ProductCollection
     */
    protected function _createProductCollection(array $productIds)
    {
        $collection = $this->_productCollectionFactory->create();
        $this->_addProductAttributesAndPrices(
            $collection
        )->addIdFilter($productIds);
        return $collection;
    }
}
