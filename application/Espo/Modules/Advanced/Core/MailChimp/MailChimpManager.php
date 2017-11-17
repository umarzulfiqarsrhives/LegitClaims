<?php


namespace Espo\Modules\Advanced\Core\MailChimp;

use Espo\ORM\Entity;

class MailChimpManager
{

    protected $config;

    protected $entityManager;

    protected $metadata;

    protected $clientMap = array();

    protected $client = null;

    public function __construct($entityManager, $metadata, $config)
    {
        $this->entityManager = $entityManager;
        $this->metadata = $metadata;
        $this->config = $config;
    }

    protected function getMetadata()
    {
        return $this->metadata;
    }

    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    protected function getConfig()
    {
        return $this->config;
    }

    protected function getContainer()
    {
        return $this->container;
    }

    protected function getClient($hardUpdate = false)
    {
        $client = $this->client;
        if ($hardUpdate || empty($client)) {
            $factory = new ClientManager($this->getEntityManager(), $this->getMetadata(), $this->getConfig());
            $this->client = $factory->create('MailChimp', null);
        }
        return $this->client;
    }

    public function getCampaigns()
    {
        $result = array();

        $client = $this->getClient();
        $list = $client->getCampaigns();

        foreach ($list as $elem) {
            $result[$elem['id']] = $elem['title'];
        }
        return $result;
    }

    public function getMemberActivity($campaignId, $since = '')
    {
        $client = $this->getClient();
        $result = $client->getMemberActivity($campaignId, $since);
        if (is_array($result)) {
            return array((object)$result);
        } else {
            $result = trim($result);
            $activities = (!empty($result)) ? explode("\n", $result) : '';
            return (!empty($activities)) ? array_map("json_decode", $activities) : false;
        }
    }

    public function getUnsubscribedMembers($listId)
    {
        $client = $this->getClient();
        $page = 0;
        $limit = 25;
        $result = array();
        $helper = new \Espo\Modules\Advanced\Core\MailChimp\RecipientHelper($this->getEntityManager(), $this->getMetadata());
        while (true) {
            $response = $client->getUnsubscribedMembersFromList($listId, $page, $limit);
            if (isset($response['data']) && count($response['data']) > 0) {
                foreach ($response['data'] as $data) {
                    $espoMember = $helper->recognizeMCMember($data);
                    if (!empty($espoMember)) {
                        $result[$data['email']] = $espoMember;
                    }
                }
                $page++;
            } else {
                break;
            }
        }
        return $result;
    }

    public function getCampaignsByOffset($params)
    {
        $result = array();

        $client = $this->getClient();
        $list = $client->getCampaigns($params);

        $result['total'] = (int) $list['total'];
        $result['list'] = array();
        foreach ($list['data'] as $elem) {
            $result['list'][] = $this->parseCampaign($elem);
        }

        return $result;
    }

    public function getListsByOffset($params)
    {
        $result = array();

        $client = $this->getClient();
        $list = $client->getLists($params);

        $result['total'] = (int) $list['total'];
        $result['list'] = array();
        foreach ($list['data'] as $elem) {
            $result['list'][] = array(
                'id' => $elem['id'], 
                'name' => $elem['name'],
                'subscribers' => $elem['stats']['member_count']);
        }

        return $result;
    }

    public function getListGroupsTree($listId)
    {
        $result = array();
        
        $client = $this->getClient();
        try {
            $list = $client->getListGroups($listId);
        } catch (\Exception $e) {
            $list = array();
        }

        $i = 1;        
        foreach ($list as $elem) {
            $grouping = new \StdClass();
            $grouping->id = $elem['id'];
            $grouping->name = $elem['name'];
            $grouping->order = $i;
            $grouping->childList = array();

            $i++;
            $j = 1;
            foreach ($elem['groups'] as $elemGroup) {
                $group = new \StdClass();
                $group->id = $elemGroup['id'];
                $group->name = $elemGroup['name'];
                $group->order = $j;
                $group->parentId = $elem['id'];
                $group->parentName = $elem['name'];
                $grouping->childList[] = $group;
                $j++;
            }
            $result[] = $grouping;
        }

        return $result;
    }
    
    public function getLists()
    {
        $result = array();

        $client = $this->getClient();
        $list = $client->getLists();

        foreach ($list as $elem) {
            $result[$elem['id']] = $elem['name'];
        }

        return $result;
    }

    public function getCampaign($id)
    {
        $client = $this->getClient();
        $params = array(
            'filters' => array('campaign_id' => $id),
        );
        $result = $client->getCampaigns($params);
        if (!empty($result) && count($result)) {
            return $this->parseCampaign($result[0]);
        }
        return false;
    }

    protected function parseCampaign($elem)
    {
        return array(
            'id' => $elem['id'], 
            'name' => $elem['title'],
            'type' => $elem['type'],
            'status' => $elem['status'],
            'webId' => $elem['web_id'],
            'dateSent' => $elem['send_time'],
            'listId' => $elem['list_id'],
            'fromName' => $elem['from_name'],
            'fromAddress' => $elem['from_email'],
            'subject' => $elem['subject']
        );
    }

    public function getReport($reportType, $campaignId, $page = 0, $skip = 0, $limit = 25)
    {
        $list = array();
        $total = 0;

        if (in_array($reportType, array("Sent", "Hard Bounced", "Soft Bounced", "Opted Out"))) {

            $methodName = "get" . str_replace(' ', '' ,$reportType) . "Report";
            $response = $this->getClient()->$methodName($campaignId, $page, $limit);
            $list = $this->getMembersFromReport($response, $skip);
            $total = (isset($response['data'])) ? count($response['data']) : 0;
        }
        return array (
            'list' => $list,
            'total' => $total,
        );
    }

    public function addEntityFieldsToList($listId)
    {
        $fieldsForAdd = array(
            'ESPNM' => array(
                'tag' => 'ESPNM',
                'name' => 'Espo Entity Name',
                'options' => array(
                    'field_type' => 'text',
                    'public' => false,
                    'show' => false 
                ), 
            ),
            'ESPID' => array(
                'tag' => 'ESPID',
                'name' => 'Espo Entity Id',
                'options' => array(
                    'field_type' => 'text',
                    'public' => false,
                    'show' => false
                ),
            ),
        );

        $existFields = array();
        $vars = $this->getListVars($listId);
        foreach ($vars as $var) {
            $existFields[] = $var["tag"];
        }

        foreach ($fieldsForAdd as $field => $varDefs) {
            if (!in_array($field, $existFields)) {
                $this->addVarToList($listId, $varDefs);
            }
        }
    }

    protected function getMembersFromReport($response, $startIdx)
    {
        $result = array();
        $helper = new \Espo\Modules\Advanced\Core\MailChimp\RecipientHelper($this->getEntityManager(), $this->getMetadata());
        if (isset($response['data'])) {

            foreach ($response['data'] as $data) {
                if ($startIdx > 0) {
                    $startIdx--;
                    continue;
                }
                $espoMember = $helper->recognizeMCMember($data['member']);
                if (!empty($espoMember)) {
                    $result[$data['member']['email']] = $espoMember;
                }
            }
        }
        return $result;
    }

    public function getEmailContent($campaignId, $memberEmail)
    {
        return $this->getClient()->getMemberEmailContent($campaignId, $memberEmail);
    }

    public function getListVars($listId)
    {
        $result = $this->getClient()->getListVars(array($listId));
        $vars = array();
        if (!empty($result['data']) && !empty($result['data'][0])) {
            $vars = $result['data'][0]['merge_vars'];
        }
        return $vars;
    }

    public function addVarToList($listId, $varDefs)
    {
        $this->getClient()->addVarToList($listId, $varDefs['tag'], $varDefs['name'], $varDefs['options']);
    }

    public function batchSubscribe($listId, $batchArray)
    {
         return $this->getClient()->batchSubcribe($listId, $batchArray);
    }

    public function updateMember($listId, $params)
    {
        return $this->getClient()->updateMember($listId, $params);
    }

    public function deleteMember($listId, $email)
    {
        return $this->getClient()->unsubscribe($listId, $email, true);
    }

    public function createList(array $data)
    {
        $name = (isset($data['name'])) ? $data['name'] : '';
        $reminder = (isset($data['reminder'])) ? $data['reminder'] : '';

        $contact = new \StdClass();
        $contact->company = (isset($data['company'])) ? $data['company'] : '';
        $contact->address1 = (isset($data['address1'])) ? $data['address1'] : '';
        $contact->address2 = (isset($data['$address2'])) ? $data['$address2'] : '';
        $contact->city =(isset($data['city'])) ? $data['city'] : '';
        $contact->state = (isset($data['state'])) ? $data['state'] : '';
        $contact->zip = (isset($data['zip'])) ? $data['zip'] : '';
        $contact->country = (isset($data['country'])) ? $data['country'] : '';
        $contact->phone = (isset($data['phone'])) ? $data['phone'] : '';

        $campaignDefaults = new \StdClass();
        $campaignDefaults->from_name = (isset($data['fromName'])) ? $data['fromName'] : '';
        $campaignDefaults->from_email = (isset($data['fromEmail'])) ? $data['fromEmail'] : '';
        $campaignDefaults->subject = (isset($data['subject'])) ? $data['subject'] : '';
        $campaignDefaults->language = (isset($data['language'])) ? $data['language'] : 'en';

        return $this->getClient()->createList($name, $contact, $campaignDefaults, $reminder);
    }

    public function createCampaign(array $data)
    {
        $defaultContent = '==============================================

Unsubscribe *|HTML:EMAIL|* from this list:
*|UNSUB|*';
        
        $type = (isset($data['type'])) ? $data['type'] : '';
        $listId = (isset($data['listId'])) ? $data['listId'] : '';
        $title = (isset($data['name'])) ? $data['name'] : '';
        $subject = (isset($data['subject'])) ? $data['subject'] : '';
        $fromEmail = (isset($data['fromEmail'])) ? $data['fromEmail'] : '';
        $fromName = (isset($data['fromName'])) ? $data['fromName'] : '';
        $toName = (isset($data['toName'])) ? $data['toName'] : '';
        $content = (isset($data['content'])) ? $data['content'] : $defaultContent;

        return $this->getClient()->createCampaign($type, $listId, $title, $subject, $fromEmail, $fromName, $toName, $content);
    }

}
