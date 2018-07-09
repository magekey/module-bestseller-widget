<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\BestsellerWidget\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshBestsellers extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var string
     */
    protected $bestsellersReportType;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $bestsellersReportType
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $bestsellersReportType
    ) {
        $this->_objectManager = $objectManager;
        $this->bestsellersReportType = $bestsellersReportType;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('statistics:bestsellers:refresh')
            ->setDescription('Refresh Bestsellers Statistics');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->_objectManager->create($this->bestsellersReportType)->aggregate();
            $output->writeln("<info>You refreshed bestsellers statistics.</info>");
        } catch (\Exception $e) {
            $output->writeln("<error>" . $e->getMessage() . "</error>");
        }
    }
}
