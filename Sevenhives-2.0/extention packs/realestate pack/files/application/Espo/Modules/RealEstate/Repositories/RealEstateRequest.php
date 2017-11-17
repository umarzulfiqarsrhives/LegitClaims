<?php

namespace Espo\Modules\RealEstate\Repositories;

use \Espo\ORM\Entity;

class RealEstateRequest extends \Espo\Core\Templates\Repositories\Base
{
    public function beforeSave(Entity $entity, array $options)
    {
        $propertyType = $entity->get('propertyType');

        switch ($propertyType) {
            case 'Apartment':
                $entity->set('fromFloorCount', null);
                $entity->set('toFloorCount', null);
                break;
            case 'Separate House':
                $entity->set('fromFloor', null);
                $entity->set('toFloor', null);
                break;
            case 'Office':
                $entity->set('fromBedroomCount', null);
                $entity->set('toBedroomCount', null);
                $entity->set('fromBathroomCount', null);
                $entity->set('toBathroomCount', null);
                $entity->set('fromFloorCount', null);
                $entity->set('toFloorCount', null);
            case 'Warehouse':
            case 'Retail':
            case 'Farm':
                $entity->set('fromFloor', null);
                $entity->set('toFloor', null);
                $entity->set('fromBedroomCount', null);
                $entity->set('toBedroomCount', null);
                $entity->set('fromBathroomCount', null);
                $entity->set('toBathroomCount', null);
                $entity->set('fromFloorCount', null);
                $entity->set('toFloorCount', null);
                break;
        }

        return parent::beforeSave($entity, $options);
    }

    public function afterSave(Entity $entity, array $options)
    {
        $result = parent::afterSave($entity, $options);
        $this->handleAfterSaveContacts($entity, $options);

        if ($entity->isNew() && !$entity->get('name')) {

            $e = $this->get($entity->id);
            $name = strval($e->get('number'));
            $name = str_pad($name, 6, '0', STR_PAD_LEFT);
            $name = 'R ' . $name;

            $e->set('name', $name);
            $this->save($e);
            $entity->set('name', $name);
            $entity->set('number', $e->get('number'));
        }

        return $result;
    }

    protected function handleAfterSaveContacts(Entity $entity, array $options)
    {
        $contactIdChanged = $entity->has('contactId') && $entity->get('contactId') != $entity->getFetched('contactId');

        if ($contactIdChanged) {
            $contactId = $entity->get('contactId');
            if (empty($contactId)) {
                $this->unrelate($entity, 'contacts', $entity->getFetched('contactId'));
                return;
            }
        }

        if ($contactIdChanged) {
            $pdo = $this->getEntityManager()->getPDO();

            $sql = "
                SELECT id FROM contact_real_estate_request
                WHERE
                    contact_id = ".$pdo->quote($contactId)." AND
                    real_estate_request_id = ".$pdo->quote($entity->id)." AND
                    deleted = 0
            ";
            $sth = $pdo->prepare($sql);
            $sth->execute();

            if (!$sth->fetch()) {
                $this->relate($entity, 'contacts', $contactId);
            }
        }
    }
}
