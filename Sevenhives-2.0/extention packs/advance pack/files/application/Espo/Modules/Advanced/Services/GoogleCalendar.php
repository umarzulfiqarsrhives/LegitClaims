<?php


namespace Espo\Modules\Advanced\Services;

use \Espo\ORM\Entity;

use \Espo\Core\Exceptions\Error;
use \Espo\Core\Exceptions\NotFound;
use \Espo\Core\Exceptions\Forbidden;

class GoogleCalendar extends \Espo\Services\Record
{

    protected function init()
    {
        $this->dependencies[] = 'language';
        $this->dependencies[] = 'container';
        $this->dependencies[] = 'acl';
    }

    protected function getLanguage()
    {
        return $this->injections['language'];
    }

    protected function getAcl()
    {
        return $this->injections['acl'];
    }

    protected function getContainer()
    {
        return $this->injections['container'];
    }

    public function usersCalendars(array $params = null)
    {
        $calendar = new \Espo\Modules\Advanced\Core\GoogleCalendar\Actions\Calendar($this->getContainer(), $this->getEntityManager(), $this->getMetadata(), $this->getConfig());

        $calendar->setUserId($this->getUser()->id);

        return $calendar->getCalendarList();
    }

    public function syncCalendar(Entity $calendar)
    {
        $externalAccount = $this->getEntityManager()->getEntity('ExternalAccount', 'Google__' . $calendar->get('userId'));
        $enabled = $externalAccount->get('enabled');

        if ($enabled) {
            $isConnected = $this->getServiceFactory()->create('ExternalAccount')->ping('Google', $calendar->get('userId'));
            if (! $isConnected) {
                //notify user
                return false;
            }

            $calendarAction = new \Espo\Modules\Advanced\Core\GoogleCalendar\Actions\Calendar($this->getContainer(), $this->getEntityManager(), $this->getMetadata(), $this->getConfig());
            $calendarAction->setUserId($calendar->get('userId'));
            $syncResult = $calendarAction->run($calendar, $externalAccount);

            return $syncResult;
        }

        return false;
    }
}
