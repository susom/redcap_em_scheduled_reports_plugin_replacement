<?php

namespace Stanford\ScheduledReports;

/**
 *
 */
class Records
{

    /**
     * @var Emails
     */
    private Emails $email;

    /**
     * @var Reports
     */
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

    /**
     * @var bool
     */
    private $test_mode = false;

    /**
     * @var
     */
    public $prefix;

    /**
     * @var
     */
    public $status;

    /**
     * @param $record
     * @param $prefix
     */
    public function __construct($record, $prefix)
    {
        $this->setPrefix($prefix);
        foreach ($this as $key => $value) {
            if (array_key_exists($key, $record)) {
                if (is_array($record[$key])) {
                    $this->$key = array_pop($record[$key]);
                } else {
                    $this->$key = $record[$key];
                }

            }
        }

        $this->setEmail(new Emails($record, $prefix));
        $this->setReport(new Reports($record, $prefix));
    }

    /**
     * @return bool
     */
    public function processRecord()
    {
        if ($this->getActivation()) {
            if ($this->isRecordValid()) {
                if ($this->getReport()->processReport()) {
                    if ($this->getEmail()->sendEmail($this->getReport()->getFileLength(), $this->getReport()->getFileName())) {
                        ScheduledReports::log($this->getPrefix(), $this->getRecordId(), "Report Delivered", !$this->getTestMode());
                        \REDCap::logEvent("Scheduled Report Delivery", "ScheduleID: {$this->record_id}\nto: {$this->getEmail()->getEmailTo()}\nsubject: {$this->getEmail()->getEmailSubject()}", "", NULL, NULL, $this->getReport()->getProjectId());
                        return true;
                    } else {
                        ScheduledReports::log($this->getPrefix(), $this->getRecordId(), "Error sending email");
                        return false;
                    }
                }
                $this->setStatus('Could not process Report. please check logs for more details!');
                return false;
            }
            $this->setStatus('Record is not valid');
            return false;
        }
        $this->setStatus('Record is not active');
        return false;
    }

    /**
     * @return bool
     */
    public function isRecordValid()
    {
        $sql = sprintf("SELECT * FROM redcap_user_rights WHERE api_token = '%s' ", db_escape($this->getReport()->getApiToken()));
        $q = db_query($sql);
        if (db_num_rows($q) != 1) {
            $error_msg = "Unable to validate API token";
            ScheduledReports::log($this->getPrefix(), $this->getRecordId(), "$error_msg: " . $sql);
            $this->error_msg = $error_msg;
            return false;
        }
        $row = db_fetch_assoc($q);
        if ($row['project_id'] != $this->getReport()->getProjectId()) {
            $this->error_msg = "Project ID does not match with API token";
            ScheduledReports::log($this->prefix, $this->record_id, $this->error_msg);
            return false;
        }
        if ($row['username'] != $this->getReport()->getUserId()) {
            $this->error_msg = "User ID does not match API token";
            ScheduledReports::log($this->prefix, $this->record_id, $this->error_msg);
            return false;
        }
        if ($this->getTestMode()) {
            $this->error_msg = "Running report as a test";
            ScheduledReports::log($this->prefix, $this->record_id, $this->error_msg);
            return true;
        }
        if ($this->getActivation() == 0) {
            // Report has not been activated
            $this->error_msg = "Report has not been activated so connot be run";
            ScheduledReports::log($this->prefix, $this->record_id, $this->error_msg);
            return false;
        }
        // See if the frequency is right
        // get number of days since last_sent_ts
        if (empty($this->getLastSentTs())) {
            // Never sent before - then the frequency must be right
            return true;
        } else {
            // Calculate some dates and deltas
            $d1 = strtotime($this->getLastSentTs());
            $d2 = strtotime(NOW);
            $interval_in_hours = rounddown(($d2 - $d1) / 3600);
            $interval_in_days = rounddown((($d2 - $d1) + 3600) / 86400); // Adding one hour of fudge

            // We never send twice on the same day
            if ($interval_in_days == 0) {
                $this->error_msg = "Trying to run $this->record_id again after only $interval_in_hours hours...";
                \Plugin::log($this->error_msg, "INFO");
                ScheduledReports::log($this->prefix, $this->record_id, $this->error_msg);
                return false;
            }

            // Daily
            if (($this->report_interval == 'daily')) return true;

            // Weekly - if it is Monday and we're weekly - go!
            if (($this->report_interval == 'weekly') and (date('w') == 1)) return true;

            // First of the Month
            if (($this->report_interval == 'monthly') and (date('j') == 1)) return true;

            // Custom
            if (($this->report_interval == 'custom') and ($interval_in_days >= $this->report_interval_custom)) return true;

            // Otherwise fail
            $this->error_msg = "Skipping - interval {$this->report_interval} is not true with $interval_in_days days";
            Plugin::log("Skipping - interval {$this->report_interval} is not true with $interval_in_days days", "DEBUG");
            ScheduledReports::log($this->prefix, $this->record_id, $this->error_msg);

            return false;
        }
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

    /**
     * @return mixed
     */
    public function getRecordId()
    {
        return $this->record_id;
    }

    /**
     * @param mixed $record_id
     */
    public function setRecordId($record_id): void
    {
        $this->record_id = $record_id;
    }

    /**
     * @return mixed
     */
    public function getWebauthUser()
    {
        return $this->webauth_user;
    }

    /**
     * @param mixed $webauth_user
     */
    public function setWebauthUser($webauth_user): void
    {
        $this->webauth_user = $webauth_user;
    }

    /**
     * @return mixed
     */
    public function getRequestorEmail()
    {
        return $this->requestor_email;
    }

    /**
     * @param mixed $requestor_email
     */
    public function setRequestorEmail($requestor_email): void
    {
        $this->requestor_email = $requestor_email;
    }

    /**
     * @return mixed
     */
    public function getReportInterval()
    {
        return $this->report_interval;
    }

    /**
     * @param mixed $report_interval
     */
    public function setReportInterval($report_interval): void
    {
        $this->report_interval = $report_interval;
    }

    /**
     * @return mixed
     */
    public function getReportIntervalCustom()
    {
        return $this->report_interval_custom;
    }

    /**
     * @param mixed $report_interval_custom
     */
    public function setReportIntervalCustom($report_interval_custom): void
    {
        $this->report_interval_custom = $report_interval_custom;
    }

    /**
     * @return mixed
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param mixed $log
     */
    public function setLog($log): void
    {
        $this->log = $log;
    }

    /**
     * @return mixed
     */
    public function getLastSentTs()
    {
        return $this->last_sent_ts;
    }

    /**
     * @param mixed $last_sent_ts
     */
    public function setLastSentTs($last_sent_ts): void
    {
        $this->last_sent_ts = $last_sent_ts;
    }

    /**
     * @return mixed
     */
    public function getSurveyUrl()
    {
        return $this->survey_url;
    }

    /**
     * @param mixed $survey_url
     */
    public function setSurveyUrl($survey_url): void
    {
        $this->survey_url = $survey_url;
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
    public function getErrorMsg()
    {
        return $this->error_msg;
    }

    /**
     * @param mixed $error_msg
     */
    public function setErrorMsg($error_msg): void
    {
        $this->error_msg = $error_msg;
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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }


}