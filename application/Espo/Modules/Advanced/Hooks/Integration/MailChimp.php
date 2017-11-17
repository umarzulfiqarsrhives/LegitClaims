<?php


namespace Espo\Modules\Advanced\Hooks\Integration;

use Espo\ORM\Entity;

class MailChimp extends \Espo\Core\Hooks\Base
{
    public static $order = 20;

    protected function init()
    {
        $this->dependencies[] = 'metadata';
    }

    protected function getMetadata()
    {
        return $this->getInjection('metadata');
    }

    public function afterSave(Entity $entity)
    {
        if ($entity->id != 'MailChimp' || $entity->getFetched('enabled') == $entity->get('enabled')) {
            return;
        }

        $metadata = $this->getMetadata();
        $data = array(
            'mailChimpNotification' => array(
                'disabled' => ! ((bool) $entity->get('enabled')),
            ),
        );

        $metadata->set('app', 'popupNotifications', $data);
        
        $metadata->save();
    }
}

