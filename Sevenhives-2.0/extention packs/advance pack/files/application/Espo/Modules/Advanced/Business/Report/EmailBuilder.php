<?php


namespace Espo\Modules\Advanced\Business\Report;

use \Espo\ORM\Entity;
use \Espo\Core\Utils\DateTime;

class EmailBuilder
{
    protected $entityManager;

    protected $smtpParams;

    protected $mailSender;

    protected $config;

    protected $dateTime;
    
    protected $metadata;

    protected $language;

    public function __construct($metadata, $entityManager, $smtpParams, $mailSender, $config, $language)
    {
        $this->metadata = $metadata;
        $this->entityManager = $entityManager;
        $this->smtpParams = $smtpParams;
        $this->mailSender = $mailSender;
        $this->config = $config;
        $this->language = $language;
    }

    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    protected function getLanguage()
    {
        return $this->language;
    }
    public function buildEmailData(& $data, $reportResult, $report)
    {
        if (!is_object($report)) {
            return false;
        }
        $type = $report->get('type');
        switch ($type) {
            case 'Grid': 
                $this->buildEmailGridData($data, $reportResult, $report);
                break;
            case 'List':
                $this->buildEmailListData($data, $reportResult, $report);
                break;
        }

        return true;
    }

    protected function buildEmailListData(& $data, $reportResult, $report)
    {
        $userId = (isset($data['userId'])) ? $data['userId'] : '';

        $entityType = $report->get('entityType');
        $columns = $report->get('columns');
        $reportName = $report->get('name');
        $reportDescription = $report->get('description');
        $entity = $this->getEntityManager()->getEntity($entityType);

        if (empty($entity)) {
            return false;
        }

        $userPreference = $this->getEntityManager()->getEntity('Preferences', $userId);

        $dateTime = new DateTime(
            $userPreference->get('dateFormat'),
            $userPreference->get('timeFormat'),
            $userPreference->get('timeZone'));

        $userThousandSeparator = $userPreference->get('thousandSeparator');
        $userDecimalMark = $userPreference->get('decimalMark');

        $this->language->setLanguage($userLanguage);
        $contents = $this->getTemplate('ReportSendingSubject', $userLanguage);
        $emailSubject = str_replace('{name}', $reportName, $contents);

        $entityDefs = $this->metadata->get('entityDefs');
        $fields = $entityDefs[$entityType]['fields'];

        $reportColumnNames = array();
        foreach ($columns as $column){
            $reportColumnNames[] = $this->language->translate($column, 'fields', $entityType);
        }

        $result = array();
        foreach ($reportResult as $recordKey => $record) {

            foreach ($columns as $columnKey => $column){
                $type = (isset($fields[$column])) ? $fields[$column]['type'] : '';
                $value = (isset($record[$column])) ? (string) $record[$column] : '';
                switch($type) {
                    case 'date':
                        if (!empty($value)) {
                            $value = $dateTime->convertSystemDate($value);
                        }
                        break;
                    case 'datetime':
                        if (!empty($value)) {
                            $value = $dateTime->convertSystemDateTime($value);
                        }
                        break;
                    case 'link':
                    case 'linkParent':
                        if (!empty($record[$column . 'Name'])) {
                            $value = $record[$column . 'Name'];
                        }
                        break;
                    case 'jsonArray':break;
                    case 'bool': $value = ($record[$column]) ? '1' : '0'; break;
                    case 'enum':
                        $value = $this->language->translateOption($value, $column, $entityType);
                        break;
                    case 'int':
                        $value = number_format($value, 0, $userDecimalMark, $userThousandSeparator);
                        break;
                    case 'float':
                        $value = number_format($value, 2, $userDecimalMark, $userThousandSeparator);
                        break;
                    case 'currency':
                        $value = number_format($value, 2, $userDecimalMark, $userThousandSeparator) . ' ' . $record[$column . 'Currency'];
                        break;
                }
                $result[$recordKey][$columnKey] = array('value' => $value, 'wrapper' => null);
            }
        }
        $contents = $this->getTemplate('ReportSendingBody', $userLanguage);
        $contents = str_replace('{reportHeader}', $emailSubject, $contents);

        $contents = str_replace('{reportDescription}', $reportDescription, $contents);
        $emailBody = $this->parseReportTable($contents, array(
            'columnNames' => $reportColumnNames,
            'reportResult' => $result
        ));

        $data['emailSubject'] = $emailSubject;
        $data['emailBody'] = $emailBody;

        return true;
    }

    protected function buildEmailGridData(& $data, $reportResult, $report)
    {
        $depth = $reportResult['depth'];
        $reportName = $report->get('name');
        $reportDescription = $report->get('description');
        $reportData = $reportResult['reportData'];
        $emailBody = '';
        $showDescription = true;

        $userId = (isset($data['userId'])) ? $data['userId'] : '';

        $userPreference = $this->getEntityManager()->getEntity('Preferences', $userId);
        $userThousandSeparator = $userPreference->get('thousandSeparator');
        $userDecimalMark = $userPreference->get('decimalMark');

        $userLanguage = $userPreference->get('language');
        $this->language->setLanguage($userLanguage);

        $contents = $this->getTemplate('ReportSendingSubject', $userLanguage);
        $emailSubject = str_replace('{name}', $reportName, $contents);

        foreach ($reportResult['columns'] as $column) {
            $result = array();
            if ($depth == 2) {
                $groupName1 = $reportResult['groupBy'][0];
                $groupName2 = $reportResult['groupBy'][1];

                $row = array();
                $row[] = '';
                foreach ($reportResult['grouping'][1] as $gr2) {
                    $label = $gr2;
                    if (empty($label)) {
                        $label = $this->getLanguage()->translate('-Empty-', 'labels', 'Report');
                    } else if (!empty($reportResult['groupNameMap'][$groupName2][$gr2])) {
                        $label = $reportResult['groupNameMap'][$groupName2][$gr2];
                    }
                    $row[] = array('value' => $label, 'wrapper' => null);
                }
                $totalLabel = $this->getLanguage()->translate('Total', 'labels', 'Report');
                $row[] = array('value' => $totalLabel, 'wrapper' => 'b');
                $result[] = $row;

                foreach ($reportResult['grouping'][0] as $gr1) {
                    $row = array();
                    $label = $gr1;
                    if (empty($label)) {
                        $label = $this->getLanguage()->translate('-Empty-', 'labels', 'Report');
                    } else if (!empty($reportResult['groupNameMap'][$groupName1][$gr1])) {
                        $label = $reportResult['groupNameMap'][$groupName1][$gr1];
                    }
                    $row[] = array('value' => $label, 'wrapper' => "b");
                    foreach ($reportResult['grouping'][1] as $gr2) {
                        $value = 0;
                        if (!empty($reportData[$gr1]) && !empty($reportData[$gr1][$gr2])) {
                            if (!empty($reportData[$gr1][$gr2][$column])) {
                                $value = $reportData[$gr1][$gr2][$column];
                                $value = rtrim(rtrim(number_format($value, 2, $userDecimalMark, $userThousandSeparator), '0'), '.');
                            }
                        }
                        $row[] = array('value' => $value, 'wrapper' => null);
                    }
                    $sum = 0;

                    if (!empty($reportResult['sums'][$gr1])) {
                        if (!empty($reportResult['sums'][$gr1][$column])) {
                            $sum = $reportResult['sums'][$gr1][$column];
                            $sum = rtrim(rtrim(number_format($sum, 2, $userDecimalMark, $userThousandSeparator), '0'), '.');
                        }
                    }
                    $row[] = array('value' => $sum, 'wrapper' => 'b');
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
                $groupName = $reportResult['groupBy'][0];

                $row = array();
                $row[] = array('value' => '', 'wrapper' => null);
                foreach ($reportResult['columns'] as $column) {
                    $label = $column;
                    if (!empty($reportResult['columnNameMap'][$column])) {
                        $label = $reportResult['columnNameMap'][$column];
                    }
                    $row[] = array('value' => $label, 'wrapper' => 'b');
                }
                $result[] = $row;

                foreach ($reportResult['grouping'][0] as $gr) {
                    $row = array();
                    $label = $gr;
                    if (empty($label)) {
                        $label = $this->getLanguage()->translate('-Empty-', 'labels', 'Report');
                    } else if (!empty($reportResult['groupNameMap'][$groupName][$gr])) {
                        $label = $reportResult['groupNameMap'][$groupName][$gr];
                    }
                    $row[] = array('value' => $label, 'wrapper' => null);
                    foreach ($reportResult['columns'] as $column) {
                        $value = 0;
                        if (!empty($reportData[$gr])) {
                            if (!empty($reportData[$gr][$column])) {
                                $value = $reportData[$gr][$column];
                                $value = rtrim(rtrim(number_format($value, 2, $userDecimalMark, $userThousandSeparator), '0'), '.');
                            }
                        }
                        $row[] = array('value' => $value, 'wrapper' => null);
                    }
                    $result[] = $row;
                }
                $row = array();
                $totalLabel = $this->getLanguage()->translate('Total', 'labels', 'Report');
                $row[] = array('value' => $totalLabel, 'wrapper' => 'b');
                foreach ($reportResult['columns'] as $column) {
                    $sum = 0;
                    if (!empty($reportResult['sums'][$column])) {
                        $sum = $reportResult['sums'][$column];
                        $sum = rtrim(rtrim(number_format($sum, 2, $userDecimalMark, $userThousandSeparator), '0'), '.');
                    }
                    $row[] = array('value' => $sum, 'wrapper' => 'b');
                }
                $result[] = $row;
            }
            $reportHeader = ($depth == 1) ? $reportName : $reportResult['columnNameMap'][$column];
            $contents = $this->getTemplate('ReportSendingBody', $userLanguage);
            $contents = str_replace('{reportHeader}', $reportHeader, $contents);

            if ($showDescription) {
                $showDescription = false;
                $contents = str_replace('{reportDescription}', $reportDescription, $contents);
            } else {
                $contents = str_replace('{reportDescription}', '', $contents);
            }
            $emailBody .= $this->parseReportTable($contents, array(
                'columnNames' => $reportColumnNames,
                'reportResult' => $result
            ));
            if ($depth == 1) {
                break;
            }
        }
        $data['emailSubject'] = $emailSubject;
        $data['emailBody'] = $emailBody;

        return true;
    }

    protected function parseReportTable($content, $parseData)
    {
        $pattern = '\'\\{loop [a-z,A-Z,0-9]+\\}\'';
        $matches = null;
        $res = preg_match_all($pattern, $content, $matches);
        if (is_array($matches)) {
            foreach ($matches[0] as $match) {
                $blockName = substr($match, 6, -1);
                $blockParams = $this->parseLoop($content, $blockName);
                if (empty($blockParams)) {
                    continue;
                }
                $blockTextResult = '';
                if ($blockName == 'columnNames') {
                    foreach ($parseData['columnNames'] as $name) {
                        $blockTextResult .= str_replace('{value}', $name, $blockParams['content']);
                    }
                } else if($blockName == 'resultRows') {
                    $subBlockName = 'resultCols';
                    $subBlockParams = $this->parseLoop($blockParams['text'], $subBlockName);
                    if (empty($subBlockParams)) {
                        continue;
                    }
                    foreach ($parseData['reportResult'] as $cols) {
                        $subBlockTextResult = '';

                        foreach ($cols as $col) {
                            $value = (is_array($col)) ? $col['value'] : $col;
                            $subBlockTextResult .= str_replace('{value}', $value, $subBlockParams['content']);
                            $wrapperStart = '';
                            $wrapperEnd = '';
                            if ( is_array($col) && $col['wrapper']) {
                                $wrapperStart = '<' . $col['wrapper'] . '>';
                                $wrapperEnd = '</' . $col['wrapper'] . '>';
                            }
                            $subBlockTextResult = str_replace('{wrapperStart}', $wrapperStart, $subBlockTextResult);
                            $subBlockTextResult = str_replace('{wrapperEnd}', $wrapperEnd, $subBlockTextResult);
                        }
                        $blockTextResult .= str_replace($subBlockParams['text'], $subBlockTextResult, $blockParams['content']);

                    }
                } else if($blockName == 'resultCols') {
                    continue;
                }
                $content = str_replace($blockParams['text'], $blockTextResult, $content);
            }
        }
        return $content;
    }

    protected function parseLoop($content, $blockName) 
    {
        $firstPos =  strpos($content, '{loop ' . $blockName .'}');
        $lastPos =  strpos($content, '{/loop ' . $blockName .'}');
        if ($firstPos === false || $lastPos === false) {
            return false;
        }
        $blockText = substr($content, 
            $firstPos, 
            $lastPos + strlen('{/loop ' . $blockName .'}') - $firstPos
        );
        $blockPattern = substr(
            $blockText, 
            strlen('{loop ' . $blockName .'}') + 1,
            (-1) * strlen('{/loop ' . $blockName .'}') - 1
        );
        return array('text' => $blockText, 'content' => $blockPattern);
    }

    protected function getTemplate($name, $userLanguage)
    {
        $fileName = 'custom/Espo/Custom/Resources/templates/'.$name.'.'.$userLanguage.'.tpl';
        if (!file_exists($fileName)) {
            $fileName = 'application/Espo/Modules/Advanced/Resources/templates/'.$name.'.'.$userLanguage.'.tpl';
        }
        if (!file_exists($fileName)) {
            $fileName = 'custom/Espo/Custom/Resources/templates/'.$name.'.en_US.tpl';
        }
        if (!file_exists($fileName)) {
            $fileName = 'application/Espo/Modules/Advanced/Resources/templates/'.$name.'.en_US.tpl';
        }
        return file_get_contents($fileName);
    }

    public function sendEmail($data)
    {
        if (!is_array($data) || !isset($data['userId']) || !isset($data['emailSubject']) || !isset($data['emailBody'])) {
            $GLOBALS['log']->error('Report Sending: Not enough data for sending email. ' . print_r($data, true));
            return false;
        }
        $user = $this->getEntityManager()->getEntity('User', $data['userId']);
        if (empty($user)) {
            $GLOBALS['log']->error('Report Sending: No user with id ' . $data['userId']);
            return false;
        }
        $emailAddress = $user->get('emailAddress');
        if (empty($emailAddress)) {
            $GLOBALS['log']->error('Report Sending: User has no email address');
            return false;
        }
        $email = $this->getEntityManager()->getEntity('Email');
        $email->set('to', $emailAddress);
        $subject = $data['emailSubject'];
        $body = $data['emailBody'];
        $email->set('subject', $subject);
        $email->set('body', $body);
        $email->set('isHtml', true);
        $this->getEntityManager()->saveEntity($email);
        $emailSender = $this->mailSender;
        if ($this->smtpParams) {
            $emailSender->useSmtp($this->smtpParams);
        }
        $emailSender->send($email);
        $this->getEntityManager()->removeEntity($email);
    }
}
