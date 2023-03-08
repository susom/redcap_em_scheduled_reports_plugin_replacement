<?php

namespace Stanford\ScheduledReports;

use ExternalModules\ExternalModules;

require_once('classes/Reports.php');
require_once('classes/Records.php');
require_once('classes/Emails.php');

/**
 *
 */
class ScheduledReports extends \ExternalModules\AbstractExternalModule
{

    /** @var Records[] */
    private $records;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        // Other code to run when object is instantiated
    }

    /**
     * @param $cronParameters
     * @return void
     */
    public function sendScheduledReportsCron()
    {
        foreach ($this->getRecords() as $record) {
            if ($record->processRecord()) {
                if (file_exists($record->getReport()->getFileName())) {
                    unlink($record->getReport()->getFileName());
                }
            } else {
                echo $record->getStatus();
            }
        }
        \REDCap::logEvent("Schedule Report Run Complete", "", "", NULL, NULL, $this->getSystemSetting('records-pid'));
    }

    /**
     * @return Records[]
     */
    public function getRecords(): array
    {
        if (!$this->records) {
            $this->setRecords();
        }
        return $this->records;
    }

    /**
     *
     */
    public function setRecords(): void
    {
        $param = array(
            'project_id' => $this->getSystemSetting('records-pid'),
            'return_format' => 'array',
            'filterLogic' => "[report_interval] <> '0'"
        );
        $q = \REDCap::getData($param);
        foreach ($q as $item) {
            $this->records[] = new Records($item[$this->getFirstEventId()], $this->PREFIX);
        }
    }

    /*
 * Write a message to the scheduled report log field
 * with an optional boolean to also update the timestamp
 */
    /**
     * @param $prefix
     * @param $record_id
     * @param $msg
     * @param $include_timestamp
     * @return void
     */
    public static function log($prefix, $record_id, $msg, $include_timestamp = false)
    {
        // Plugin::log($msg,"DEBUG","LOG");
        $data = array(
            \REDCap::getRecordIdField() => $record_id,
            'log' => "[" . date('Y-m-d H:i:s') . "] $msg"
        );
        // Plugin::log($data, "DEBUG", "Data Log from Cron");
        // Plugin::log("Include Timestamp:" . (int) $include_timestamp, "DEBUG");
        if ($include_timestamp == true) $data['last_sent_ts'] = date('Y-m-d H:i:s');
        \REDCap::saveData(ExternalModules::getSystemSetting($prefix, 'records-pid'), 'json', json_encode(array($data)));
    }
}
