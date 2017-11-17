<?php


namespace Espo\Modules\Advanced\Core\MailChimp;

use \Espo\Core\Exceptions\Error;
use \Espo\Core\Exceptions\Forbidden;
use \Espo\Core\Exceptions\NotFound;
use \Espo\ORM\Entity;

class Synchronizer
{
    const MAX_BATCH_PORTION = 100;
    const REQUEST_LIMIT = 25;

    protected $entityManager = null;
    protected $metadata = null;
    protected $config = null;
    protected $mailChimpManager = null;

    public function __construct($entityManager, $metadata, $config)
    {
        $this->entityManager = $entityManager;
        $this->metadata = $metadata;
        $this->config = $config;
        $this->mailChimpManager = new \Espo\Modules\Advanced\Core\MailChimp\MailChimpManager($this->getEntityManager(), $this->getMetadata(), $this->getConfig());
    }

    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    protected function getMetadata()
    {
        return $this->metadata;
    }

    protected function getConfig()
    {
        return $this->config;
    }

    protected function getMailChimpManager()
    {
        return $this->mailChimpManager;
    }

    protected function getMarker($campaign, $markerType)
    {
        return $this->getEntityManager()->getRepository('MailChimpLogMarker')->findMarker($campaign->get('mailChimpCampaignId'), $markerType);
    }

    protected function addLogRecord(array $logFields)
    {
        $logRecord = $this->getEntityManager()->getEntity('CampaignLogRecord');
        $logRecord->set($logFields);
        $this->getEntityManager()->saveEntity($logRecord);
        return $logRecord;
    }

    protected function campaignMemberActivity($campaign)
    {
        $pdo = $this->getEntityManager()->getPDO();
        $marker = $this->getMarker($campaign, 'MemberActivity');

        if (empty($marker)) {
            return;
        }

        $markerData = $marker->get('data');
        $query = "
            SELECT string_data as stringData, 
                action_date as actionDate,
                parent_type as parentType, 
                parent_id as parentId,
                `action` as action
            FROM campaign_log_record
            WHERE `action` IN ('Opened', 'Clicked') AND deleted=0 AND campaign_id=" . $pdo->quote($campaign->id). "
            ORDER BY action_date DESC, created_at DESC, stringData
        ";
        $sth = $pdo->prepare($query);
        $sth->execute();

        $opened = array();
        $clicked = array();
        
        $res = $sth->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($res as $row) {
            if ($row['action'] == 'Opened') {
                $opened[$row['parentId']] = $row;
            } else if ($row['action'] == 'Clicked') {
                if (!isset($clicked[$row['stringData']])) {
                    $clicked[$row['stringData']] = array();
                }
                $clicked[$row['stringData']][$row['parentId']] = $row;
            }
        }
        $since = (isset($markerData->since)) ? $markerData->since : '';
        $reportResult = $this->getMailChimpManager()->getMemberActivity($campaign->get('mailChimpCampaignId'), $since);
        if (!empty($reportResult)) {
            $now = new \DateTime("NOW", new \DateTimeZone('UTC'));
            foreach ($reportResult as $items) {
                foreach ($items as $memberEmail => $activities) {
                    $memberEntity = $this->getEntityManager()->getRepository('EmailAddress')->getEntityByAddress($memberEmail);
                    if (empty($memberEntity)) {
                        continue;
                    }
                    foreach ($activities as $activity) {
                        $activity = (object) $activity;
                        $action = '';
                        switch ($activity->action) {
                            case 'open': $action = 'Opened'; break;
                            case 'click': $action = 'Clicked'; break;
                        } 
                        
                        if (empty($action) ||
                            $action == 'Opened' && isset($opened[$memberEntity->id]) ||
                            $action == 'Clicked' && isset($clicked[$activity->url]) && isset($clicked[$activity->url][$memberEntity->id])) {
                            continue;
                        }

                        switch ($action) {
                            case 'Opened': $opened[$memberEntity->id] = true; break;
                            case 'Clicked': $clicked[$activity->url][$memberEntity->id] = true; break;
                        } 
                        $logFields = array(
                            'campaignId' => $campaign->id,
                            'parentType' => $memberEntity->getEntityName(),
                            'parentId' => $memberEntity->id,
                            'application' => 'MailChimp',
                            'action' => $action,
                            'actionDate' => $activity->timestamp,
                            'stringData' => $activity->url,
                        );
                        $this->addLogRecord($logFields);
                    }
                }
            }
            $now->modify('+1 second');
            $data = array('since' => $now->format("Y-m-d H:i:s"));
            $marker->set('data', json_encode($data));
            $this->getEntityManager()->saveEntity($marker);
        }
    }

    protected function createMemberEmail(Entity $campaign, Entity $member, $memberEmail, $sentTime, $emailInfo)
    {
        $mcCampaignId = $campaign->get('mailChimpCampaignId');
        $content = $this->getMailChimpManager()->getEmailContent($mcCampaignId, $memberEmail);
        if (!empty($content) && (isset($content['text']) || isset($content['html']))) {
            $email = $this->getEntityManager()->getEntity('Email');
            $subject = (isset($emailInfo['subject'])) ? $emailInfo['subject'] : $campaign->get('mailChimpCampaignName');
            $email->set('name', $subject);
            if (isset($content['html'])) {
                $email->set('body', $content['html']);
                $email->set('bodyPlain', $content['text']);
            } else {
                $email->set('body', $content['text']);
                $email->set('bodyPlain', $content['text']);
                $email->set('isHtml', false);
            }
            $email->set('parentId', $member->id);
            $email->set('parentType', $member->getEntityName());
            $email->set('to', $memberEmail);
            $email->set('from', $emailInfo['fromAddress']);
            $email->set('status', 'Sent');
            $email->set('dateSent', $sentTime);
            $this->getEntityManager()->saveEntity($email);
            return $email;
        }
        return false;
    }

    protected function campaignSentMembers($campaign, $sentTime, $createEmails = false, $emailInfo = array())
    {
        $reportType = 'Sent';
        $marker = $this->getMarker($campaign, $reportType);
        if (empty($marker) || $marker->get('completed')) {
            return;
        }
        $page = $marker->get('page');
        $skip = $marker->get('skip');

        $reportResult = $this->getMailChimpManager()->getReport($reportType, $campaign->get('mailChimpCampaignId'), $page, $skip, self::REQUEST_LIMIT);

        foreach ($reportResult['list'] as $memberEmail => $memberEntity) {

            $logFields = array(
                'campaignId' => $campaign->id,
                'parentType' => $memberEntity->getEntityName(),
                'parentId' => $memberEntity->id,
                'application' => 'MailChimp',
                'action' => $reportType,
                'actionDate' => $sentTime,
                'stringData' => $memberEmail,
            );

            if ($createEmails) {
                $email = $this->createMemberEmail($campaign, $memberEntity, $memberEmail, $sentTime, $emailInfo);
                if (!empty($email)) {
                    $logFields['objectId'] = $email->id;
                    $logFields['objectType'] = 'Email';
                }
            }
            $this->addLogRecord($logFields);
        }

        if ($reportResult['total'] == self::REQUEST_LIMIT) {
            $marker->set('page', ++$page);
            $marker->set('skip', 0);
        } else {
            $marker->set('skip', $reportResult['total']);
            $marker->set('completed', true);
        }
        $this->getEntityManager()->saveEntity($marker);

        $this->campaignSentMembers($campaign, $sentTime, $createEmails, $emailInfo);
    }

    protected function campaignHardBouncedMembers($campaign, $sentTime, $relatedMCListId, $reaction)
    {
        $reportType = 'Hard Bounced';
        $marker = $this->getMarker($campaign, $reportType);
        if (empty($marker) || $marker->get('completed')) {
            return;
        }
        $page = $marker->get('page');
        $skip = $marker->get('skip');

        $reportResult = $this->getMailChimpManager()->getReport($reportType, $campaign->get('mailChimpCampaignId'), $page, $skip, self::REQUEST_LIMIT);

        foreach ($reportResult['list'] as $memberEmail => $memberEntity) {
            $logFields = array(
                'campaignId' => $campaign->id,
                'parentType' => $memberEntity->getEntityName(),
                'parentId' => $memberEntity->id,
                'application' => 'MailChimp',
                'action' => 'Bounced',
                'stringAdditionalData' => 'Hard',
                'actionDate' => $sentTime,
                'stringData' => $memberEmail,
            );
            $this->addLogRecord($logFields);

            if ($reaction == "setAsInvalid" || $reaction == "setAsInvalidAndRemove") {
                $emailAddressEntity = $this->getEntityManager()->getRepository('EmailAddress')->getByAddress($memberEmail);
                if (!empty($emailAddressEntity) && !$emailAddressEntity->get("invalid")) {
                    $emailAddressEntity->set("invalid", true);
                    $this->getEntityManager()->saveEntity($emailAddressEntity);
                }
            }
            if ($reaction == "removeFromList" || $reaction == "setAsInvalidAndRemove") {
                $this->removeRecipientFromEspoLists($memberEntity, $relatedMCListId);
            }
        }

        if ($reportResult['total'] == self::REQUEST_LIMIT) {
            $marker->set('page', ++$page);
            $marker->set('skip', 0);
            $this->getEntityManager()->saveEntity($marker);
            $this->campaignHardBouncedMembers($campaign, $sentTime, $relatedMCListId, $reaction);
        } else {
            $marker->set('skip', $reportResult['total']);
            //$marker->set('completed', true);
            $this->getEntityManager()->saveEntity($marker);
        }
    }

    protected function campaignSoftBouncedMembers($campaign, $sentTime)
    {
        $reportType = 'Soft Bounced';
        $pdo = $this->getEntityManager()->getPDO();

        $query = "
            SELECT 
                string_data as stringData, 
                string_additional_data as stringAdditionalData, 
                `action` as action, 
                action_date as actionDate, 
                created_at as createdAt, 
                parent_type as parentType, 
                parent_id as parentId,
                campaign_id as campaignId
            FROM campaign_log_record
            WHERE `action` IN ('Sent', 'Bounced') AND deleted=0 AND campaign_id=" . $pdo->quote($campaign->id). "
            ORDER BY action_date DESC, created_at DESC, stringData
        ";
        $sth = $pdo->prepare($query);
        $sth->execute();

        $lastMembersStatus = array();
        $res = $sth->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($res as $row) {
            if (!isset($lastMembersStatus[$row['stringData']])) {
                $lastMembersStatus[$row['stringData']] = $row;
            }
        }
        while (true) {
            $marker = $this->getMarker($campaign, $reportType);
            if (empty($marker) || $marker->get('completed')) {
                break;
            }
            $page = $marker->get('page');
            $skip = $marker->get('skip');

            $reportResult = $this->getMailChimpManager()->getReport($reportType, $campaign->get('mailChimpCampaignId'), $page, $skip, self::REQUEST_LIMIT);

            foreach ($reportResult['list'] as $memberEmail => $memberEntity) {
                
                if (empty($lastMembersStatus[$memberEmail]) || $lastMembersStatus[$memberEmail]['action'] != "Bounced") {
                    $actionDateStr = (!empty($lastMembersStatus[$memberEmail])) ? $lastMembersStatus[$memberEmail]['createdAt'] : $sentTime;
                    $actionDate = new \DateTime($actionDateStr, new \DateTimeZone('UTC'));
                    $actionDate->modify('+1 second');

                    $logFields = array(
                        'campaignId' => $campaign->id,
                        'parentType' => $memberEntity->getEntityName(),
                        'parentId' => $memberEntity->id,
                        'application' => 'MailChimp',
                        'action' => 'Bounced',
                        'stringAdditionalData' => 'Soft',
                        'actionDate' => $actionDate->format("Y-m-d H:i:s"),
                        'stringData' => $memberEmail,
                    );
                    $this->addLogRecord($logFields);

                } else if (!empty($lastMembersStatus[$memberEmail]) && $lastMembersStatus[$memberEmail]['action'] == "Bounced"){
                    unset($lastMembersStatus[$memberEmail]);
                }
            }

            if ($reportResult['total'] == self::REQUEST_LIMIT) {
                $marker->set('page', ++$page);
                $this->getEntityManager()->saveEntity($marker);
            } else {
                $marker->set('page', 0);
                $this->getEntityManager()->saveEntity($marker);
                break;
            }
        }
        foreach($lastMembersStatus as $email => $lastMemberStatus) {
            if ($lastMemberStatus['action'] == "Bounced" && $lastMemberStatus['stringAdditionalData'] == 'Soft') {
                $logFields = $lastMemberStatus;

                $logFields['action'] = 'Sent';
                $logFields['actionDate'] = $sentTime;
                $logFields['stringAdditionalData'] = '';

                $this->addLogRecord($logFields);
            }
        }
    }

    protected function campaignOptedOutMembers($campaign, $sentTime, $espoTargetLists, $relatedMCListId, $markOptedOut = false)
    {
        $reportType = 'Opted Out';
        $marker = $this->getMarker($campaign, $reportType);
        if (empty($marker) || $marker->get('completed')) {
            return;
        }
        $page = $marker->get('page');
        $skip = $marker->get('skip');

        $reportResult = $this->getMailChimpManager()->getReport($reportType, $campaign->get('mailChimpCampaignId'), $page, $skip, self::REQUEST_LIMIT);

        foreach ($reportResult['list'] as $memberEmail => $memberEntity) {
            $lastLog = $this->getEntityManager()->getRepository('CampaignLogRecord')->where(
                array(
                    'parentId' => $memberEntity->id,
                    'parentType' => $memberEntity->getEntityName(),
                    'campaignId' => $campaign->id,
                    'application' => 'MailChimp'
                ))->findOne(
                    array(
                        'orderBy' => 'actionDate',
                        'order' => 'DESC'
            ));
            $date = (!empty($lastLog)) ? $lastLog->get('actionDate') : $sentTime;
            $actionDate = new \DateTime($date, new \DateTimeZone('UTC'));
            $actionDate->modify("+1 second");
            $logFields = array(
                'campaignId' => $campaign->id,
                'parentType' => $memberEntity->getEntityName(),
                'parentId' => $memberEntity->id,
                'application' => 'MailChimp',
                'action' => $reportType,
                'actionDate' => $actionDate->format("Y-m-d H:i:s"),
                'stringData' => $espoTargetLists,
            );
            $this->addLogRecord($logFields);

            if ($markOptedOut) {
                $emailAddressEntity = $this->getEntityManager()->getRepository('EmailAddress')->getByAddress($memberEmail);
                if (!empty($emailAddressEntity) && !$emailAddressEntity->get("optOut")) {
                    $emailAddressEntity->set("optOut", true);
                    $this->getEntityManager()->saveEntity($emailAddressEntity);
                }
            }
            $this->unsubscribeRecipientFromEspoLists($memberEntity, $relatedMCListId);

        }

        if ($reportResult['total'] == self::REQUEST_LIMIT) {
            $marker->set('page', ++$page);
            $marker->set('skip', 0);
            $this->getEntityManager()->saveEntity($marker);
            $this->campaignOptedOutMembers($campaign, $sentTime, $espoTargetLists, $relatedMCListId, $markOptedOut);
        } else {
            $marker->set('skip', $reportResult['total']);
            //$marker->set('completed', true);
            $this->getEntityManager()->saveEntity($marker);
        }
        
    }

    public function scheduleCampaignsSync()
    {
        $pdo = $this->getEntityManager()->getPDO();
        $query = "SELECT id 
            FROM campaign 
            WHERE mail_chimp_campaign_id <> '' AND mail_chimp_campaign_id IS NOT NULL AND deleted=0
            ORDER BY mail_chimp_last_successful_updating";

        $sth = $pdo->prepare($query);
        $sth->execute();
        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $row) {
            $this->getEntityManager()->getRepository('MailChimp')->addSyncJob('Campaign', $row['id']);
        }
    }

    public function scheduleTargetListsSync()
    {
        $pdo = $this->getEntityManager()->getPDO();
        $query = "SELECT id 
            FROM target_list
            WHERE mail_chimp_list_id <> '' AND mail_chimp_list_id IS NOT NULL AND deleted=0
            ORDER BY mail_chimp_last_successful_updating";

        $sth = $pdo->prepare($query);
        $sth->execute();
        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $row) {
            $this->getEntityManager()->getRepository('MailChimp')->addSyncJob('TargetList', $row['id']);
        }
    }

    public function loadLogDataForCampaign(Entity $campaign)
    {
        $n = time();
        $mcManager = $this->getMailChimpManager();
        $mcCampaignId = $campaign->get('mailChimpCampaignId');
        if (empty($mcCampaignId)) {
            return false;
        }
        $mailChimpCampaign = $mcManager->getCampaign($mcCampaignId);

        if (empty($mailChimpCampaign)) {
            return false;
        }
        if ($campaign->get('mailChimpCampaignStatus') !== $mailChimpCampaign['status']) {
            $campaign->set('mailChimpCampaignStatus',$mailChimpCampaign['status']);
            $this->getEntityManager()->saveEntity($campaign);
        }
        if ($mailChimpCampaign['status'] == 'sent') {
            $utc = new \DateTimeZone('UTC');
            $sentTime = new \DateTime($mailChimpCampaign['dateSent'], $utc);
            $now = new \DateTime('NOW', $utc);

            $integration = $this->getEntityManager()->getEntity('Integration', 'MailChimp');
            if (empty($integration)) {
                return false;
            }
            $emailInfo = array(
                'fromName' => $mailChimpCampaign['fromName'],
                'fromAddress' => $mailChimpCampaign['fromEmail'],
                'subject' => $mailChimpCampaign['subject']
            );
            $this->campaignSentMembers($campaign, $sentTime->format("Y-m-d H:i:s"), $integration->get('createEmails'), $emailInfo);
            $this->campaignMemberActivity($campaign);
            $sentTime->modify("+1 second");
            $this->campaignHardBouncedMembers($campaign, $sentTime->format("Y-m-d H:i:s"), $mailChimpCampaign['listId'], $integration->get('hardBouncedAction'));
            $this->campaignSoftBouncedMembers($campaign, $sentTime->format("Y-m-d H:i:s"));

            $targetListNamesArray = array();
            $lists = $this->getEntityManager()->getRepository('TargetList')->where(array('mailChimpListId' => $mailChimpCampaign['listId']))->find();
            if (!empty($lists)) {
                foreach ($lists as $list) {
                    $targetListNamesArray[] = $list->get('name');
                }
            }
            $this->campaignOptedOutMembers(
                $campaign,
                $sentTime->format("Y-m-d H:i:s"),
                implode(', ', $targetListNamesArray),
                $mailChimpCampaign['listId'],
                $integration->get('markEmailsOptedOut'));

            $campaign->set('mailChimpLastSuccessfulUpdating', $now->format("Y-m-d H:i:s"));
            $this->getEntityManager()->saveEntity($campaign);
        }
    }

    public function updateMCRecipients(Entity $targetList)
    {
        $mailChimpListId = $targetList->get('mailChimpListId');

        if (!empty($mailChimpListId)) {
            $this->getMailChimpManager()->addEntityFieldsToList($mailChimpListId);
            $helper = new \Espo\Modules\Advanced\Core\MailChimp\RecipientHelper($this->getEntityManager(), $this->getMetadata());

            $unsubscribedList = $this->getMailChimpManager()->getUnsubscribedMembers($mailChimpListId);
            foreach ($unsubscribedList as $emailAddress => $member) {
                $this->unsubscribeRecipientFromEspoList($member, $targetList);
            }

            $recipients = $helper->getTargetListRecipients($targetList);

            $portion = array();

            foreach ($recipients as $recipient) {
                $parsedRecipient = $helper->prepareRecipientToMailChimp($recipient);
                if (empty($parsedRecipient)) {
                    continue;
                }
                 $subscribeElem = $helper->formatSubscriber($parsedRecipient, false, $targetList->get('mcListGroupingId'), $targetList->get('mcListGroupName'));
                if (!empty($subscribeElem)) {
                    $portion[] = $subscribeElem;
                }

                if (count($portion) >= self::MAX_BATCH_PORTION) {
                    $this->getMailChimpManager()->batchSubscribe($mailChimpListId, $portion);
                    $portion = array();
                }
            }

            if (!empty($portion)) {
                $this->getMailChimpManager()->batchSubscribe($mailChimpListId, $portion);
            }

            $now = new \DateTime('NOW', new \DateTimeZone('UTC'));
            $targetList->set('mailChimpLastSuccessfulUpdating', $now->format("Y-m-d H:i:s"));
            $this->getEntityManager()->saveEntity($targetList);

            return true;
        }

        return false;
    }

    public function removeRecipientFromEspoLists($memberEntity, $relatedMCListId) {
        $lists = $this->getEntityManager()->getRepository('TargetList')->where(array('mailChimpListId' => $relatedMCListId))->find();
        $memberRepository = $this->getEntityManager()->getRepository($memberEntity->getEntityName());
        if (!empty($lists)) {
            foreach ($lists as $list) {
                $memberRepository->unrelate($memberEntity, 'targetLists', $list);
            }
        }
    }

    public function unsubscribeRecipientFromEspoLists($memberEntity, $relatedMCListId) {
        $lists = $this->getEntityManager()->getRepository('TargetList')->where(array('mailChimpListId' => $relatedMCListId))->find();
        if (!empty($lists)) {
            foreach ($lists as $list) {
                $this->unsubscribeRecipientFromEspoList($memberEntity, $list);
            }
        }
    }

    public function unsubscribeRecipientFromEspoList($member, $targetList) {
        $this->getEntityManager()->getRepository($member->getEntityName())->updateRelation($member, 'targetLists', $targetList->id, array('optedOut' => 1));
    }

}
