<?php
namespace Espo\Modules\Advanced\EntryPoints;

use \Espo\Core\Utils\Util;

use \Espo\Core\Exceptions\NotFound;
use \Espo\Core\Exceptions\Forbidden;
use \Espo\Core\Exceptions\BadRequest;
use \Espo\Core\Exceptions\Error;

class ReportAsCsv extends \Espo\Core\EntryPoints\Base
{
    public static $authRequired = true;

    public function run()
    {
        if (empty($_GET['id'])) {
            throw new BadRequest();
        }

        $id = $_GET['id'];


        $where = null;
        if (!empty($_GET['where'])) {
            $where = $_GET['where'];
        }

        $column = null;
        if (!empty($_GET['column'])) {
            $column = $_GET['column'];
        }

        $service = $this->getServiceFactory()->create('Report');

        if (!empty($where)) {
            $where = json_decode($where, true);
        }

        $contents = $service->getCsv($id, $where, $column);

        $report = $this->getEntityManager()->getEntity('Report', $id);

        $name = $report->get('name');
        $name = str_replace(' ', '_', $name);
        $name = preg_replace("/([^\w\s\d\-_~,;:\[\]\(\).])/u", '_', $name);

        $fileName = $name . '.csv';


        ob_clean();
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=\"{$fileName}\"");
        echo $contents;
    }
}

