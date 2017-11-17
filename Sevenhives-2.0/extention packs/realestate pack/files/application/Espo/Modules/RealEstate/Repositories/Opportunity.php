<?php


namespace Espo\Modules\RealEstate\Repositories;

use \Espo\ORM\Entity;

class Opportunity extends \Espo\Modules\Crm\Repositories\Opportunity
{
    public function beforeSave(Entity $entity, array $options)
    {
        parent::beforeSave($entity, $options);

        if (
            $entity->has('closeDate') &&
            !$entity->get('closeDate') &&
            $entity->get('stage') == 'Closed Won' &&
            (
                $entity->isFieldChanged('stage') || $entity->isNew()
            )
        ) {
            $entity->set('closeDate', date('Y-m-d'));
        }

        if ($entity->get('requestId') && $entity->get('propertyId')) {
            $request = $this->getEntityManager()->getEntity('RealEstateRequest', $entity->get('requestId'));
            $property = $this->getEntityManager()->getEntity('RealEstateProperty', $entity->get('propertyId'));
            if ($request && $property) {
                $name = $property->get('name') . ' - ' . $request->get('name');
                $entity->set('name', $name);
            }
        }

        if ($entity->has('name') && !$entity->get('name')) {
            $entity->set('name', 'unnamed');
        }
    }

    public function afterSave(Entity $entity, array $options)
    {
        parent::afterSave($entity, $options);

        if (
            $entity->get('stage') == 'Closed Won' &&
            (
                $entity->isFieldChanged('stage') || $entity->isNew()
            )
        ) {
            if ($entity->get('requestId') && $entity->get('propertyId')) {
                $request = $this->getEntityManager()->getEntity('RealEstateRequest', $entity->get('requestId'));
                $request->set('status', 'Completed');
                $this->getEntityManager()->saveEntity($request);

                $property = $this->getEntityManager()->getEntity('RealEstateProperty', $entity->get('propertyId'));
                $property->set('status', 'Completed');
                $this->getEntityManager()->saveEntity($property);

                $opportunityList = $this->where(array(
                    'requestId' => $entity->get('requestId'),
                    'propertyId' => $entity->get('propertyId')
                ))->find();

                foreach ($opportunityList as $opportunity) {
                    if ($entity->id == $opportunity->id) continue;
                    $opportunity->set('stage', 'Closed Lost');
                    $opportunity->set('closeDate', date('Y-m-d'));
                    $this->save($opportunity);
                }
            }
        }

        if ($entity->isNew() && $entity->get('status') !== 'Closed Lost') {
            if ($entity->get('requestId') && $entity->get('propertyId')) {
                $note = $this->getEntityManager()->getEntity('Note');
                $note->set(array(
                    'type' => 'CreateRelated',
                    'parentId' => $entity->get('propertyId'),
                    'parentType' => 'RealEstateProperty',
                    'data' => array(
                        'action' => 'created',
                    ),
                    'relatedId' => $entity->id,
                    'relatedType' => 'Opportunity'
                ));
                $this->getEntityManager()->saveEntity($note);

                $note = $this->getEntityManager()->getEntity('Note');
                $note->set(array(
                    'type' => 'CreateRelated',
                    'parentId' => $entity->get('requestId'),
                    'parentType' => 'RealEstateRequest',
                    'data' => array(
                        'action' => 'created',
                    ),
                    'relatedId' => $entity->id,
                    'relatedType' => 'Opportunity'
                ));
                $this->getEntityManager()->saveEntity($note);
            }
        }

        if ($entity->isNew() || $entity->isFieldChanged('stage')) {
            if ($entity->get('requestId') && $entity->get('propertyId')) {
                $property = $this->getEntityManager()->getEntity('RealEstateProperty', $entity->get('propertyId'));
                if ($property) {
                    if ($entity->get('stage') !== 'Closed Lost') {
                        $this->getEntityManager()->getRepository('RealEstateProperty')->unrelate($property, 'requests', $entity->get('requestId'));
                    } else {
                        $this->getEntityManager()->getRepository('RealEstateProperty')->relate($property, 'requests', $entity->get('requestId'), array(
                            'interestDegree' => 0
                        ));
                    }
                }
            }
        }
    }
}

