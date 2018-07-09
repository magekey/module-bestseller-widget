<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\BestsellerWidget\Block;

use Magento\Widget\Block\BlockInterface;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

use MageKey\BestsellerWidget\Model\ResourceModel\BestsellersCollectionFactory;

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
     * @var ProductVisibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var array
     */
    protected $_items = [];

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param BestsellersCollectionFactory $bestsellersCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductVisibility $catalogProductVisibility
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        BestsellersCollectionFactory $bestsellersCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        ProductVisibility $catalogProductVisibility,
        array $data = []
    ) {
        $this->_bestsellersCollectionFactory = $bestsellersCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
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
     * Retrieve aggregation periods
     *
     * @return array
     */
    public function getAggregationPeriods()
    {
        return [
            'weekly' => 'day',
            'monthly' => 'month',
            'yearly' => 'year',
        ];
    }

    /**
     * Retrieve period labels
     *
     * @return array
     */
    public function getLabels()
    {
        return [
            'weekly' => __('Weekly'),
            'monthly' => __('Monthly'),
            'yearly' => __('Yearly'),
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
            foreach ($this->getAggregationPeriods() as $code => $period) {
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
            if ($collection && $collection->getSize()) {
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
            $productIds = $this->_getReportProductIds($period);
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
     * Retrieve report product ids
     *
     * @param string $period
     * @return array
     */
    protected function _getReportProductIds($period)
    {
        $aggregationPeriods = $this->getAggregationPeriods();
        $collection = $this->_bestsellersCollectionFactory->create()
            ->setPeriod(
                $aggregationPeriods[$period]
            )
            ->addStoreFilter(
                $this->_storeManager->getStore()->getId()
            );

        $intervals = $this->_getReportIntervals();
        if (isset($intervals[$period])) {
            $collection->setDateRange(
                $intervals[$period]->getStart()->format('Y-m-d'),
                $intervals[$period]->getEnd()->format('Y-m-d')
            );
        }

        $reportSelect = $collection
            ->loadSelect()
            ->getSelect();

        $connection = $collection->getConnection();
        $wrapperSelect = $connection->select()
            ->from(
                ['report' => $reportSelect],
                false
            )
            ->columns([
                'id' => new \Zend_Db_Expr('IF(linked.parent_id IS NULL, report.product_id, linked.parent_id)')
            ])
            ->joinLeft(
                ['linked' => $connection->getTableName('catalog_product_super_link')],
                'linked.product_id = report.product_id',
                false
            )
            ->limit(
                $this->getProductsCount()
            );

        $result = $connection->fetchCol($wrapperSelect);

        return array_unique($result);
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
            foreach ($this->getPeriods() as $code) {
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
        $collection = $this->_productCollectionFactory
            ->create()
            ->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter()
            ->addIdFilter($productIds);
        return $collection;
    }
}
