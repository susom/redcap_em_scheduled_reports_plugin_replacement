<?php

namespace Stanford\ScheduledReports;

/**
 *
 */
class Reports
{
    /**
     * @var
     */
    private $title;
    /**
     * @var
     */
    private $project_id;
    /**
     * @var
     */
    private $user_id;
    /**
     * @var
     */
    private $api_token;
    /**
     * @var
     */
    private $report_id;
    /**
     * @var
     */
    private $format;
    /**
     * @var
     */
    private $raw_or_label;
    /**
     * @var
     */
    private $raw_or_label_headers;
    /**
     * @var
     */
    private $export_checkbox_label;

    /**
     * @var
     */
    public $prefix;

    /**
     * @var bool
     */
    private $test_mode = false;

    /**
     * @var
     */
    private $activation;

    /**
     * @var
     */
    private $file_name;

    /**
     * @var
     */
    private $file_length;

    /**
     * @param $record
     * @param $prefix
     */
    public function __construct($record, $prefix)
    {
        $this->setPrefix($prefix);
        foreach ($this as $key => $value) {
            if (array_key_exists($key, $record)) {
                $this->$key = $record[$key];
            }
        }
    }

    /**
     * @return bool
     */
    public function processReport()
    {
        $this->setFileName(APP_PATH_TEMP . date("YmdHis") . '_' . $this->getReportId() . '.' . $this->getFormat());

        $params = array(
            'token' => $this->getApiToken(),
            'content' => 'report',
            'report_id' => $this->getReportId(),
            'format' => $this->getFormat(),
            'returnFormat' => 'csv',
            'rawOrLabel' => $this->getRawOrLabel(),
            'rawOrLabelHeaders' => $this->getRawOrLabelHeaders(),
            'exportCheckboxLabel' => $this->getExportCheckboxLabel()
        );
        $usl = $this->buildAPIURL();
        $result = http_post($this->buildAPIURL(), $params);
        if (substr($result, 0, 6) === "ERROR:") {
            ScheduledReports::log($this->getPrefix(), '', $result . " in " . __FUNCTION__);
            return false;
        }
        $this->setFileLength(strlen($result));
        $result = file_put_contents($this->getFileName(), $result);
        return true;
    }

    /**
     * @return string
     */
    private function buildAPIURL()
    {
        return sprintf(
            "%s://%s/api/",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME']
        );
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getProjectId()
    {
        return $this->project_id;
    }

    /**
     * @param mixed $project_id
     */
    public function setProjectId($project_id): void
    {
        $this->project_id = $project_id;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getApiToken()
    {
        return $this->api_token;
    }

    /**
     * @param mixed $api_token
     */
    public function setApiToken($api_token): void
    {
        $this->api_token = $api_token;
    }

    /**
     * @return mixed
     */
    public function getReportId()
    {
        return $this->report_id;
    }

    /**
     * @param mixed $report_id
     */
    public function setReportId($report_id): void
    {
        $this->report_id = $report_id;
    }

    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param mixed $format
     */
    public function setFormat($format): void
    {
        $this->format = $format;
    }

    /**
     * @return mixed
     */
    public function getRawOrLabel()
    {
        return $this->raw_or_label;
    }

    /**
     * @param mixed $raw_or_label
     */
    public function setRawOrLabel($raw_or_label): void
    {
        $this->raw_or_label = $raw_or_label;
    }

    /**
     * @return mixed
     */
    public function getRawOrLabelHeaders()
    {
        return $this->raw_or_label_headers;
    }

    /**
     * @param mixed $raw_or_label_headers
     */
    public function setRawOrLabelHeaders($raw_or_label_headers): void
    {
        $this->raw_or_label_headers = $raw_or_label_headers;
    }

    /**
     * @return mixed
     */
    public function getExportCheckboxLabel()
    {
        return $this->export_checkbox_label;
    }

    /**
     * @param mixed $export_checkbox_label
     */
    public function setExportCheckboxLabel($export_checkbox_label): void
    {
        $this->export_checkbox_label = $export_checkbox_label;
    }

    /**
     * @return mixed
     */
    public function getTestMode()
    {
        return $this->test_mode;
    }

    /**
     * @param mixed $test_mode
     */
    public function setTestMode($test_mode): void
    {
        $this->test_mode = $test_mode;
    }

    /**
     * @return mixed
     */
    public function getActivation()
    {
        return $this->activation;
    }

    /**
     * @param mixed $activation
     */
    public function setActivation($activation): void
    {
        $this->activation = $activation;
    }

    /**
     * @return mixed
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param mixed $prefix
     */
    public function setPrefix($prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * @param mixed $file_name
     */
    public function setFileName($file_name): void
    {
        $this->file_name = $file_name;
    }

    /**
     * @return mixed
     */
    public function getFileLength()
    {
        return $this->file_length;
    }

    /**
     * @param mixed $file_length
     */
    public function setFileLength($file_length): void
    {
        $this->file_length = $file_length;
    }


}