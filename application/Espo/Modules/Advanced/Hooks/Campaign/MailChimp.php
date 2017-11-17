<?php


namespace Espo\Modules\Advanced\Hooks\Campaign;

use \Espo\ORM\Entity;

class MailChimp extends \Espo\Core\Hooks\Base
{
    public static $order = 9;

    public function beforeSave(Entity $entity)
    {
        if (!$entity->isNew() && $entity->isFieldChanged('mailChimpCampaignId')) {
            $entity->set('mailChimpManualSyncRun', false);
            //$entity->set('mailChimpLastSuccessfulUpdating', null);

            //$this->getEntityManager()->getRepository('MailChimpLogMarker')->resetMarkers($entity->id);
        }
    }
}

