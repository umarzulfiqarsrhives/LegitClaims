<?php


namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\ORM\Entity;

class MakeFollowed extends BaseEntity
{
    protected function run(Entity $entity, array $actionData)
    {
        if (empty($actionData['userIdList'])) return;
        if (empty($actionData['whatToFollow'])) {
            $actionData['whatToFollow'] = 'targetEntity';
        }


        if (!is_array($actionData['userIdList'])) return;

        $userIdList = $actionData['userIdList'];

        $target = null;
        if ($actionData['whatToFollow'] == 'targetEntity') {
            $target = $entity;
        } else {
            $link = $actionData['whatToFollow'];
            $type = $this->getMetadata()->get('entityDefs.' . $entity->getEntityType() . '.links.' . $link . '.type');

            if (empty($type)) return;

            $idField = $link . 'Id';

            if ($type == 'belongsTo') {
                if (!$entity->get($idField)) return;
                $foreignEntityType = $this->getMetadata()->get('entityDefs.' . $entity->getEntityType() . '.links.' . $link . '.entity');
                if (empty($foreignEntityType)) return;
                $target = $this->getEntityManager()->getEntity($foreignEntityType, $entity->get($idField));
            } else if ($type == 'belongsToParent') {
                $typeField = $link . 'Type';
                if (!$entity->get($idField)) return;
                if (!$entity->get($typeField)) return;
                $target = $this->getEntityManager()->getEntity($entity->get($typeField), $entity->get($idField));
            }
            if (empty($target)) return;
        }

        $streamService = $this->getServiceFactory()->create('Stream');
        $streamService->followEntityMass($target, $userIdList);

        return true;
    }
}