<?php


namespace Espo\Modules\Advanced\Hooks\ExternalAccount;

use Espo\ORM\Entity;

class Google extends \Espo\Core\Hooks\Base
{
    public static $order = 9;

    public function afterSave(Entity $entity)
    {
        list($integration, $userId) = explode('__', $entity->id);
        
        if ($integration == 'Google') {
              
            $storedUsersCalendars = $this->getEntityManager()->getRepository('GoogleCalendar')->storedUsersCalendars($userId);
            $direction = $entity->get('calendarDirection');
            $monitoredCalendarIds = $entity->get('calendarMonitoredCalendarsIds');
            $monitoredCalendars = $entity->get('calendarMonitoredCalendarsNames');
            if (empty($monitoredCalendarIds)) {
                $monitoredCalendarIds = array();
            }
            $mainCalendarId = $entity->get('calendarMainCalendarId');
            $mainCalendarName = $entity->get('calendarMainCalendarName');
                
            if ($direction == "GCToEspo" && !in_array($mainCalendarId, $monitoredCalendarIds)) {
                $monitoredCalendarIds[] = $mainCalendarId;
                $monitoredCalendars->$mainCalendarId = $mainCalendarName;
            }
            
            foreach($monitoredCalendarIds as $calendarId) {
                
                $googleCalendar = $this->getEntityManager()->getRepository('GoogleCalendar')->getCalendarByGCId($calendarId, $userId);
                
                if (empty($googleCalendar)) {
                    
                    $googleCalendar = $this->getEntityManager()->getEntity('GoogleCalendar');
                    $googleCalendar->set('name', $monitoredCalendars->$calendarId);
                    $googleCalendar->set('calendarId', $calendarId);
                    $this->getEntityManager()->saveEntity($googleCalendar);
                }
                
                $id = $googleCalendar->id;
                
                if (isset($storedUsersCalendars['monitored'][$id])) {
                
                    if (!$storedUsersCalendars['monitored'][$id]['active']) {
                        
                        $calendarEntity = $this->getEntityManager()->getEntity('GoogleCalendarUser', $storedUsersCalendars['monitored'][$id]['id']);
                        $calendarEntity->set('active', true);
                        $this->getEntityManager()->saveEntity($calendarEntity);
                    
                    }
                
                } else {
                    $calendarEntity = $this->getEntityManager()->getEntity('GoogleCalendarUser');
                    $calendarEntity->set('userId', $userId);
                    $calendarEntity->set('type', 'monitored');
                    $calendarEntity->set('role', 'owner');
                    $calendarEntity->set('googleCalendarId', $id);
                    $this->getEntityManager()->saveEntity($calendarEntity);
                }
            }
            
            foreach($storedUsersCalendars['monitored'] as $id => $calendar) {
                if ($calendar['active'] && (!is_array($monitoredCalendarIds) || !in_array($calendar['calendar_id'], $monitoredCalendarIds))) {
                    $calendarEntity = $this->getEntityManager()->getEntity('GoogleCalendarUser', $calendar['id']);
                    $calendarEntity->set('active', false);
                    $this->getEntityManager()->saveEntity($calendarEntity);
                }
            }
            
            if ($direction == "GCToEspo") {
                $mainCalendarId = '';
                $mainCalendarName = array();
            }
            
            if (empty($mainCalendarId)) {
                foreach($storedUsersCalendars['main'] as $calendarId => $calendar) {
                    if ($calendar['active']) {
                        $calendarEntity = $this->getEntityManager()->getEntity('GoogleCalendarUser', $calendar['id']);
                        $calendarEntity->set('active', false);
                        $this->getEntityManager()->saveEntity($calendarEntity);
                    } 
                }
            } else {
                $googleCalendar = $this->getEntityManager()->getRepository('GoogleCalendar')->getCalendarByGCId($mainCalendarId, $userId);
                    
                if (empty($googleCalendar)) {
                    
                    $googleCalendar = $this->getEntityManager()->getEntity('GoogleCalendar');
                    $googleCalendar->set('name', $mainCalendarName);
                    $googleCalendar->set('calendarId', $mainCalendarId);
                    $this->getEntityManager()->saveEntity($googleCalendar);
                }
                
                $id = $googleCalendar->id;
                
                foreach($storedUsersCalendars['main'] as $calendarId => $calendar) {
                    
                    if ($calendar['active'] && $id != $calendarId) {
                        
                        $calendarEntity = $this->getEntityManager()->getEntity('GoogleCalendarUser', $calendar['id']);
                        $calendarEntity->set('active', false);
                        $this->getEntityManager()->saveEntity($calendarEntity);
                    
                    } else if (!$calendar['active'] && $id == $calendarId) {
                        
                        $calendarEntity = $this->getEntityManager()->getEntity('GoogleCalendarUser', $calendar['id']);
                        $calendarEntity->set('active', true);
                        $this->getEntityManager()->saveEntity($calendarEntity);
                    
                    }
                    
                }
                    
                if (!isset($storedUsersCalendars['main'][$id])) {
                   
                    $calendarEntity = $this->getEntityManager()->getEntity('GoogleCalendarUser');
                    $calendarEntity->set('userId', $userId);
                    $calendarEntity->set('type', 'main');
                    $calendarEntity->set('role', 'owner');
                    $calendarEntity->set('googleCalendarId', $id);
                    $this->getEntityManager()->saveEntity($calendarEntity);
                
                }
            }
        }
    }

    public function beforeSave(Entity $entity)
    {
        list($integration, $userId) = explode('__', $entity->id);
        
        if ($integration == 'Google') {
            
            $prevEntity = $this->getEntityManager()->getEntity('ExternalAccount', $entity->id);
            
            if (empty($prevEntity) ) {
                return false;
            }
            
            if ($prevEntity->get('calendarStartDate') > $entity->get('calendarStartDate')) {
                $googleCalendarUsers = $this->getEntityManager()->getRepository('GoogleCalendarUser')
                    ->where(array(
                        'active' => true, 
                        'userId' => $userId))
                    ->find();
                
                foreach ($googleCalendarUsers as $googleCalendarUser) {
                    $googleCalendarUser->set('pageToken', '');
                    $googleCalendarUser->set('syncToken', '');
                    $googleCalendarUser->set('lastSync', null);
                    $this->getEntityManager()->saveEntity($googleCalendarUser);
                }
            } 
        }
    }
}
