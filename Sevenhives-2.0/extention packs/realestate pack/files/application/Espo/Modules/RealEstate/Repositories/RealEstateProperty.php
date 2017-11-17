<?php

namespace Espo\Modules\RealEstate\Repositories;

use \Espo\ORM\Entity;

class RealEstateProperty extends \Espo\Core\Templates\Repositories\Base
{
    public function beforeSave(Entity $entity, array $options)
    {
        $propertyType = $entity->get('type');

        switch ($propertyType) {
            case 'Apartment':
                $entity->set('floorCount', null);
                break;
            case 'Separate House':
                $entity->set('floor', null);
                break;
            case 'Office':
                $entity->set('bedroomCount', null);
                $entity->set('bathroomCount', null);
                $entity->set('floorCount', null);
            case 'Warehouse':
            case 'Retail':
            case 'Farm':
                $entity->set('floor', null);
                $entity->set('bedroomCount', null);
                $entity->set('bathroomCount', null);
                $entity->set('floorCount', null);
                break;
        }

        $name = '';
        if ($entity->get('addressStreet') || $entity->get('addressCity')) {
            if ($entity->get('addressStreet')) {
                $name .= str_replace("\n", ', ', $entity->get('addressStreet'));
            }
            if ($entity->get('addressCity')) {
                if ($name != '') {
                    $name .= ", ";
                }
                $name .= $entity->get('addressCity');
            }
        } else {
            $name = "unknown-address";
        }
        $entity->set('name', $name);


        return parent::beforeSave($entity, $options);
    }


}
