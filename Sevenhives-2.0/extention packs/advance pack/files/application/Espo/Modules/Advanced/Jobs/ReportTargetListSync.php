<?php


namespace Espo\Modules\Advanced\Jobs;

use \Espo\Core\Exceptions;

class ReportTargetListSync extends \Espo\Core\Jobs\Base
{

    public function run()
    {
        $reportService = $this->getServiceFactory()->create('Report');

        $targetListList = $this->getEntityManager()->getRepository('TargetList')->where(array(
            'syncWithReportsEnabled' => true
        ))->find();

        foreach ($targetListList as $targetList) {
            try {
                $reportService->syncTargetListWithReports($targetList);
            } catch (\Exceptions $e) {
                $GLOBALS['log']->error('ReportTargetListSync: [' . $e->getCode() . '] ' .$e->getMessage());
            }
        }

        return true;
    }
}
