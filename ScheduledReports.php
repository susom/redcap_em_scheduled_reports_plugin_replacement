<?php

namespace Stanford\ScheduledReports;

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
        if (isset($_GET['pid'])) {
            $this->getRecords();
            $a = 1;
        }
    }

    /**
     * @param $cronParameters
     * @return void
     */
    public function sendScheduledReportsCron($cronParameters)
    {
        // TODO
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
            $this->records[] = new Records($item[$this->getFirstEventId()]);
        }
    }

}
