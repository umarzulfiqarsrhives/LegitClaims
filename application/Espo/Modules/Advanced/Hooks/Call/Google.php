<?php


namespace Espo\Modules\Advanced\Hooks\Call;

use Espo\ORM\Entity;

class Google extends \Espo\Core\Hooks\Base
{
    public static $order = 9;

    public function beforeSave(Entity $entity)
    {
        if (!$entity->isNew() && $entity->isFieldChanged('assignedUserId') && $entity->get('googleCalendarEventId') !='') {
            
            $newEntity = $this->getEntityManager()->getEntity($entity->getEntityName());
            
            $copyFields = array(
                "name", 
                "assignedUserId", 
                "googleCalendarId", 
                "googleCalendarEventId",
                "dateStart",
                "dateEnd"    
            );
            foreach ($copyFields as $field) {
                $newEntity->set($field, $entity->getFetched($field));
            }
            
            $this->getEntityManager()->saveEntity($newEntity);
            $this->getEntityManager()->removeEntity($newEntity);
            
            $entity->set('googleCalendarEventId','');
            $entity->set('googleCalendarId','');
        }
        
        if (!$entity->isNew() && $entity->getFetched('googleCalendarEventId') == 'FAIL') {
            $entity->set('googleCalendarEventId','');
        }
    
    }

}
