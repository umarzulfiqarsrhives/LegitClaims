<?php


namespace Espo\Modules\Advanced\Services;

use \Espo\ORM\Entity;

class Opportunity extends \Espo\Modules\Crm\Services\Opportunity
{

    public function loadAdditionalFields(Entity $entity)
    {
        parent::loadAdditionalFields($entity);

        $itemList = $this->getEntityManager()->getRepository('OpportunityItem')->where(array(
            'opportunityId' => $entity->id
        ))->order('order')->find();

        $entity->set('itemList', $itemList->toArray());
    }
}

