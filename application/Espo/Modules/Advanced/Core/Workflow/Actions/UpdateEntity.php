<?php


namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\ORM\Entity;

class UpdateEntity extends BaseEntity
{
    protected function run(Entity $entity, array $actionData)
    {
        $entityManager = $this->getEntityManager();

        $reloadedEntity = $entityManager->getEntity($entity->getEntityType(), $entity->id);

        $this->fillData($reloadedEntity, $actionData['fields']);

        if ($entity->isNew()) {
            $this->fillData($entity, $actionData['fields']); //fixed a bug when use updateEntity for a new record
        }

        $reloadedEntity->skipHooks = true;

        return $entityManager->saveEntity($reloadedEntity);
    }
}