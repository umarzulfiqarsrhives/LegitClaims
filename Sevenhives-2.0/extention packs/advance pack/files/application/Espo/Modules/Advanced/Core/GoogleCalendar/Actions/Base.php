<?php


namespace Espo\Modules\Advanced\Core\GoogleCalendar\Actions;

use \Espo\Core\Exceptions\Error;
use \Espo\Core\Exceptions\Forbidden;
use \Espo\Core\Exceptions\NotFound;

abstract class Base
{	
    protected $baseUrl = 'https://www.googleapis.com/calendar/v3/';    
    protected $userId;
    
    protected $configPath = 'data/google/config.json';
    
    protected $entityManager;
    protected $acl;
    protected $container;
    protected $metadata;
    
    protected $clientMap = array();
    
    public function __construct($container, $entityManager, $metadata, $config)
    {
        $this->entityManager = $entityManager;
        $this->metadata = $metadata;
        $this->config = $config;
        $this->container = $container;
    }
    
    protected function getMetadata()
    {
        return $this->metadata;
    }
    
    protected function getAcl()
    {
        return $this->acl;
    }
    
    protected function setAcl()
    {
        $user = $this->getEntityManager()->getEntity('User', $this->getUserId());
        
        $aclManagerClassName = '\\Espo\\Core\\AclManager';
        if (class_exists($aclManagerClassName)) {
            $aclManager = new $aclManagerClassName($this->getContainer());
            $this->acl = new \Espo\Core\Acl($aclManager, $user);
        } else {
            $this->acl = new \Espo\Core\Acl($user, $this->getConfig(), null, $this->getMetadata());
        }
    }
    
    protected function getEntityManager()
    {
        return $this->entityManager;
    }
    
    protected function getConfig()
    {
        return $this->config;
    }
    
    public function setUserId($userId)
    {
        $this->userId = $userId;
        $this->setAcl();
    }
    
    public function getUserId()
    {
        return $this->userId;
    }
    
    protected function getContainer()
    {
        return $this->container;
    }
    
    protected function getClient()
    {
        $factory = new \Espo\Core\ExternalAccount\ClientManager($this->getEntityManager(), $this->getMetadata(), $this->getConfig());
        
        return $factory->create('Google', $this->getUserId());
    }
    
}
