<?php


namespace Espo\Modules\Advanced\Controllers;

use \Espo\Core\Exceptions\Forbidden;

class Workflow extends \Espo\Core\Controllers\Record
{
    protected function checkGlobalAccess()
    {
        if (!$this->getUser()->isAdmin()) {
            throw new Forbidden();
        }
    }
}

