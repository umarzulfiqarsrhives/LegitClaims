<?php


namespace Espo\Modules\Advanced\Hooks\TargetList;

use \Espo\ORM\Entity;

class MailChimp extends \Espo\Core\Hooks\Base
{
    public static $order = 9;

    public function beforeSave(Entity $entity)
    {
        if (!$entity->isNew() && $entity->isFieldChanged('mailChimpListId')) {
            $entity->set('mailChimpManualSyncRun', false);
            $entity->set('mailChimpLastSuccessfulUpdating', null);
        }
    }
    
}

