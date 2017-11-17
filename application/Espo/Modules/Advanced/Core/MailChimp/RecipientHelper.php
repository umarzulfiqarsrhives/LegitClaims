<?php


namespace Espo\Modules\Advanced\Core\MailChimp;

use \Espo\ORM\Entity;

class RecipientHelper
{

    protected $entityManager = null;
    protected $metadata = null;

    public function __construct($entityManager, $metadata)
    {
        $this->entityManager = $entityManager;
        $this->metadata = $metadata;
    }

    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    protected function getMetadata()
    {
        return $this->metadata;
    }

    public function getTargetListRecipients(Entity $entity)
    {
        $recipients = array();
        $relations = array(
            'accounts' => 'account_target_list',
            'users' => 'target_list_user',
            'leads' => 'lead_target_list',
            'contacts' => 'contact_target_list',
        );
        $listRepository = $this->getEntityManager()->getRepository('TargetList');

        foreach ($relations as $relation => $tableName) {
            $params = array();
            $columns = $this->getMetadata()->get("entityDefs.TarrgetList.links.{$relation}.additionalColumns");
            if (!empty($columns) && isset($columns->optedOut)) {
                $params['whereClause'] = array($tableName .'.opted_out' => 0);
            }

            $res = $listRepository->findRelated($entity, $relation, $params);
            $relRecipients = $res->toArray();
            foreach ($relRecipients as $recipient) {
                if (empty($recipient['emailAddress'])) {
                    continue;
                }
                $address = $this->getEntityManager()->getRepository('EmailAddress')->getByAddress($recipient['emailAddress']);
                if (empty($address) || $address->get('optOut') || $address->get('invalid')) {
                    continue;
                }
                $recipient['scope'] = $res->getEntityName();
                $recipients[] = $recipient;
            }
        }
        return $recipients;
    }

    public function prepareRecipientToMailChimp($entityArray)
    {
        $result = array();
        $entityName = $entityArray['scope'];
        switch ($entityName) {
            case 'Account': 
                $lastName = $entityArray['name']; 
                $firstName = '';
                break;
            case 'Lead':
            case 'Contact':
            case 'User':
                $lastName = $entityArray['lastName'];
                $firstName = $entityArray['firstName'];
                break;
        }
        if (isset($lastName)) {
            $email = $entityArray['emailAddress'];
            $result = array(
                'lastName' => $lastName,
                'firstName' => $firstName,
                'emailAddress' => $email,
                'entityName' => $entityName,
                'entityId' => $entityArray['id']
            );
            return $result;
        } else {
            return false;
        }
    }

    public function recognizeMCMember(array $member)
    {
        $entity = false;
        if (isset($member['merge']['ESPNM']) && isset($member['merge']['ESPID'])) {
            $entity = $this->getEntityManager()->getEntity($member['merge']['ESPNM'], $member['merge']['ESPID']);
        }
        if (empty($entity)) {
            $entity = $this->getEntityManager()->getRepository('EmailAddress')->getEntityByAddress($member['email']);
        }

        return $entity;
    }

    public function formatSubscriber(array $params = array(), $forUpdate = false, $groupingId = null, $groupName = null)
    {
        if (empty($params['emailAddress'])) {
            return false;
        }

        $data = array(
            'email' => array(
                'email' => $params['emailAddress']
            ), 
            'merge_vars' => array(
                'LNAME' => (isset($params['lastName'])) ? $params['lastName'] : '',
                'FNAME' => (isset($params['firstName'])) ? $params['firstName'] : '',
                'ESPNM' => (isset($params['entityName'])) ? $params['entityName'] : '',
                'ESPID' => (isset($params['entityId'])) ? $params['entityId'] : '',
            ), 
            'email_type' => 'html'
        );
        if ($forUpdate) {
            $data['merge_vars']['new-email'] = $params['newEmailAddress'];
        }
        if (!empty($groupingId)) {
            $groups = array();
            if ($groupName) {
                $groups[] = $groupName;
            }
            $data['merge_vars']['groupings'] = array(
                array(
                    'id' => $groupingId,
                    'groups' => $groups,
                )
            );
        }
        return $data;

    }

}
