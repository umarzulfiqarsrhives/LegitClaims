<?php


namespace Espo\Modules\Advanced\Core\MailChimp;

use \Espo\Core\Exceptions\Error;
use \Espo\Core\Exceptions\Forbidden;
use \Espo\Core\Exceptions\NotFound;

use \Espo\Core\ExternalAccount\OAuth2\Client;

class ClientManager extends \Espo\Core\ExternalAccount\ClientManager
{
    protected function createMailChimp($integration, $userId = null)
    {
        $integrationEntity = $this->getEntityManager()->getEntity('Integration', $integration);

        $className = $this->getMetadata()->get("integrations.{$integration}.clientClassName");

        if (!$integrationEntity->get('enabled')) {
            return null;
        }

        $mcClient = new \Espo\Modules\Advanced\Core\MailChimp\ExternalAccount\MailChimp\Client();
        $params = array();
        
        $integrationParams = $this->getMetadata()->get("integrations.{$integration}.params");
        
        if (is_array($integrationParams)) {
            $params = $integrationParams;
        }
        
        $integrationFields = $this->getMetadata()->get("integrations.{$integration}.fields");
        
        if (is_array($integrationFields)) {
            foreach ($integrationFields as $field => $fieldParams) {
                $params[$field] = $integrationEntity->get($field);
            }
        }
        $client = new $className($mcClient, $params, $this);

        $this->addToClientMap($client, $integrationEntity, null, null);

        return $client;
    }
}
