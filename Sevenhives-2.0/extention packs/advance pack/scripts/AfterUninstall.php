<?php


class AfterUninstall
{
    protected $conatiner;

    public function run($conatiner)
    {
        $this->container = $conatiner;

        $entityManager = $this->container->get('entityManager');

        if ($job = $entityManager->getRepository('ScheduledJob')->where(array('job' => 'SynchronizeEventsWithGoogleCalendar'))->findOne()) {
            $entityManager->removeEntity($job);
        }
        if ($job = $entityManager->getRepository('ScheduledJob')->where(array('job' => 'MailChimpSyncData'))->findOne()) {
            $entityManager->removeEntity($job);
        }
        if ($job = $entityManager->getRepository('ScheduledJob')->where(array('job' => 'ReportTargetListSync'))->findOne()) {
            $entityManager->removeEntity($job);
        }
        if ($job = $entityManager->getRepository('ScheduledJob')->where(array('job' => 'ScheduleReportSending'))->findOne()) {
            $entityManager->removeEntity($job);
        }
    }
}
