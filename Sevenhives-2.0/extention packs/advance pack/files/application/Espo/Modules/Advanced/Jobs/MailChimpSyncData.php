<?php


namespace Espo\Modules\Advanced\Jobs;

use \Espo\Core\Exceptions;

class MailChimpSyncData extends \Espo\Core\Jobs\Base
{

    public function run()
    {
       $integrationEntity = $this->getEntityManager()->getEntity('Integration', 'MailChimp');

       if ($integrationEntity && $integrationEntity->get('enabled')) {

           $service = $this->getServiceFactory()->create('MailChimp'); 

         //  $service->updateMailChimpNames();
           $service->scheduleAllSync();
           //$service->scheduleTargetListsSync();
        }
        return true;
    }
}
