<?php


namespace Espo\Modules\RealEstate\Services;

use \PDO;

class Activities extends \Espo\Modules\Crm\Services\Activities
{

    protected function getRealEstatePropertyMeetingQuery($id, $op = 'IN', $notIn = [])
    {
        if (is_object($id)) {
            $id = $id->id;
        }

        $sql = "
            SELECT meeting.id AS 'id', meeting.name AS 'name', meeting.date_start AS 'dateStart', meeting.date_end AS 'dateEnd', 'Meeting' AS '_scope',
                   meeting.assigned_user_id AS assignedUserId, TRIM(CONCAT(user.first_name, ' ', user.last_name)) AS assignedUserName,
                   meeting.parent_type AS 'parentType', meeting.parent_id AS 'parentId', meeting.status AS status, meeting.created_at AS createdAt
            FROM `meeting`
            LEFT JOIN `user` ON user.id = meeting.assigned_user_id
        ";

        $sql .= "
            WHERE
                meeting.deleted = 0 AND
        ";

        $sql .= "
            (
                (
                    meeting.parent_type = 'RealEstateProperty' AND meeting.parent_id = ".$this->getPDO()->quote($id)."
                )
                OR
                (
                    meeting.parent_type = 'Opportunity' AND meeting.parent_id IN (
                        SELECT opportunity.id FROM opportunity WHERE opportunity.property_id = ".$this->getPDO()->quote($id)."
                    )
                )
            )
        ";


        if (!empty($notIn)) {
            $sql .= "
                AND meeting.status {$op} ('". implode("', '", $notIn) . "')
            ";
        }

        return $sql;
    }

    protected function getRealEstatePropertyCallQuery($id, $op = 'IN', $notIn = [])
    {
        if (is_object($id)) {
            $id = $id->id;
        }

        $sql = "
            SELECT call.id AS 'id', call.name AS 'name', call.date_start AS 'dateStart', call.date_end AS 'dateEnd', 'Call' AS '_scope',
                   call.assigned_user_id AS assignedUserId, TRIM(CONCAT(user.first_name, ' ', user.last_name)) AS assignedUserName,
                   call.parent_type AS 'parentType', call.parent_id AS 'parentId', call.status AS status, call.created_at AS createdAt
            FROM `call`
            LEFT JOIN `user` ON user.id = call.assigned_user_id
        ";

        $sql .= "
            WHERE
                call.deleted = 0 AND
        ";

        $sql .= "
            (
                (
                    call.parent_type = 'RealEstateProperty' AND call.parent_id = ".$this->getPDO()->quote($id)."
                )
                OR
                (
                    call.parent_type = 'Opportunity' AND call.parent_id IN (
                        SELECT opportunity.id FROM opportunity WHERE opportunity.property_id = ".$this->getPDO()->quote($id)."
                    )
                )
            )
        ";


        if (!empty($notIn)) {
            $sql .= "
                AND call.status {$op} ('". implode("', '", $notIn) . "')
            ";
        }

        return $sql;
    }

    protected function getRealEstatePropertyEmailQuery($id, $op = 'IN', $notIn = [])
    {
        if (is_object($id)) {
            $id = $id->id;
        }

        $sql = "
            SELECT email.id AS 'id', email.name AS 'name', email.date_sent AS 'dateStart', '' AS 'dateEnd', 'Email' AS '_scope',
                   email.assigned_user_id AS assignedUserId, TRIM(CONCAT(user.first_name, ' ', user.last_name)) AS assignedUserName,
                   email.parent_type AS 'parentType', email.parent_id AS 'parentId', email.status AS status, email.created_at AS createdAt
            FROM `email`
            LEFT JOIN `user` ON user.id = email.assigned_user_id
        ";

        $sql .= "
            WHERE
                email.deleted = 0 AND
        ";

        $sql .= "
            (
                (
                    email.parent_type = 'RealEstateProperty' AND email.parent_id = ".$this->getPDO()->quote($id)."
                )
                OR
                (
                    email.parent_type = 'Opportunity' AND email.parent_id IN (
                        SELECT opportunity.id FROM opportunity WHERE opportunity.property_id = ".$this->getPDO()->quote($id)."
                    )
                )
            )
        ";


        if (!empty($notIn)) {
            $sql .= "
                AND email.status {$op} ('". implode("', '", $notIn) . "')
            ";
        }

        return $sql;
    }

    protected function getRealEstateRequestMeetingQuery($id, $op = 'IN', $notIn = [])
    {
        if (is_object($id)) {
            $id = $id->id;
        }

        $sql = "
            SELECT meeting.id AS 'id', meeting.name AS 'name', meeting.date_start AS 'dateStart', meeting.date_end AS 'dateEnd', 'Meeting' AS '_scope',
                   meeting.assigned_user_id AS assignedUserId, TRIM(CONCAT(user.first_name, ' ', user.last_name)) AS assignedUserName,
                   meeting.parent_type AS 'parentType', meeting.parent_id AS 'parentId', meeting.status AS status, meeting.created_at AS createdAt
            FROM `meeting`
            LEFT JOIN `user` ON user.id = meeting.assigned_user_id
        ";

        $sql .= "
            WHERE
                meeting.deleted = 0 AND
        ";

        $sql .= "
            (
                (
                    meeting.parent_type = 'RealEstateRequest' AND meeting.parent_id = ".$this->getPDO()->quote($id)."
                )
                OR
                (
                    meeting.parent_type = 'Opportunity' AND meeting.parent_id IN (
                        SELECT opportunity.id FROM opportunity WHERE opportunity.request_id = ".$this->getPDO()->quote($id)."
                    )
                )
            )
        ";


        if (!empty($notIn)) {
            $sql .= "
                AND meeting.status {$op} ('". implode("', '", $notIn) . "')
            ";
        }

        return $sql;
    }

    protected function getRealEstateRequestCallQuery($id, $op = 'IN', $notIn = [])
    {
        if (is_object($id)) {
            $id = $id->id;
        }

        $sql = "
            SELECT call.id AS 'id', call.name AS 'name', call.date_start AS 'dateStart', call.date_end AS 'dateEnd', 'Call' AS '_scope',
                   call.assigned_user_id AS assignedUserId, TRIM(CONCAT(user.first_name, ' ', user.last_name)) AS assignedUserName,
                   call.parent_type AS 'parentType', call.parent_id AS 'parentId', call.status AS status, call.created_at AS createdAt
            FROM `call`
            LEFT JOIN `user` ON user.id = call.assigned_user_id
        ";

        $sql .= "
            WHERE
                call.deleted = 0 AND
        ";

        $sql .= "
            (
                (
                    call.parent_type = 'RealEstateRequest' AND call.parent_id = ".$this->getPDO()->quote($id)."
                )
                OR
                (
                    call.parent_type = 'Opportunity' AND call.parent_id IN (
                        SELECT opportunity.id FROM opportunity WHERE opportunity.request_id = ".$this->getPDO()->quote($id)."
                    )
                )
            )
        ";


        if (!empty($notIn)) {
            $sql .= "
                AND call.status {$op} ('". implode("', '", $notIn) . "')
            ";
        }

        return $sql;
    }

    protected function getRealEstateRequestEmailQuery($id, $op = 'IN', $notIn = [])
    {
        if (is_object($id)) {
            $id = $id->id;
        }

        $sql = "
            SELECT email.id AS 'id', email.name AS 'name', email.date_sent AS 'dateStart', '' AS 'dateEnd', 'Email' AS '_scope',
                   email.assigned_user_id AS assignedUserId, TRIM(CONCAT(user.first_name, ' ', user.last_name)) AS assignedUserName,
                   email.parent_type AS 'parentType', email.parent_id AS 'parentId', email.status AS status, email.created_at AS createdAt
            FROM `email`
            LEFT JOIN `user` ON user.id = email.assigned_user_id
        ";

        $sql .= "
            WHERE
                email.deleted = 0 AND
        ";

        $sql .= "
            (
                (
                    email.parent_type = 'RealEstateRequest' AND email.parent_id = ".$this->getPDO()->quote($id)."
                )
                OR
                (
                    email.parent_type = 'Opportunity' AND email.parent_id IN (
                        SELECT opportunity.id FROM opportunity WHERE opportunity.request_id = ".$this->getPDO()->quote($id)."
                    )
                )
            )
        ";


        if (!empty($notIn)) {
            $sql .= "
                AND email.status {$op} ('". implode("', '", $notIn) . "')
            ";
        }

        return $sql;
    }


}

