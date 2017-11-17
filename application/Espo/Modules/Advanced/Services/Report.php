<?php


namespace Espo\Modules\Advanced\Services;

use \Espo\ORM\Entity;

use \Espo\Core\Exceptions\Error;
use \Espo\Core\Exceptions\NotFound;
use \Espo\Core\Exceptions\Forbidden;

class Report extends \Espo\Services\Record
{

    protected function init()
    {
        $this->dependencies[] = 'language';
        $this->dependencies[] = 'container';
        $this->dependencies[] = 'acl';
        $this->dependencies[] = 'preferences';
        $this->dependencies[] = 'config';
        $this->dependencies[] = 'user';
        $this->dependencies[] = 'serviceFactory';
    }

    protected function getPreferences()
    {
        return $this->injections['preferences'];
    }

    protected function getServiceFactory()
    {
        return $this->injections['serviceFactory'];
    }

    protected function getConfig()
    {
        return $this->injections['config'];
    }

    protected function getUser()
    {
        return $this->injections['user'];
    }

    protected function getLanguage()
    {
        return $this->injections['language'];
    }

    protected function getAcl()
    {
        return $this->injections['acl'];
    }

    protected function getContainer()
    {
        return $this->injections['container'];
    }

    protected function getRecordService($name)
    {
        if ($this->getServiceFactory()->checkExists($name)) {
            $service = $this->getServiceFactory()->create($name);
        } else {
            $service = $this->getServiceFactory()->create('Record');
            if (method_exists($service, 'setEntityType')) {
                $service->setEntityType($name);
            } else {
                $service->setEntityName($name);
            }
        }

        return $service;
    }

    protected function beforeCreate(Entity $entity, array $data = array())
    {
        parent::beforeCreate($entity, $data);
        if (!$this->getAcl()->check($entity->get('entityType'), 'read')) {
            throw new Forbidden();
        }
    }

    protected function beforeUpdate(Entity $entity, array $data = array())
    {
        parent::beforeUpdate($entity, $data);
        $entity->clear('entityType');
    }

    public function run($id, $where = null, array $params = null)
    {
        if (empty($id)) {
            throw new Error();
        }
        $report = $this->getEntity($id);

        if (!$report) {
            throw new NotFound();
        }

        if (!$this->getAcl()->check($report, 'read')) {
            throw new Forbidden();
        }

        if ($report->get('isInternal')) {
            $className = $report->get('internalClassName');
            if (!empty($className)) {
                if (stripos($className, ':') !== false) {
                    list($moduleName, $reportName) = explode(':', $className);
                    if ($moduleName == 'Custom') {
                        $className = "\\Espo\\Custom\\Reports\\{$reportName}";
                    } else {
                        $className = "\\Espo\\Modules\\{$moduleName}\\Reports\\{$reportName}";
                    }
                } else {
                    $className = "\\Espo\\Reports\\{$className}";
                }
            } else {
                throw new Error('No class name specified for internal report.');
            }
            $reportObj = new $className($this->getContainer());
            return $reportObj->run($where, $params);
        }

        $type = $report->get('type');

        $data = $report->get('data');
        if (empty($data)) {
            $data = new \StdClass();
        }
        $data->orderBy = $report->get('orderBy');
        $data->groupBy = $report->get('groupBy');
        $data->columns = $report->get('columns');
        $data->filtersWhere = $this->convertFiltersData($report->get('filtersData'));

        $entityType = $report->get('entityType');

        $ignoreList = $this->getMetadata()->get('entityDefs.Report.entityListToIgnore', array());

        if (!$this->getAcl()->check($entityType, 'read')) {
            throw new Forbidden();
        }

        if (in_array($entityType, $ignoreList)) {
            throw new Forbidden();
        }

        switch ($type) {
            case 'Grid':
                if (!empty($params) && is_array($params) && array_key_exists('groupValue', $params)) {
                    return $this->executeSubReport($entityType, $data, $where, $params);
                }
                return $this->executeGridReport($entityType, $data, $where);
            case 'List':
                return $this->executeListReport($entityType, $data, $where, $params);
        }
    }

    protected function convertFiltersData($filtersData)
    {
        if (empty($filtersData)) {
            return null;
        }

        $arr = [];

        foreach ($filtersData as $name => $defs) {
            $field = $name;

            if (empty($defs)) {
                continue;
            }

            if (isset($defs->where)) {
                $arr[] = $defs->where;
            } else {
                if (isset($defs->field)) {
                    $field = $defs->field;
                }
                $type = $defs->type;
                if (!empty($defs->dateTime)) {
                    $arr[] = $this->convertDateTimeWhere($type, $field, $defs->value);
                } else {
                    $o = new \StdClass();
                    $o->type = $type;
                    $o->field = $field;
                    $o->value = $defs->value;
                    $arr[] = $o;
                }
            }
        }

        return $arr;
    }

    protected function convertDateTimeWhere($type, $field, $value)
    {
        $where = new \StdClass();
        $where->field = $field;

        $format = 'Y-m-d H:i:s';

        if (empty($value) && in_array($type, array('on', 'before', 'after'))) {
            return null;
        }

        $timeZone = $this->getPreferences()->get('timeZone');
        if (empty($timeZone)) {
            $timeZone = $this->getConfig()->get('timeZone');
        }

        $dt = new \DateTime('now', new \DateTimeZone($timeZone));

        switch ($type) {
            case 'today':
                $where->type = 'between';
                $dt->setTime(0, 0, 0);
                $dt->setTimezone(new \DateTimeZone('UTC'));
                $from = $dt->format($format);
                $dt->modify('+1 day');
                $to = $dt->format($format);
                $where->value = [$from, $to];
                break;
            case 'past':
                $where->type = 'before';
                $dt->setTimezone(new \DateTimeZone('UTC'));
                $where->value = $dt->format($format);
                break;
            case 'future':
                $where->type = 'after';
                $dt->setTimezone(new \DateTimeZone('UTC'));
                $where->value = $dt->format($format);
                break;
            case 'lastSevenDays':
                $where->type = 'between';

                $dtFrom = clone $dt;

                $dt->setTimezone(new \DateTimeZone('UTC'));
                $to = $dt->format($format);


                $dtFrom->modify('-7 day');
                $dtFrom->setTime(0, 0, 0);
                $dtFrom->setTimezone(new \DateTimeZone('UTC'));

                $from = $dtFrom->format($format);

                $where->value = [$from, $to];

                break;
            case 'lastXDays':
                $where->type = 'between';

                $dtFrom = clone $dt;

                $dt->setTimezone(new \DateTimeZone('UTC'));
                $to = $dt->format($format);

                $number = strval(intval($value));
                $dtFrom->modify('-'.$number.' day');
                $dtFrom->setTime(0, 0, 0);
                $dtFrom->setTimezone(new \DateTimeZone('UTC'));

                $from = $dtFrom->format($format);

                $where->value = [$from, $to];

                break;
            case 'nextXDays':
                $where->type = 'between';

                $dtTo = clone $dt;

                $dt->setTimezone(new \DateTimeZone('UTC'));
                $from = $dt->format($format);

                $number = strval(intval($value));
                $dtTo->modify('+'.$number.' day');
                $dtTo->setTime(24, 59, 59);
                $dtTo->setTimezone(new \DateTimeZone('UTC'));

                $to = $dtTo->format($format);

                $where->value = [$from, $to];

                break;
            case 'on':
                $where->type = 'between';

                $dt = new \DateTime($value, new \DateTimeZone($timeZone));
                $dt->setTimezone(new \DateTimeZone('UTC'));
                $from = $dt->format($format);

                $dt->modify('+1 day');
                $to = $dt->format($format);
                $where->value = [$from, $to];
                break;
            case 'before':
                $where->type = 'before';
                $dt = new \DateTime($value, new \DateTimeZone($timeZone));
                $dt->setTimezone(new \DateTimeZone('UTC'));
                $where->value = $dt->format($format);
                break;
            case 'after':
                $where->type = 'after';
                $dt = new \DateTime($value, new \DateTimeZone($timeZone));
                $dt->setTimezone(new \DateTimeZone('UTC'));
                $where->value = $dt->format($format);
                break;
            case 'between':
                $where->type = 'between';
                if (is_array($value)) {
                    $dt = new \DateTime($value[0], new \DateTimeZone($timeZone));
                    $dt->setTimezone(new \DateTimeZone('UTC'));
                    $from = $dt->format($format);

                    $dt = new \DateTime($value[1], new \DateTimeZone($timeZone));
                    $dt->setTimezone(new \DateTimeZone('UTC'));
                    $to = $dt->format($format);

                    $where->value = [$from, $to];
                }
               break;
            default:
                $where->type = $type;
        }

        return $where;
    }

    protected function handleLeftJoins($item, $entityType, &$params)
    {
        if (strpos($item, ':') !== false) {
            list($f, $item) = explode(':', $item);
        }

        if (strpos($item, '.') !== false) {
            list($rel, $f) = explode('.', $item);
            if (!in_array($rel, $params['leftJoins'])) {
                $params['leftJoins'][] = $rel;
                $defs = $this->getEntityManager()->getMetadata()->get($entityType);
                if (!empty($defs['relations']) && !empty($defs['relations'][$rel])) {
                    $params['distinct'] = true;
                }
            }
        }
    }

    protected function handleGroupBy($groupBy, $entityType, &$params, &$linkColumns, &$groupNameMap)
    {
        foreach ($groupBy as $item) {
            if (strpos($item, '.') === false) {
                if ($this->getMetadata()->get('entityDefs.' . $entityType . '.fields.' . $item . '.type') == 'link') {
                    if (!in_array($item, $params['leftJoins'])) {
                        $params['leftJoins'][] = $item;
                    }
                    $params['select'][] = $item . 'Name';
                    $params['select'][] = $item . 'Id';
                    $params['groupBy'][] = $item . 'Id';

                    $linkColumns[] = $item;
                } else {
                    if ($this->getMetadata()->get('entityDefs.' . $entityType . '.fields.' . $item . '.type') == 'enum') {
                        $groupNameMap[$item] = $this->getLanguage()->translate($item, 'options', $entityType);
                    }

                    $params['select'][] = $item;
                    $params['groupBy'][] = $item;
                }
            } else {
                $this->handleLeftJoins($item, $entityType, $params);
                $params['select'][] = $item;
                $params['groupBy'][] = $item;
            }
        }
    }

    protected function handleColumns($columns, $entityType, &$params, &$linkColumns)
    {
        foreach ($columns as $item) {
            if (strpos($item, '.') === false) {
                if ($this->getMetadata()->get('entityDefs.' . $entityType . '.fields.' . $item . '.type') == 'link') {
                    if (!in_array($item, $params['leftJoins'])) {
                        $params['leftJoins'][] = $item;
                    }
                    if (!in_array($item, $params['select'] . 'Name')) {
                        $params['select'][] = $item . 'Name';
                    }
                    if (!in_array($item, $params['select'] . 'Id')) {
                        $params['select'][] = $item . 'Id';
                    }
                    $linkColumns[] = $item;
                } else {
                    if (!in_array($item, $params['select'])) {
                        $params['select'][] = $item;
                    }
                }
            } else {
                $this->handleLeftJoins($item, $entityType, $params);
                if (!in_array($item, $params['select'])) {
                    $params['select'][] = $item;
                }
            }
        }
    }

    protected function handleOrderBy($orderBy, $entityType, &$params, &$orderLists)
    {
        foreach ($orderBy as $item) {
            if (strpos($item, 'LIST:') !== false) {
                $orderBy = substr($item, 5);

                if (strpos($orderBy, '.') !== false) {
                    list($rel, $field) = explode('.', $orderBy);

                    $foreignEntity = $this->getMetadata()->get('entityDefs.' . $entityType . '.links.' . $rel . '.entity');
                    if (empty($foreignEntity)) {
                        continue;
                    }
                    $options = $this->getMetadata()->get('entityDefs.' . $foreignEntity . '.fields.' . $field . '.options',  array());

                } else {

                    $field = $orderBy;
                    $options = $this->getMetadata()->get('entityDefs.' . $entityType . '.fields.' . $field . '.options',  array());
                }

                $params['orderBy'][] = array(
                    'LIST:' . $orderBy . ':' . implode(',', $options),
                );
                $orderLists[$orderBy] = $options;
            } else {
                if (strpos($item, 'ASC:') !== false) {
                    $orderBy = substr($item, 4);
                    $order = 'ASC';
                } else if (strpos($item, 'DESC:') !== false) {
                    $orderBy = substr($item, 5);
                    $order = 'DESC';
                } else {
                    continue;
                }

                if ($this->getMetadata()->get('entityDefs.' . $entityType . '.fields.' . $orderBy . '.type') == 'link') {
                    $orderBy = $orderBy . 'Name';
                }

                if (!in_array($orderBy, $params['select'])) {
                    continue;
                }

                $index = array_search($orderBy, $params['select']) + 1;

                $params['orderBy'][] = array(
                    $index,
                    $order
                );
            }
        }
    }

    protected function handleFilters($where, $entityType, &$params)
    {
        foreach ($where as $item) {
            if (!empty($item['field'])) {
                $this->handleLeftJoins($item['field'], $entityType, $params);
            }
        }

        $selectManager = $this->getSelectManagerFactory()->create($entityType);
        $filtersParams = $selectManager->getSelectParams(array('where' => $where));

        $params = $this->mergeSelectParams($params, $filtersParams);
    }


    protected function handleWhere($where, $entityType, &$params)
    {
        foreach ($where as $item) {
            if (!empty($item['field'])) {
                $this->handleLeftJoins($item['field'], $entityType, $params);
            }
        }

        $selectManager = $this->getSelectManagerFactory()->create($entityType);
        $filtersParams = $selectManager->getSelectParams(array('where' => $where));

        $params = $this->mergeSelectParams($params, $filtersParams);
    }

    protected function mergeSelectParams($params1, $params2)
    {
        $customWhere = '';
        if (!empty($params1['customWhere'])) {
            $customWhere .= $params1['customWhere'];
        }
        if (!empty($params2['customWhere'])) {
            $customWhere .= $params2['customWhere'];
        }

        $customJoin = '';
        if (!empty($params1['customJoin'])) {
            $customJoin .= $params1['customJoin'];
        }
        if (!empty($params2['customJoin'])) {
            $customJoin .= $params2['customJoin'];
        }
        $result = array_merge_recursive($params1, $params2);
        $result['customWhere'] = $customWhere;
        $result['customJoin'] = $customJoin;

        return $result;
    }

    protected function executeListReport($entityType, $data, $where, array $rawParams = null)
    {
        if (empty($rawParams)) {
            $rawParams = array();
        }

        $selectManager = $this->getSelectManagerFactory()->create($entityType);

        $params = $selectManager->getSelectParams($rawParams);

        if (!empty($data->filtersWhere)) {
            $filtersWhere = json_decode(json_encode($data->filtersWhere), true);
            $this->handleFilters($filtersWhere, $entityType, $params);
        }

        if ($where) {
            $this->handleWhere($where, $entityType, $params);
        }

        $collection = $this->getEntityManager()->getRepository($entityType)->find($params);
        $count = $this->getEntityManager()->getRepository($entityType)->count($params);

        $service = $this->getRecordService($entityType);
        foreach ($collection as $entity) {
            $service->loadAdditionalFieldsForList($entity);
        }

        return array(
            'collection' => $collection,
            'total' => $count
        );
    }

    protected function executeSubReport($entityType, $data, $where, array $rawParams)
    {
        $groupValue = $rawParams['groupValue'];
        unset($rawParams['groupValue']);

        $selectManager = $this->getSelectManagerFactory()->create($entityType);

        $params = $selectManager->getSelectParams($rawParams);

        $params['whereClause'] = isset($params['whereClause']) ? $params['whereClause'] : array();
        $params['leftJoins'] = isset($params['leftJoins']) ? $params['leftJoins'] : array();

        if (!empty($data->groupBy)) {
            $this->handleGroupBy($data->groupBy, $entityType, $params, $linkColumns, $groupNameMap);
            $groupBy = $params['groupBy'][0];
            unset($params['groupBy']);
        }

        if (empty($groupBy)) {
            throw new Error();
        }

        unset($params['select']);

        if (!empty($data->filtersWhere)) {
            $filtersWhere = json_decode(json_encode($data->filtersWhere), true);
            $this->handleFilters($filtersWhere, $entityType, $params);
        }

        if ($where) {
            $this->handleWhere($where, $entityType, $params);
        }

        $params['whereClause'] = (!empty($params['whereClause'])) ? $params['whereClause'] : array();

        $params['whereClause'][$groupBy] = $groupValue;

        $collection = $this->getEntityManager()->getRepository($entityType)->find($params);
        $count = $this->getEntityManager()->getRepository($entityType)->count($params);

        $service = $this->getRecordService($name);
        foreach ($collection as $entity) {
            $service->loadAdditionalFieldsForList($entity);
        }

        return array(
            'collection' => $collection,
            'total' => $count
        );
    }

    protected function executeGridReport($entityType, $data, $where)
    {
        $params = array();

        $seed = $this->getEntityManager()->getEntity($entityType);

        $this->getEntityManager()->getRepository($entityType)->handleSelectParams($params);

        $params['select'] = array();
        $params['groupBy'] = array();
        $params['orderBy'] = array();
        $params['whereClause'] = array();
        $params['leftJoins'] = isset($params['leftJoins']) ? $params['leftJoins'] : array();

        $groupNameMap = array();
        $orderLists = array();
        $linkColumns = array();
        $sums = array();

        if (!empty($data->groupBy)) {
            $this->handleGroupBy($data->groupBy, $entityType, $params, $linkColumns, $groupNameMap);
        }

        if (!empty($data->columns)) {
            $this->handleColumns($data->columns, $entityType, $params, $linkColumns);
        }

        if (!empty($data->orderBy)) {
            $this->handleOrderBy($data->orderBy, $entityType, $params, $orderLists);
        }

        if (!empty($data->filtersWhere)) {
            $filtersWhere = json_decode(json_encode($data->filtersWhere), true);
            $this->handleFilters($filtersWhere, $entityType, $params);
        }

        if ($where) {
            $this->handleWhere($where, $entityType, $params);
        }

        $sql = $this->getEntityManager()->getQuery()->createSelectQuery($entityType, $params);

        $pdo = $this->getEntityManager()->getPDO();
        $sth = $pdo->prepare($sql);
        $sth->execute();
        $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as $i => $row) {
            foreach ($row as $j => $value) {
                if (is_null($value)) {
                    $rows[$i][$j] = '';
                    unset($rows[$i]);
                }
            }
        }

        $reportData = $this->buildGrid($rows, $params, $data->columns, $sums);

        $grouping = array();
        foreach ($params['groupBy'] as $i => $groupCol) {
            $grouping[$i] = array();
            foreach ($rows as $row) {
                if (!in_array($row[$groupCol], $grouping[$i])) {
                    $grouping[$i][] = $row[$groupCol];
                }
            }
            if ($i > 0) {
                if (in_array('ASC:' . $groupCol, $data->orderBy)) {
                    sort($grouping[$i]);
                } if (in_array('DESC:' . $groupCol, $data->orderBy)) {
                    rsort($grouping[$i]);
                } else if (in_array('LIST:' . $groupCol, $data->orderBy)) {
                    if (!empty($orderLists[$groupCol])) {
                        $list = $orderLists[$groupCol];
                        usort($grouping[$i], function ($a, $b) use ($list) {
                            return array_search($a, $list) > array_search($b, $list);
                        });
                    }
                }
            }

            $isDate = false;
            if (strpos($groupCol, 'MONTH:') === 0) {
                $isDate = true;
                sort($grouping[$i]);
                $fullList = array();
                $dt = new \DateTime($grouping[$i][0] . '-01');
                $dtEnd = new \DateTime($grouping[$i][count($grouping[$i])  - 1] . '-01');
                if ($dt && $dtEnd) {
                    $interval = new \DateInterval('P1M');
                    while ($dt->getTimestamp() <= $dtEnd->getTimestamp()) {
                        $fullList[] = $dt->format('Y-m');
                        $dt->add($interval);
                    }
                    $grouping[$i] = $fullList;
                }
            } else if (strpos($groupCol, 'DAY:') === 0) {
                $isDate = true;
                sort($grouping[$i]);
                $fullList = array();
                $dt = new \DateTime($grouping[$i][0]);
                $dtEnd = new \DateTime($grouping[$i][count($grouping[$i])  - 1]);
                if ($dt && $dtEnd) {
                    $interval = new \DateInterval('P1D');
                    while ($dt->getTimestamp() <= $dtEnd->getTimestamp()) {
                        $fullList[] = $dt->format('Y-m-d');
                        $dt->add($interval);
                    }
                    $grouping[$i] = $fullList;
                }
            } else if (strpos($groupCol, 'YEAR:') === 0) {
                $isDate = true;
                sort($grouping[$i]);
                $fullList = array();
                $dt = new \DateTime($grouping[$i][0] . '-01-01');
                $dtEnd = new \DateTime($grouping[$i][count($grouping[$i]) - 1] . '-01-01');
                if ($dt && $dtEnd) {
                    $interval = new \DateInterval('P1Y');
                    while ($dt->getTimestamp() <= $dtEnd->getTimestamp()) {
                        $fullList[] = $dt->format('Y');
                        $dt->add($interval);
                    }
                    $grouping[$i] = $fullList;
                }
            }

            if ($isDate) {
                if (in_array('DESC:' . $groupCol, $data->orderBy)) {
                    rsort($grouping[$i]);
                }
            }
        }

        foreach ($linkColumns as $column) {
            $groupNameMap[$column] = array();
            foreach ($rows as $row) {
                if (array_key_exists($column . 'Id', $row) && array_key_exists($column . 'Name', $row)) {
                    $groupNameMap[$column][$row[$column . 'Id']] = $row[$column . 'Name'];
                }
            }
        }

        $columnNameMap = array();
        foreach ($data->columns as $item) {
            if ($item == 'COUNT:id') {
                $columnNameMap[$item] = $this->getLanguage()->translate('COUNT', 'functions', 'Report');
                continue;
            }

            if (strpos($item, ':') !== false) {
                $func = substr($item, 0, strpos($item, ':'));
                $field = substr($item, strpos($item, ':') + 1);

                if (strpos($field, '.') !== false) {
                    list ($rel, $field) = explode('.', $field);
                    $foreignEntity = $this->getMetadata()->get('entityDefs.' . $entityType . '.links.' . $rel . '.entity');
                    if (empty($foreignEntity)) {
                        continue;
                    }

                    $entityTypeLocal = $foreignEntity;
                } else {
                    $entityTypeLocal = $entityType;
                }

                $suffix = '';
                if ($this->getMetadata()->get('entityDefs.' . $entityTypeLocal . '.fields.' . $field. '.type') == 'currencyConverted') {
                    $field = str_replace('Converted', '', $field);
                    $suffix = ' (' . $this->getConfig()->get('baseCurrency') . ')';
                }
                $fieldTranslated = $this->getLanguage()->translate($field, 'fields', $entityTypeLocal);

                $columnNameMap[$item] = $this->getLanguage()->translate($func, 'functions', 'Report') . ': ' . $fieldTranslated . $suffix;
            }
        }

        $result = array(
            'type' => 'Grid',
            'groupBy' => $data->groupBy,
            'columns' => $data->columns,
            'sums' => $sums,
            'groupNameMap' => $groupNameMap,
            'columnNameMap' => $columnNameMap,
            'depth' => count($data->groupBy),
            'grouping' => $grouping,
            'reportData' => $reportData,
            'entityType' => $entityType,
            'success' => !empty($data->success) ? $data->success : null
        );

        return $result;
    }

    protected function buildGrid($rows, $params, $columns, &$sums, $groups = array(), $number = 0)
    {
        $k = count($groups);

        $groupColumn = $params['groupBy'][$k];

        $data = array();

        if ($k <= count($params['groupBy']) - 1) {

            $keys = array();
            foreach ($rows as $row) {
                foreach ($groups as $i => $g) {
                    if ($row[$params['groupBy'][$i]] !== $g) {
                        continue 2;
                    }
                }

                $key = $row[$groupColumn];
                if (!in_array($key, $keys)) {
                    $keys[] = $key;
                }
            }

            foreach ($keys as $number => $key) {
                $gr = $groups;
                $gr[] = $key;
                $data[$key] = $this->buildGrid($rows, $params, $columns, $sums, $gr, $number + 1);
            }
        } else {

            $s = &$sums;
            for ($i = 0; $i < count($groups) - 1; $i++) {
                $group = $groups[$i];
                if (!array_key_exists($group, $s)) {
                    $s[$group] = array();
                }
                $s = &$s[$group];
            }


            foreach ($rows as $j => $row) {
                foreach ($groups as $i => $g) {
                    if ($row[$params['groupBy'][$i]] != $g) {
                        continue 2;
                    }
                }

                foreach ($params['select'] as $c) {
                    if (in_array($c, $columns)) {
                        if (empty($s[$c])) {
                            $s[$c] = 0;
                        }
                        if (strpos($c, 'COUNT:') === 0) {
                            $value = intval($row[$c]);
                        } else {
                            $value = floatval($row[$c]);
                        }
                        if (strpos($c, 'MIN:') === 0) {
                            if ($s[$c] > $value) {
                                $s[$c] = $value;
                            }
                        } else if (strpos($c, 'MAX:') === 0) {
                            if ($s[$c] < $value) {
                                $s[$c] = $value;
                            }
                        } else if (strpos($c, 'AVG:') === 0) {
                            $s[$c] = $s[$c] + ($value - $s[$c]) / floatval($number);

                        } else {
                            $s[$c] = $s[$c] + $value;
                        }
                        $data[$c] = $value;
                    }
                }
            }
        }
        return $data;
    }

    public function getCSV($id, $where, $column)
    {
    	$data = $this->run($id, $where);

    	$depth = $data['depth'];

    	$reportData = $data['reportData'];

    	$result = array();
    	if ($depth == 2) {
    		$groupName1 = $data['groupBy'][0];
    		$groupName2 = $data['groupBy'][1];

			$row = array();
			$row[] = '';
			foreach ($data['grouping'][1] as $gr2) {
				$label = $gr2;
				if (empty($label)) {
    				$label = $this->getLanguage()->translate('-Empty-', 'labels', 'Report');
    			} else if (!empty($data['groupNameMap'][$groupName2][$gr2])) {
    				$label = $data['groupNameMap'][$groupName2][$gr2];
    			}
    			$row[] = $label;
			}
			$row[] = $this->getLanguage()->translate('Total', 'labels', 'Report');

			$result[] = $row;

    		foreach ($data['grouping'][0] as $gr1) {
    			$row = array();
    			$label = $gr1;
    			if (empty($label)) {
    				$label = $this->getLanguage()->translate('-Empty-', 'labels', 'Report');
    			} else if (!empty($data['groupNameMap'][$groupName1][$gr1])) {
	    			$label = $data['groupNameMap'][$groupName1][$gr1];
	    		}
    			$row[] = $label;
    			foreach ($data['grouping'][1] as $gr2) {
    				$value = 0;
    				if (!empty($reportData[$gr1]) && !empty($reportData[$gr1][$gr2])) {
    					if (!empty($reportData[$gr1][$gr2][$column])) {
    						$value = $reportData[$gr1][$gr2][$column];
    					}
    				}
    				$row[] = $value;
    			}
    			$sum = 0;

    			if (!empty($data['sums'][$gr1])) {
    				if (!empty($data['sums'][$gr1][$column])) {
    					$sum = $data['sums'][$gr1][$column];
    				}
    			}
    			$row[] = $sum;
    			$result[] = $row;
    		}

    		$out = array();
    		foreach ($result as $i => $row) {
    			foreach ($row as $j => $value) {
    				$out[$j][$i] = $value;
    			}
    		}
    		$result = $out;
    	} else if ($depth == 1) {
    		$groupName = $data['groupBy'][0];

			$row = array();
			$row[] = '';
			foreach ($data['columns'] as $column) {
				$label = $column;
				if (!empty($data['columnNameMap'][$column])) {
					$label = $data['columnNameMap'][$column];
				}
				$row[] = $label;
			}
			$result[] = $row;

			foreach ($data['grouping'][0] as $gr) {
				$row = array();
				$label = $gr;
				if (empty($label)) {
    				$label = $this->getLanguage()->translate('-Empty-', 'labels', 'Report');
    			} else if (!empty($data['groupNameMap'][$groupName][$gr])) {
    				$label = $data['groupNameMap'][$groupName][$gr];
    			}
				$row[] = $label;
				foreach ($data['columns'] as $column) {
					$value = 0;
					if (!empty($reportData[$gr])) {
						if (!empty($reportData[$gr][$column])) {
							$value = $reportData[$gr][$column];
						}
					}
					$row[] = $value;
				}
				$result[] = $row;
			}
			$row = array();
			$row[] = $this->getLanguage()->translate('Total', 'labels', 'Report');
			foreach ($data['columns'] as $column) {
				$sum = 0;
				if (!empty($data['sums'][$column])) {
					$sum = $data['sums'][$column];
				}
				$row[] = $sum;
			}
			$result[] = $row;
    	}

        $delimiter = $this->getConfig()->get('exportDelimiter', ';');

        $fp = fopen('php://temp', 'w');
        foreach ($result as $row) {
            fputcsv($fp, $row, $delimiter);
        }
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

    	return $csv;
    }

    public function populateTargetList($id, $targetListId)
    {
        $report = $this->getEntityManager()->getEntity('Report', $id);
        if (!$report) {
            throw new NotFound();
        }
        if (!$this->getAcl()->check($report, 'read')) {
            throw new Forbidden();
        }

        $targetList = $this->getEntityManager()->getEntity('TargetList', $targetListId);
        if (!$targetList) {
            throw new NotFound();
        }
        if (!$this->getAcl()->check($targetList, 'edit')) {
            throw new Forbidden();
        }

        if ($report->get('type') != 'List') {
            throw new Error();
        }

        $entityType = $report->get('entityType');

        switch ($entityType) {
            case 'Contact':
                $link = 'contacts';
                break;
            case 'Lead':
                $link = 'leads';
                break;
            case 'User':
                $link = 'users';
                break;
            case 'Account':
                $link = 'accounts';
                break;
            default:
                throw new Error();
        }

        $data = $report->get('data');
        if (empty($data)) {
            $data = new \StdClass();
        }
        $data->orderBy = $report->get('orderBy');
        $data->columns = $report->get('columns');
        $data->filtersWhere = $this->convertFiltersData($report->get('filtersData'));


        $rawParams = array();
        $selectManager = $this->getSelectManagerFactory()->create($entityType);
        $params = $selectManager->getSelectParams($rawParams);

        if (!empty($data->filtersWhere)) {
            $filtersWhere = json_decode(json_encode($data->filtersWhere), true);
            $this->handleFilters($filtersWhere, $entityType, $params);
        }

        return $this->getEntityManager()->getRepository('TargetList')->massRelate($targetList, $link, $params);
    }

    public function syncTargetListWithReports(Entity $targetList)
    {
        if (!$this->getAcl()->check($targetList, 'edit')) {
            throw new Forbidden();
        }

        $targetListService = $this->getServiceFactory()->create('TargetList');

        if ($targetList->get('syncWithReportsUnlink')) {
            $targetListService->unlinkAll($targetList->id, 'contacts');
            $targetListService->unlinkAll($targetList->id, 'leads');
            $targetListService->unlinkAll($targetList->id, 'accounts');
            $targetListService->unlinkAll($targetList->id, 'users');
        }
        $reportList = $this->getEntityManager()->getRepository('TargetList')->findRelated($targetList, 'syncWithReports');
        foreach ($reportList as $report) {
            $this->populateTargetList($report->id, $targetList->id);
        }
        return true;
    }

    public function exportList($id, $where = null, array $params = null)
    {
        $resultData = $this->run($id, $where, $params);

        if (!array_key_exists('collection', $resultData)) {
            throw new Error();
        }

        $collection = $resultData['collection'];

        $arr = array();

        $collection->toArray();

        $fieldsToSkip = array(
            'modifiedByName',
            'modifiedById',
            'modifiedAt',
            'deleted',
        );

        $fields = null;
        foreach ($collection as $entity) {
            if (empty($fields)) {
                $fields = array();
                foreach ($entity->getFields() as $field => $defs) {
                    if (in_array($field, $fieldsToSkip)) {
                        continue;
                    }

                    if (empty($defs['notStorable'])) {
                        $fields[] = $field;
                    } else {
                        if (in_array($defs['type'], array('email', 'phone'))) {
                            $fields[] = $field;
                        } else if ($defs['name'] == 'name') {
                            $fields[] = $field;
                        }
                    }
                }
            }

            $row = array();
            foreach ($fields as $field) {
                $value = $this->getFieldFromEntityForExport($entity, $field);
                $row[$field] = $value;
            }
            $arr[] = $row;
        }

        $delimiter = $this->getPreferences()->get('exportDelimiter');
        if (empty($delimiter)) {
            $delimiter = $this->getConfig()->get('exportDelimiter', ';');
        }

        $fp = fopen('php://temp', 'w');
        fputcsv($fp, array_keys($arr[0]), $delimiter);
        foreach ($arr as $row) {
            fputcsv($fp, $row, $delimiter);
        }
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        $fileName = "Export_{$this->entityType}.csv";

        $attachment = $this->getEntityManager()->getEntity('Attachment');
        $attachment->set('name', $fileName);
        $attachment->set('role', 'Export File');
        $attachment->set('type', 'text/csv');

        $this->getEntityManager()->saveEntity($attachment);

        if (!empty($attachment->id)) {
            $this->getInjection('fileManager')->putContents('data/upload/' . $attachment->id, $csv);
            return $attachment->id;
        }
        throw new Error();
    }
}

