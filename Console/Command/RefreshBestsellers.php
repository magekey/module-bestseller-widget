<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\BestsellerWidget\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use MageKey\BestsellerWidget\Model\BestsellersReport;

class RefreshBestsellers extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var BestsellersReport
     */
    protected $bestsellersReport;

    /**
     * @param BestsellersReport $bestsellersReport
     */
    public function __construct(
        BestsellersReport $bestsellersReport
    ) {
        $this->bestsellersReport = $bestsellersReport;
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
            $this->bestsellersReport->refresh();
            $output->writeln("<info>You refreshed bestsellers statistics.</info>");
        } catch (\Exception $e) {
            $output->writeln("<error>" . $e->getMessage() . "</error>");
        }
    }
}
