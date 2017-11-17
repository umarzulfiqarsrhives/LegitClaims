<?php


namespace Espo\Modules\Advanced\Core\Loaders;

class WorkflowHelper extends \Espo\Core\Loaders\Base
{
    public function load()
    {
        return new \Espo\Modules\Advanced\Core\Workflow\Helper($this->getContainer());
    }
}