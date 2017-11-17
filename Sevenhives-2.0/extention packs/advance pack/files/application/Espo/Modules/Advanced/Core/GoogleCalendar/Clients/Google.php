<?php


namespace Espo\Modules\Advanced\Core\GoogleCalendar\Clients;

use \Espo\Core\Exceptions\Error;
use \Espo\Core\Exceptions\Forbidden;
use \Espo\Core\Exceptions\NotFound;
use \Espo\Core\Exceptions\BadRequest;

use \Espo\Core\ExternalAccount\OAuth2\Client;

class Google extends \Espo\Core\ExternalAccount\Clients\OAuth2Abstract
{	
    protected $baseUrl = 'https://www.googleapis.com/calendar/v3/';
    
    //copied from parent class
    public function request($url, $params = null, $httpMethod = Client::HTTP_METHOD_GET, $contentType = null, $allowRenew = true)
    {
        $httpHeaders = array();
        if (!empty($contentType)) {
            $httpHeaders['Content-Type'] = $contentType;
            switch ($contentType) {
                case Client::CONTENT_TYPE_MULTIPART_FORM_DATA:
                    $httpHeaders['Content-Length'] = strlen($params);
                    break;
                case Client::CONTENT_TYPE_APPLICATION_JSON:
                    $httpHeaders['Content-Length'] = strlen($params);
                    break;
            }
        }

        $r = $this->client->request($url, $params, $httpMethod, $httpHeaders);

        $code = null;
        if (!empty($r['code'])) {
            $code = $r['code'];
        }
        // added successful statuses
        if ($code >= 200 && $code < 300) {
            return $r['result'];
        } else {
            $handledData = $this->handleErrorResponse($r);

            if ($allowRenew && is_array($handledData)) {
                if ($handledData['action'] == 'refreshToken') {
                    if ($this->refreshToken()) {
                        return $this->request($url, $params, $httpMethod, $contentType, false);
                    }
                } else if ($handledData['action'] == 'renew') {
                    return $this->request($url, $params, $httpMethod, $contentType, false);
                }
            }
        }

        throw new Error("Error after requesting {$httpMethod} {$url}.", $code);
    }
    // end copy
    
    protected function getPingUrl()
    {
        return 'https://www.googleapis.com/calendar/v3/users/me/calendarList';
    }
    
    public function getCalendarList($params = array())
    {
        $method = 'GET';
        
        $url = $this->baseUrl . 'users/me/calendarList';
        
        $defaultParams = array(
            'maxResults' => 50,
            'minAccessRole' => 'owner'
        );
        
        $params = array_merge($defaultParams, $params);
        
        try {
            return $this->request($url, $params, $method);
        } catch (\Exception $e) {
            $GLOBALS['log']->error('GoogleCalendarERROR: ' . $e->getMessage());
            return array();
        }
    }
    
    public function getCalendarInfo($calendarId)
    {
        $method = 'GET';
        $url = $this->baseUrl . 'calendars/' . $calendarId;
        
        try {
            return $this->request($url, null, $method);
        } catch (\Exception $e) {
            $GLOBALS['log']->error('GoogleCalendarERROR: ' . $e->getMessage());
            return false;
        }
    }
    
    public function getEventList($calendarId, $params = array())
    {
        $method = 'GET';
        
        $url = $this->baseUrl . 'calendars/' . $calendarId . '/events';
        
        $defaultParams = array(
            'maxResults' => 10,
            'alwaysIncludeEmail' => 'true',
        );
        
        $params = array_merge($defaultParams, $params);
        
        try {
            return $this->request($url, $params, $method);
        
        } catch (\Exception $e) {
            $result = array('success' => false);
            
            if ($e->getCode() == 400 || $e->getCode() == 410) {
                $result['action'] = 'resetToken';
            }
            
            $GLOBALS['log']->error('GoogleCalendarERROR: ' . $e->getMessage());
            $paramsStr = print_r($params, true);
            $GLOBALS['log']->error('GoogleCalendarERROR: Params: ' . $paramsStr);
            
            return $result;
        }
    }
    
    public function getEventInstances($calendarId, $eventId, $params = array())
    {
        $method = 'GET';
        
        $url = $this->baseUrl . 'calendars/' . $calendarId . '/events/' . $eventId .'/instances';
        
        $defaultParams = array(
            'maxResults' => 10,
            'alwaysIncludeEmail' => 'true',
        );
        
        $params = array_merge($defaultParams, $params);
        
        try {
            return $this->request($url, $params, $method);
        
        } catch (\Exception $e) {
            $result = array('success' => false);
            
            if ($e->getCode() == 400 || $e->getCode() == 410) {
                $result['action'] = 'resetToken';
            } else if ($e->getCode() == 403 || $e->getCode() == 404) {
                $result['action'] = 'deleteEvent';
            }
            
            $GLOBALS['log']->error('GoogleCalendarERROR: ' . $e->getMessage());
            $paramsStr = print_r($params, true);
            $GLOBALS['log']->error('GoogleCalendarERROR: Params: ' . $paramsStr);
            
            return $result;
        }
        
    }
    
    public function deleteEvent($calendarId, $eventId)
    {
        $method = 'DELETE';
        $url = $this->baseUrl . 'calendars/' . $calendarId . '/events/' . $eventId;
        try {
            $this->request($url, null, $method);
        } catch (\Exception $e) {
            $GLOBALS['log']->error("GoogleCalendarERROR:" . $e->getMessage());
            return false;
        }
        return true;
    }
    
    public function retrieveEvent($calendarId, $eventId)
    {
        $method = 'GET';
        $url = $this->baseUrl . 'calendars/' . $calendarId . '/events/' . $eventId;
        
        try {
            return $this->request($url, array(), $method);
        } catch (\Exception $e) {
            $GLOBALS['log']->error("GoogleCalendarERROR:" . $e->getMessage());
            return false;
        }
    }
    
    public function insertEvent($calendarId, $event)
    {
        
        $method = 'POST';
        
        $url = $this->baseUrl . 'calendars/' . $calendarId . '/events';
        
        try {
            return $this->request($url, json_encode($event), $method, 'application/json');
        } catch (\Exception $e) {
            
            $GLOBALS['log']->error('GoogleCalendarERROR: ' . $e->getMessage());
            $paramsStr = print_r($event, true);
            $GLOBALS['log']->error('GoogleCalendarERROR: Params: ' . $paramsStr);
            return false;
        }
        
    }
    
    public function updateEvent($calendarId, $eventId, $modification)
    {
        $method = 'PUT';
        $url = $this->baseUrl . 'calendars/' . $calendarId . '/events/' . $eventId;
        
        try {
            return $this->request($url, json_encode($modification), $method, 'application/json');
        } catch (\Exception $e) {
           
            $GLOBALS['log']->error('GoogleCalendarERROR: ' . $e->getMessage());
            $paramsStr = print_r($modification, true);
            $GLOBALS['log']->error('GoogleCalendarERROR: Params: ' . $paramsStr);
            return false;
        }
        
    }
}
