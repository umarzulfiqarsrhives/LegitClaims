<?php

 
namespace Espo\Modules\Advanced\Jobs;

use \Espo\Core\Exceptions;

class ScheduleReportSending extends \Espo\Core\Jobs\Base
{

    public function run()
    {
        try {
            $service = $this->getServiceFactory()->create('ReportSending'); 
            $service->scheduleEmailSending();
        } catch (\Exception $e) {
            $GLOBALS['log']->error('JOB Schedule Report Sending Error: ' . $e->getMessage());
        }
        return true;
    }
}
