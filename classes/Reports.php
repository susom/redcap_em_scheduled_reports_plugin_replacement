<?php

namespace Stanford\ScheduledReports;

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

    public function __construct($record)
    {
        foreach ($this as $key => $value) {
            if (array_key_exists($key, $record)) {
                $this->$key = $record[$key];
            }
        }
    }
}