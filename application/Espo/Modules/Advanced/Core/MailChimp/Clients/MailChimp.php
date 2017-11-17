<?php


namespace Espo\Modules\Advanced\Core\MailChimp\Clients;

use \Espo\Core\Exceptions\Error;
use \Espo\Core\Exceptions\Forbidden;
use \Espo\Core\Exceptions\NotFound;

use Espo\Modules\Advanced\Core\MailChimp\ExternalAccount\MailChimp\Client;

class MailChimp implements \Espo\Core\ExternalAccount\Clients\IClient
{	

    protected $client = null;
    protected $dc = null;
    protected $apiKey = null;

    protected $paramList = array(
        'apiKey',
        'dc',
    );
    protected $manager = null;

    public function __construct($client, array $params = array(), $manager = null)
    {
        $apiKey = $params['apiKey'];

        if (!empty($apiKey)) {
            list($params['apiKey'], $params['dc']) = explode('-', $apiKey);
        }

        $this->client = $client;
        $this->client->setAccessToken($params['apiKey']);
        $this->setParams($params);
        $this->manager = $manager;
    }

    public function getParam($name)
    {
        if (in_array($name, $this->paramList)) {
            return $this->$name;
        }
    }

    public function setParam($name, $value)
    {
        if (in_array($name, $this->paramList)) {
            $methodName = 'set' . ucfirst($name);
            if (method_exists($this->client, $methodName)) {
                $this->client->$methodName($value);
            }
            $this->$name = $value;
        }
    }

    public function setParams(array $params)
    {
        foreach ($this->paramList as $name) {
            if (!empty($params[$name])) {
                $this->setParam($name, $params[$name]);
            }
        }
    }

    public function baseRequest($url, $params = null, $httpMethod = Client::HTTP_METHOD_GET, $contentType = null, $authType = Client::TOKEN_TYPE_URI)
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

        if ($authType == 'basic') {
            $this->client->setAuthType(Client::AUTH_TYPE_URI);
            $this->client->setTokenType(Client::TOKEN_TYPE_BASIC);
        } else {
            $this->client->setAuthType(Client::AUTH_TYPE_AUTHORIZATION_BASIC);
            $this->client->setTokenType(Client::TOKEN_TYPE_URI);
        }
        $r = $this->client->request($url, $params, $httpMethod, $httpHeaders);
        $code = null;
        if (!empty($r['code'])) {
            $code = $r['code'];
        }
        if ($code >= 200 && $code < 300) {
            return $r['result'];
        }
        $error = isset($r['result']['error']) ? $r['result']['error'] : '';
        throw new Error("Error after requesting {$httpMethod} {$url}. " . $error, $code);
    }

    public function ping()
    {
        $url = $this->getPingUrl();

        try {
            $this->request($url);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function buildUrl($url)
    {
        if ($this->dc == '') {
            return false;
        }
        $baseUrl = "https://" . $this->dc . ".api.mailchimp.com/2.0/";

        return $baseUrl . trim($url, '\/') . '.json';
    }
    
    protected function getPingUrl()
    {
        return $this->buildUrl('helper/ping');
    }

    public function subcribe($listId, array $params)
    {
        $url = 'lists/subscribe';

        $defaultParams = array(
            'id' => $listId,
            'double_optin' => false,
            'update_existing' => true,
            'replace_interests' => false,
            'send_welcome' => false
        );
        $params = array_merge($defaultParams, $params);
        
        return $this->request($url, $params, 'POST');
    }

    public function updateMember($listId, array $params)
    {
        $url = 'lists/update-member';

        $params['id'] = $listId;
        $params['replace_interests'] = false;

        return $this->request($url, $params, 'POST');
    }

    public function batchSubcribe($listId, array $batchArray)
    {
        $url = 'lists/batch-subscribe';
        $params = array(
            'id' => $listId,
            'batch' => $batchArray,
            'double_optin' => false,
            'update_existing' => true,
            'replace_interests' => false
       );
       return $this->request($url, $params, 'POST');
    }

    public function unsubscribe($listId, $email, $delete = false)
    {
        $url = 'lists/unsubscribe';

        $params = array(
            'id' => $listId,
            'email' => array('email' => $email),
            'delete_member' => $delete,
            'send_goodbye' => false,
            'send_notify' => false,
        );
        $result = $this->request($url, $params, 'GET');

        return (!empty($result['complete']));
    }

    public function getOptedOutReport($campaignId, $page = 0, $limit = 25)
    {
        $url = 'reports/unsubscribes';

        $params = array(
            'cid' => $campaignId,
            'opts' => array(
                'start' => $page,
                'limit' => $limit
            )
        );
        return $this->request($url, $params, 'GET');
    }

    public function getSentReportAll($campaignId, $status = '', $page = 0, $limit = 25)
    {
        $url = 'reports/sent-to';

        $params = array(
            'cid' => $campaignId,
            'opts' => array(
                'start' => $page,
                'limit' => $limit
            )
        );
        if (!empty($status)) {
            $params['opts']['status'] = $status;
        }
        $result = $this->request($url, $params, 'GET');

        return $result;
    }
    
    public function getUnsubscribedMembersFromList($listId, $page = 0, $limit = 25)
    {
        $url = '/lists/members';
        
        $params = array(
            'id' => $listId,
            'status' => 'unsubscribed',
            'opts' => array(
                'start' => $page,
                'limit' => $limit
            ),
        );

        return $this->request($url, $params, 'GET');
    }
    
    public function createCampaign($type, $listId, $title, $subject, $fromEmail, $fromName, $toName, $content)
    {
        $url = '/campaigns/create';
        $params = array(
            "type" => $type,
            "options" => array (
                "list_id" => $listId,
                "subject" => $subject,
                "from_email" => $fromEmail,
                "from_name" => $fromName,
                "to_name" => $toName,
                "title" =>  $title
            )
        );
        
        if ($type == 'plaintext') {
           
            $params["content"]['text'] = $content;
        } else {
            $params["content"]['text'] = $content;
            $params["content"]['html'] = $content;
        }
        
        $result = $this->request($url, $params, 'POST');
        return $result;
    }

    public function createList($name, \StdClass $contact, \StdClass $campaignDefaults, $reminder) 
    {
        $params = new \StdClass();
        $params->name = $name;
        $params->contact = $contact;
        $params->campaign_defaults = $campaignDefaults;
        $params->permission_reminder = $reminder;
        $params->email_type_option = true;
        
        $url = "https://" . $this->dc . ".api.mailchimp.com/3.0/lists/";
        
        return $this->baseRequest($url, json_encode($params), 'POST', Client::CONTENT_TYPE_APPLICATION_JSON, 'basic');
    }

    public function getHardBouncedReport($campaignId, $page = 0, $limit = 25)
    {
        return $this->getSentReportAll($campaignId, 'hard', $page, $limit);
    }

    public function getSoftBouncedReport($campaignId, $page = 0, $limit = 25)
    {
        return $this->getSentReportAll($campaignId, 'soft', $page, $limit);
    }

    public function getSentReport($campaignId, $page = 0, $limit = 25)
    {
        //return $this->getSentReportAll($campaignId, 'sent', $page, $limit);
        return $this->getSentReportAll($campaignId, '', $page, $limit);
    }

    public function getMemberInfo($listId, $email)
    {
        $url = 'lists/member-info';

        $params = array(
            'id' => $listId,
            'emails' => array(array('email' => $email)),
        );
        return $this->request($url, $params, 'GET');
    }

    private function byPage($url, $params) 
    {
        return $this->request($url, $params, 'GET');
    }

    private function loadAll($url, $params)
    {
        $page = 0;
        $lists = array();

        while (true) {
            $params['start'] = $page;
            $res = $this->byPage($url, $params);

            if (is_array($res) && !empty($res['data'])) {
                $lists = array_merge($lists, $res['data']);
                $page++;
            } else {
                break;
            }
        }
        return $lists;
    }

    public function getLists($params)
    {
        $url = 'lists/list';

        $requestParams = $this->convertListParams($params);

        if (!isset($requestParams['start'])) {
            return $this->loadAll($url, $requestParams);
        } else {
            return $this->byPage($url, $requestParams);
        }
    }

    public function getListGroups($listId)
    {
        $url = 'lists/interest-groupings';

        $params['id'] = $listId;
        return $this->request($url, $params, 'GET');
    }

    public function getMemberActivity($id, $since = '') 
    {
        $params['id'] = $id;

        if (!empty($since)) {
            $params['since'] = $since;
        }

        $url = "https://" . $this->dc . ".api.mailchimp.com/export/1.0/campaignSubscriberActivity/";
        return $this->baseRequest($url, $params, 'GET');
    }

    public function getCampaigns($params)
    {
        $url = 'campaigns/list';

        $requestParams = $this->convertCampaignParams($params);
        $requestParams['filters']['type'] = "regular,plaintext,auto,absplit";
        if (!isset($requestParams['start'])) {
            return $this->loadAll($url, $requestParams);
        } else {
            return $this->byPage($url, $requestParams);
        }
    }

    public function getCampaignContent($id, $email)
    {
        $url = 'campaigns/content';
        $params = array(
            'cid' => $id,
        );
        return $this->request($url, $params, 'GET');
    }

    public function getMemberEmailContent($id, $email)
    {
        $url = 'campaigns/content';
        $params = array(
            'cid' => $id,
            'opts' => array(
                'email' => $email
            )
        );
        return $this->request($url, $params, 'GET');
    }

    public function request($url, $params, $method, $buildUrl = true)
    {
        if ($buildUrl) {
            $url = $this->buildUrl($url);
        }
        $result = $this->baseRequest($url, $params, $method);
        return $result;
    }

    public function getListVars($listIds = array())
    {
        $url = 'lists/merge-vars';
        $params = array(
            'id' => $listIds
        );

        return $this->request($url, $params, 'GET');
    }

    public function addVarToList($listId, $name, $description, array $options = array())
    {
        $url = 'lists/merge-var-add';

        $params = array(
            'id' => $listId,
            'tag' => $name,
            'name' => $description,
            'options' => $options
        );
        return $this->request($url, $params, 'POST');
    }

    private function convertParams($params, $nameField)
    {
        $result = array();

        if (is_array($params)) {
            
            if (isset($params['filters']) && is_array($params['filters'])) {
                $result['filters'] = $params['filters'];
            }
            if (isset($params['filter'])) {
                $result['filters'][$nameField] = $params['filter'];
            }

            $result['limit'] = (isset($params['maxSize']) && !empty($params['maxSize'])) ? $params['maxSize'] : 5;

            if (isset($params['page'])) {
                $result['start'] = $params['page'];
            } else if (isset($params['offset'])) {
                $result['start'] = floor($params['offset'] / $result['limit']);
            }

            $result['sort_field'] = $nameField;

            if (isset($params['asc'])) {
                $result['sort_dir'] = ($params['asc']) ? 'ASC' : 'DESC';
            }
        }
        return $result;
    }

    private function convertCampaignParams($params)
    {
        return $this->convertParams($params, 'title');
    }

    private function convertListParams($params)
    {
        return $this->convertParams($params, 'list_name');
    }

}
