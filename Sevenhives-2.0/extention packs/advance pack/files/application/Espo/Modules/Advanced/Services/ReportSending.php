<?php


namespace Espo\Modules\Advanced\Services;

use \Espo\Core\Exceptions\Error;
use \Espo\Modules\Advanced\Business\Report\EmailBuilder;

class ReportSending extends \Espo\Core\Services\Base
{
    protected $dependencies = array(
        'entityManager',
        'serviceFactory',
        'user',
        'metadata',
        'config',
        'language',
        'mailSender',
        'preferences'
    );

    protected function getPDO()
    {
        return $this->getEntityManager()->getPDO();
    }

    protected function getLanguage()
    {
        return $this->injections['language'];
    }

    protected function getServiceFactory()
    {
        return $this->injections['serviceFactory'];
    }

    protected function getEntityManager()
    {
        return $this->injections['entityManager'];
    }

    protected function getUser()
    {
        return $this->injections['user'];
    }

    protected function getConfig()
    {
        return $this->injections['config'];
    }

    protected function getMetadata()
    {
        return $this->injections['metadata'];
    }

    protected function getMailSender()
    {
        return $this->injections['mailSender'];
    }

    protected function getPreferences()
    {
        return $this->injections['preferences'];
    }

    protected function getReportSenderManager()
    {
        $smtpParams = $this->getPreferences()->getSmtpParams();

        return new EmailBuilder($this->getMetadata(), $this->getEntityManager(), $smtpParams, $this->getMailSender(),$this->getConfig(), $this->getLanguage());
    }

    public function sendReport($data)
    {
        try {
            $smtpParams = $this->getPreferences()->getSmtpParams();
            $service = $this->getServiceFactory()->create('Report');
            $report = $this->getEntityManager()->getEntity('Report', $data['reportId']);
            if (empty($report)) {
                $GLOBALS['log']->error('Report Sending: No Report ' . $data['reportId']);
                return false;
            }
            $params = array();

            if ($report->get('type') == 'List') {
                $params = array(
                    'offset' => 0,
                    'maxSize' => 500,
                );
            }
            $result = $service->run($data['reportId'], '', $params);
            $reportResult = (isset($result['collection']) && is_object($result['collection'])) ? $result['collection']->toArray() : $result;

            if (count($reportResult) == 0 && $report->get('emailSendingDoNotSendEmptyReport')) {
                $GLOBALS['log']->info('Report Sending: Report ' . $report->get('name') . ' is empty and was not send');
                return false;
            }
            $sender = $this->getReportSenderManager();
            $sender->buildEmailData($data, $reportResult, $report);
            $sender->sendEmail($data);
        } catch (\Exception $e) {
            $GLOBALS['log']->error('Report Sending: ' . $e->getMessage());
        }
        return true;
    }

    public function scheduleEmailSending()
    {
        $query = "SELECT id, email_sending_interval AS sendInterval, email_sending_last_date_sent AS lastDateSent, email_sending_time AS sendingTime, email_sending_setting_month AS month, email_sending_setting_day AS day,  email_sending_setting_weekdays as weekdays
                FROM report 
                WHERE email_sending_interval IS NOT NULL AND email_sending_interval <> ''";

        $sth = $this->getPDO()->prepare($query);
        $sth->execute();
        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);

        $utcTZ = new \DateTimeZone('UTC');
        $now = new \DateTime("now", $utcTZ);

        $defaultTz = $this->getConfig()->get('timeZone');
        $espoTimeZone = new \DateTimeZone($defaultTz);
        foreach ($result as $row) {
            $scheduleSending = false;
            $check = false;

            $lastSent = '';
            if (!empty($row['lastDateSent'])) {
                $lastSent = new \DateTime($row['lastDateSent'], $utcTZ);
                $lastSent->setTimezone($espoTimeZone);
            }
            $nowCopy = clone $now;
            $nowCopy->setTimezone($espoTimeZone);

            switch ($row['sendInterval']) {
                case 'Daily':
                    $check = true;
                    break;
                case 'Weekly':
                    $check = (strpos($row['weekdays'], $nowCopy->format('w')) !== false);
                    break;
                case 'Monthly':
                    $check =
                        $nowCopy->format('j') == $row['day'] || 
                        $nowCopy->format('j') == $nowCopy->format('t') && $nowCopy->format('t') < $row['day'];
                    break;
                case 'Yearly':
                    $check =
                        (
                            $nowCopy->format('j') == $row['day'] || 
                            $nowCopy->format('j') == $nowCopy->format('t') && $nowCopy->format('t') < $row['day']
                        ) &&
                        $nowCopy->format('n') == $row['month'];
                    break;
            }
            if ($check) {
                if (empty($lastSent)) {
                    $scheduleSending = true;
                } else {
                    $nowCopy->setTime(0,0,0);
                    $lastSent->setTime(0,0,0);
                    $diff = $lastSent->diff($nowCopy);
                    if (!empty($diff)) {
                        $dayDiff = (int) ((($diff->invert) ? '-' : '') . $diff->days);
                        if ($dayDiff > 0) {
                            $scheduleSending = true;
                        }
                    }
                }
            }
            if ($scheduleSending) {
                $report = $this->getEntityManager()->getEntity('Report', $row['id']);
                if (empty($report)) {
                    continue;
                }
                $report->loadLinkMultipleField('emailSendingUsers');
                $users = $report->get('emailSendingUsersIds');
                if (empty($users)) {
                    continue;
                }

                $executeTime = clone $now;

                if (!empty($row['sendingTime'])) {
                    $time = explode(':', $row['sendingTime']);

                    if (empty($time[0]) || $time[0] < 0 && $time[0] > 23) {
                        $time[0] = 0;
                    }
                    if (empty($time[1]) || $time[1] < 0 && $time[1] > 59) {
                        $time[1] = 0;
                    }

                    $executeTime->setTimezone($espoTimeZone);
                    $executeTime->setTime($time[0], $time[1], 0);
                    $executeTime->setTimezone($utcTZ);
                }

                $report->set('emailSendingLastDateSent', $executeTime->format('Y-m-d H:i:s'));
                $this->getEntityManager()->saveEntity($report);

                $emailManager = $this->getReportSenderManager();
                foreach ($users as $userId) {
                    $jobEntity = $this->getEntityManager()->getEntity('Job');

                    $data = array(
                        'userId' => $userId,
                        'reportId' => $report->id
                    );

                    $jobEntity->set(array(
                        'name' => '',
                        'executeTime' => $executeTime->format('Y-m-d H:i:s'),
                        'method' => 'sendReport',
                        'data' => json_encode($data),
                        'serviceName' => 'ReportSending'
                    ));

                    $jobEntityId = $this->getEntityManager()->saveEntity($jobEntity);
                }
            }
        }
    }
}
