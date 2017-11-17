<?php


namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\Core\Exceptions\Error;
use Espo\Modules\Advanced\Core\Workflow\Utils;

use Espo\ORM\Entity;

class CreateNotification extends Base
{
    /**
     * Main run method
     *
     * @param  array $actionData
     * @return string
     */
    protected function run(Entity $entity, array $actionData)
    {
        if (empty($actionData['recipient'])) {
            return;
        }
        if (empty($actionData['messageTemplate'])) {
            return;
        }

        $userList = [];
        switch ($actionData['recipient']) {
            case 'specifiedUsers':
                if (empty($actionData['userIdList']) || !is_array($actionData['userIdList'])) {
                    return;
                }
                $userIds = $actionData['userIdList'];
                break;

            case 'specifiedTeams':
                $userIds = $this->getHelper()->getUserIdsByTeamIds($actionData['specifiedTeamsIds']);
                break;

            case 'teamUsers':
                $entity->loadLinkMultipleField('teams');
                $userIds = $this->getHelper()->getUserIdsByTeamIds($entity->get('teamsIds'));
                break;

            case 'followers':
                $userIds = $this->getHelper()->getFollowerUserIds($entity);
                break;

            default:
                $user = $this->getRecipientUser($actionData['recipient']);
                if ($user) {
                    $userList[] = $user;
                }
                break;
        }

        if (isset($userIds)) {
            foreach ($userIds as $userId) {
                $user = $this->getEntityManager()->getEntity('User', $userId);
                $userList[] = $user;
            }
        }

        foreach ($userList as $user) {
            $notification = $this->getEntityManager()->getEntity('Notification');
            $notification->set(array(
                'type' => 'message',
                'data' => array(
                    'entityId' => $entity->id,
                    'entityType' => $entity->getEntityType(),
                    'entityName' => $entity->get('name'),
                    'userId' => $this->getUser()->id,
                    'userName' => $this->getUser()->get('name')
                ),
                'userId' => $user->id,
                'message' => $actionData['messageTemplate'],
                'relatedId' => $entity->id,
                'relatedType' => $entity->getEntityType()
            ));
            $this->getEntityManager()->saveEntity($notification);
        }
        return true;
    }


    /**
     * Get email address defined in workflow
     *
     * @param  string $type
     * @return array
     */
    protected function getRecipientUser($recipient)
    {
        $data = $this->getActionData();

        if ($recipient == 'currentUser') {
            return $this->getUser();
        } else {
            return Utils::getFieldValue($this->getEntity(), $recipient, true, $this->getEntityManager());
        }

    }
}