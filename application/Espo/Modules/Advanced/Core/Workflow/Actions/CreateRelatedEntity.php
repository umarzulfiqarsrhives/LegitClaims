<?php


namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use \Espo\ORM\Entity;

class CreateRelatedEntity extends CreateEntity
{
    protected function run(Entity $entity, array $actionData)
    {
        $entityManager = $this->getEntityManager();

        $linkEntityName = $this->getLinkEntityName($entity, $actionData['link']);

        if (!isset($linkEntityName)) {
            $GLOBALS['log']->error('Workflow\Actions\\'.$actionData['type'].': Cannot find an entity name of the link ['.$actionData['link'].'] in the entity ['.$entity->getEntityType().'].');
            return false;
        }

        $GLOBALS['log']->debug('Workflow\Actions\\'.$actionData['type'].': Start creating a new entity ['.$linkEntityName.'].');

        $newEntity = $entityManager->getEntity($linkEntityName);
        $this->fillData($newEntity, $actionData['fields']);
        $newEntityId = $entityManager->saveEntity($newEntity);

        if (!empty($newEntityId)) {

            $newEntity = $entityManager->getEntity($newEntity->getEntityType(), $newEntityId);

            $GLOBALS['log']->debug('Workflow\Actions\\'.$actionData['type'].': End creating a new entity ['.$newEntity->getEntityType().'] with ID ['.$newEntityId.'].');

            $GLOBALS['log']->debug('Workflow\Actions\\'.$actionData['type'].': Start relate entity ['.$entity->getEntityType().', '.$entity->id.'] with a new entity ['.$newEntity->getEntityType().', '.$newEntity->id.'] by link ['.$actionData['link'].'].');

            $entityManager->getRepository($entity->getEntityType())->relate($entity, $actionData['link'], $newEntity);

            $GLOBALS['log']->debug('Workflow\Actions\\'.$actionData['type'].': End relate entity ['.$entity->getEntityType().', '.$entity->id.'] with a new entity ['.$newEntity->getEntityType().', '.$newEntity->id.'] by link ['.$actionData['link'].'].');
        }

        return !empty($newEntityId) ? true: false;
    }

    /**
     * Get an Entity name of a link
     *
     * @param  \Espo\ORM\Entity $entity
     * @param  string $linkName
     *
     * @return string | null
     */
    protected function getLinkEntityName(Entity $entity, $linkName)
    {
        $linkEntity = $entity->get($linkName);
        if ($linkEntity instanceof Entity) {
            return $linkEntity->getEntityType();
        }

        if (!isset($linkEntityName) && $entity->hasRelation($linkName)) {
            $relations = $entity->getRelations();
            if (!empty($relations[$linkName]['entity'])) {
                return $relations[$linkName]['entity'];
            }
        }
    }
}
