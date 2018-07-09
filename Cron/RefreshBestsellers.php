<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\BestsellerWidget\Cron;

use MageKey\BestsellerWidget\Model\BestsellersReport;

class RefreshBestsellers
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
    }

    /**
     * Delete all product flat tables for not existing stores
     *
     * @return void
     */
    public function execute()
    {
        $this->bestsellersReport->refresh();
    }
}
