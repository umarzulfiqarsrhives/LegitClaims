<?php


namespace Espo\Modules\Advanced\Repositories;

use Espo\ORM\Entity;

class MailChimpLogMarker extends \Espo\Core\ORM\Repositories\RDB
{
    protected $allowedTypes = array('Sent', 'Hard Bounced', 'Soft Bounced', 'Opted Out', 'MemberActivity');
    
    public function findMarker($campaignId, $markerType, $createIfEmpty = true)
    {
        if (in_array($markerType, $this->allowedTypes)) {
            $marker = $this->where(array(
                    'mcCampaignId' => $campaignId,
                    'type' => $markerType
                ))->findOne();
            
            if (empty($marker)) {
                if (!$createIfEmpty) {
                    return false;
                }
                $marker = $this->getEntityManager()->getEntity("MailChimpLogMarker");
                $marker->set('mcCampaignId', $campaignId);
                $marker->set('type', $markerType);
                $this->getEntityManager()->saveEntity($marker);
            }
            
            return $marker;
        }
    }
    
    public function resetMarkers($campaignId)
    {
        foreach ($this->allowedTypes as $type) {
            $marker = $this->findMarker($campaignId, $type, false);
            if (!empty($marker)) {
                $this->getEntityManager()->removeEntity($marker);
            }
        }
    }
}

