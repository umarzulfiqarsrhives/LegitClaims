<?php


namespace Espo\Modules\Advanced\Jobs;

use \Espo\Core\Exceptions;

class SynchronizeEventsWithGoogleCalendar extends \Espo\Core\Jobs\Base
{

    public function run()
    {
        $integrationEntity = $this->getEntityManager()->getEntity('Integration', 'Google');

        if ($integrationEntity && $integrationEntity->get('enabled')) {

            $service = $this->getServiceFactory()->create('GoogleCalendar'); 
            $collection = $this->getEntityManager()->getRepository('GoogleCalendarUser')->where(array('active' => true))->find(array('orderBy' => 'lastLooked'));
           
            foreach ($collection as $calendar) {
                try {
                    $service->syncCalendar($calendar);
                } catch (\Exception $e) {
                    $GLOBALS['log']->error('GoogleCalendarERROR: Run Sync Error: ' . $e->getMessage());
                }
            }
        }
        
        return true;
    }
}
