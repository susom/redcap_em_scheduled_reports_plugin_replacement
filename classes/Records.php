<?php

namespace Stanford\ScheduledReports;

class Records
{

    private Emails $email;

    private Reports $report;
    /**
     * @var
     */
    private $record_id;
    /**
     * @var
     */
    private $webauth_user;
    /**
     * @var
     */
    private $requestor_email;

    /**
     * @var
     */
    private $report_interval;
    /**
     * @var
     */
    private $report_interval_custom;
    /**
     * @var
     */
    private $log;
    /**
     * @var
     */
    private $last_sent_ts;
    /**
     * @var
     */
    private $survey_url;
    /**
     * @var
     */
    private $activation;
    /**
     * @var
     */
    private $error_msg;

    public function __construct($record)
    {
        foreach ($this as $key => $value) {
            if (array_key_exists($key, $record)) {
                if (is_array($record[$key])) {
                    $this->$key = array_pop($record[$key]);
                } else {
                    $this->$key = $record[$key];
                }

            }
        }

        $this->setEmail(new Emails($record));
        $this->setReport(new Reports($record));
    }

    public function isRecordActive()
    {
        return $this->activation;
    }

    /**
     * @return Emails
     */
    public function getEmail(): Emails
    {
        return $this->email;
    }

    /**
     * @param Emails $email
     */
    public function setEmail(Emails $email): void
    {
        $this->email = $email;
    }

    /**
     * @return Reports
     */
    public function getReport(): Reports
    {
        return $this->report;
    }

    /**
     * @param Reports $report
     */
    public function setReport(Reports $report): void
    {
        $this->report = $report;
    }


}