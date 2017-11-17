<?php


namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\ORM\Entity;

class UpdateRelatedEntity extends BaseEntity
{
    protected function run(Entity $entity, array $actionData)
    {
        $link = $actionData['link'];

        $relatedEntity = $this->getRelatedEntity($entity, $link);

        if (!($relatedEntity instanceof \Espo\ORM\Entity)) {
            return;
        }

        $update = true;

        $relationDefs = $entity->getRelations();

        if (isset($relationDefs[$link]) && $relationDefs[$link]['type'] == 'belongsToParent' && !empty($actionData['parentEntity'])) {

            if ($actionData['parentEntity'] != $relatedEntity->getEntityType()) {
                $update = false;
            }
        }

        if ($update) {
            $this->fillData($relatedEntity, $actionData['fields']);
            return $this->getEntityManager()->saveEntity($relatedEntity);
        }

        return true;
    }

    /**
     * Get Related Entity
     *
     * @param  \Espo\ORM\Entity $entity
     * @param  string $link
     *
     * @return \Espo\ORM\Entity | null
     */
    protected function getRelatedEntity(Entity $entity, $link)
    {
        if (empty($link) || !$entity->hasRelation($link)) {
            return;
        }

        $relationDefs = $entity->getRelations();
        $linkDefs = $relationDefs[$link];

        $relatedEntity = null;

        switch ($linkDefs['type']) {
            case 'belongsToParent':
                $parentType = $entity->get($link . 'Type');
                $parentId = $entity->get($link . 'Id');
                if (!empty($parentType) && !empty($parentId)) {
                    try {
                        $relatedEntity = $this->getEntityManager()->getEntity($parentType, $parentId);
                    } catch (\Exception $e) {
                        $GLOBALS['log']->info('Workflow[UpdateRelatedEntity]: Cannot getRelatedEntity(), error: '. $e->getMessage());
                    }
                }
                break;

            default:
                try {
                    $relatedEntity = $entity->get($link);
                } catch (\Exception $e) {
                    $GLOBALS['log']->info('Workflow[UpdateRelatedEntity]: Cannot getRelatedEntity(), error: '. $e->getMessage());
                }
                break;
        }

        return $relatedEntity;
    }
}