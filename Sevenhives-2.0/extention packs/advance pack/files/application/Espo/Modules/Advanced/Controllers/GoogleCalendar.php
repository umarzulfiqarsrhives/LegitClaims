<?php


namespace Espo\Modules\Advanced\Controllers;

use \Espo\Core\Exceptions\BadRequest;

class GoogleCalendar extends \Espo\Core\Controllers\Base
{
    public function actionUsersCalendars($params, $data, $request)
    {
        return $this->getService('GoogleCalendar')->usersCalendars();
    }
}
