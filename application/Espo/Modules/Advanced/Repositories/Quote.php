<?php


namespace Espo\Modules\Advanced\Repositories;

use Espo\ORM\Entity;

class Quote extends \Espo\Core\ORM\Repositories\RDB
{
    protected function beforeSave(Entity $entity, array $options)
    {
        parent::beforeSave($entity, $options);

        if (!$entity->get('accountId')) {
            $opportunityId = $entity->get('opportunityId');
            if ($opportunityId) {
                $opportunity = $this->getEntityManager()->getEntity('Opportunity', $opportunityId);
                if ($opportunity) {
                    $accountId = $opportunity->get('accountId');
                    if ($accountId) {
                        $entity->set('accountId', $accountId);
                    }
                }
            }
        }
    }
}

