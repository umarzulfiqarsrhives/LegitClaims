<?php


namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\Modules\Advanced\Core\Workflow\Utils;

use Espo\ORM\Entity;

class CreateEntity extends BaseEntity
{
    protected function run(Entity $entity, array $actionData)
    {
        $entityManager = $this->getEntityManager();

        $linkEntityName = $actionData['link'];

        $GLOBALS['log']->debug('Workflow\Actions\\'.$actionData['type'].': Start creating a new entity ['.$linkEntityName.'].');

        $newEntity = $entityManager->getEntity($linkEntityName);
        $this->fillData($newEntity, $actionData['fields']);
        $result = $entityManager->saveEntity($newEntity);

        $GLOBALS['log']->debug('Workflow\Actions\\'.$actionData['type'].': End creating a new entity ['.$linkEntityName.', '.$newEntity->id.'].');

        return $result;
    }
}
