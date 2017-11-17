<?php


namespace Espo\Modules\Advanced\SelectManagers;

class Report extends \Espo\Core\SelectManagers\Base
{
    protected function filterListTargets(&$result)
    {
        $result['whereClause'][] = array(
            'type=' => 'List',
            'entityType' => ['Contact', 'Lead', 'User', 'Account']
        );
    }

    protected function filterListAccounts(&$result)
    {
        $result['whereClause'][] = array(
            'type=' => 'List',
            'entityType' => 'Account'
        );
    }

    protected function filterListContacts(&$result)
    {
        $result['whereClause'][] = array(
            'type=' => 'List',
            'entityType' => 'Contact'
        );
    }

    protected function filterListLeads(&$result)
    {
        $result['whereClause'][] = array(
            'type=' => 'List',
            'entityType' => 'Lead'
        );
    }

    protected function filterListUsers(&$result)
    {
        $result['whereClause'][] = array(
            'type=' => 'List',
            'entityType' => 'User'
        );
    }

    protected function filterList(&$result)
    {
        $result['whereClause'][] = array(
            'type=' => 'List'
        );
    }

    protected function filterGrid(&$result)
    {
        $result['whereClause'][] = array(
            'type=' => 'Grid'
        );
    }

 }

