<?php


namespace Espo\Modules\Advanced\Controllers;

use \Espo\Core\Exceptions\BadRequest;
use \Espo\Core\Exceptions\Error;

class Report extends \Espo\Core\Controllers\Record
{
    public function actionRunList($params, $data, $request)
    {
        $id = $request->get('id');
        $where = $request->get('where');

        if (empty($id)) {
            throw new BadRequest();
        }

        $maxSize = $request->get('maxSize');
        if ($maxSize > 200) {
            throw new BadRequest();
        }

        $result = $this->getRecordService()->run($id, $where, array(
            'sortBy' => $request->get('sortBy'),
            'asc' => $request->get('asc') === 'true',
            'offset' => $request->get('offset'),
            'maxSize' => $maxSize,
            'groupValue' => $request->get('groupValue')
        ));

        if ($result) {
            return array(
                'list' => $result['collection']->toArray(),
                'total' => $result['total']
            );
        }
    }

    public function actionRun($params, $data, $request)
    {
        $id = $request->get('id');
        $where = $request->get('where');

        if (empty($id)) {
            throw new BadRequest();
        }

        return $this->getRecordService()->run($id, $where);
    }

    public function actionPopulateTargetList($params, $data, $request)
    {
        if (!$request->isPost()) {
            throw new BadRequest();
        }

        if (empty($data['id']) || empty($data['targetListId'])) {
            throw new BadRequest();
        }

        $id = $data['id'];
        $targetListId = $data['targetListId'];

        return $this->getRecordService()->populateTargetList($id, $targetListId);
    }

    public function actionSyncTargetListWithReports($params, $data, $request)
    {
        if (!$request->isPost()) {
            throw new BadRequest();
        }
        if (empty($data['targetListId'])) {
            throw new BadRequest();
        }
        $targetListId = $data['targetListId'];

        $targetList = $this->getEntityManager()->getEntity('TargetList', $targetListId);
        if (!$targetList->get('syncWithReportsEnabled')) {
            throw new Error();
        }

        return $this->getRecordService()->syncTargetListWithReports($targetList);
    }

    public function actionExportList($params, $data, $request)
    {
        $id = $request->get('id');
        $where = $request->get('where');

        if (empty($id)) {
            throw new BadRequest();
        }

        return array(
            'id' => $this->getRecordService()->exportList($request->get('id'), $where, array(
                'sortBy' => $request->get('sortBy'),
                'asc' => $request->get('asc') === 'true',
                'groupValue' => $request->get('groupValue')
            ))
        );
    }

}
