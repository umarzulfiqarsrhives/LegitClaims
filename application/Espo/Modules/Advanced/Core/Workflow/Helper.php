<?php


namespace Espo\Modules\Advanced\Core\Workflow;

use Espo\ORM\Entity;

class Helper
{
    private $container;

    private $streamService;

    public function __construct(\Espo\Core\Container $container)
    {
        $this->container = $container;
    }

    protected function getContainer()
    {
        return $this->container;
    }

    protected function getEntityManager()
    {
        return $this->container->get('entityManager');
    }

    protected function getUser()
    {
        return $this->container->get('user');
    }

    protected function getServiceFactory()
    {
        return $this->container->get('serviceFactory');
    }

    protected function getStreamService()
    {
        if (empty($this->streamService)) {
            $this->streamService = $this->getServiceFactory()->create('Stream');
        }

        return $this->streamService;
    }

    /**
     * Get followers users ids
     *
     * @param  Entity $entity
     *
     * @return array
     */
    public function getFollowerUserIds(Entity $entity)
    {
        $users = $this->getStreamService()->getEntityFollowers($entity);

        return isset($users['idList']) ? $users['idList'] : array();
    }

    /**
     * Get user ids for team ids
     *
     * @param  array  $teamIds
     *
     * @return array
     */
    public function getUserIdsByTeamIds(array $teamIds)
    {
        $userIds = array();

        if (!empty($teamIds)) {
            $pdo = $this->getEntityManager()->getPDO();

            $sql = "
                SELECT team_user.user_id
                FROM team_user
                WHERE
                    team_user.team_id IN ('".implode("', '", $teamIds)."') AND
                    team_user.deleted = 0
            ";

            $sth = $pdo->prepare($sql);
            $sth->execute();
            if ($rows = $sth->fetchAll()) {
                foreach ($rows as $row) {
                    $userIds[] = $row['user_id'];
                }
            }
        }

        return $userIds;
    }

    /**
     * Get primary email addresses for user list
     *
     * @param  array  $userList
     *
     * @return array
     */
    public function getUsersEmailAddress(array $userList)
    {
        $data = array();

        if (!empty($userList)) {
            $pdo = $this->getEntityManager()->getPDO();

            $sql = "
                SELECT email_address.name
                FROM entity_email_address
                JOIN email_address ON email_address.id = entity_email_address.email_address_id AND email_address.deleted = 0
                WHERE
                    entity_email_address.entity_id IN ('".implode("', '", $userList)."') AND
                    entity_email_address.entity_type = 'User' AND
                    entity_email_address.deleted = 0 AND
                    entity_email_address.primary = 1
            ";

            $sth = $pdo->prepare($sql);
            $sth->execute();
            if ($rows = $sth->fetchAll()) {
                foreach ($rows as $row) {
                    $data[] = $row['name'];
                }
            }
        }

        return $data;
    }
}