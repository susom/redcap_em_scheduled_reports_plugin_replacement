<?php

namespace Stanford\ScheduledReports;

/**
 *
 */
class Emails extends \Message
{
    /**
     * @var
     */
    private $email_to;
    /**
     * @var
     */
    private $email_bcc;
    /**
     * @var
     */
    private $email_from;
    /**
     * @var
     */
    private $email_subject;
    /**
     * @var
     */
    private $email_body;

    /**
     * @var
     */
    private $filename;
    /**
     * @var
     */
    private $file_length;


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
    public $prefix;

    /**
     * @param $record
     * @param $prefix
     */
    public function __construct($record, $prefix)
    {
        parent::__construct();
        $this->setPrefix($prefix);
        foreach ($this as $key => $value) {
            if (array_key_exists($key, $record)) {
                $this->$key = $record[$key];
            }
        }

        // TEST MODE WILL SEND THE REPORT EMAIL TO THE REPORT TO REQUESTER EMAIL INSTEAD.
        if ($this->test_mode && $record['requestor_email']) {
            $test_message = "<br><hr style='color: #666;'><div style='color: #666; font-size: smaller;'>[TEST ALERT] " .
                "This is a scheduled report test.  The <b>to:</b> address ({$this->email_to}) was replaced with " .
                "the requestor's email ({$record['requestor_email']}) and any <b>bcc:</b> was removed. " .
                "To return and edit this scheduled report, " .
                "please visit <a href='{$record['survey_url']}' target='_BLANK'>{$record['survey_url']}</a></div>";
            $this->setEmailBody($this->getEmailBody() . $test_message);
            $this->setEmailTo($record['requestor_email']);
        }
    }


    /**
     * @param $fileLengh
     * @param $fileName
     * @return bool
     */
    public function sendEmail($fileLengh, $fileName)
    {
        if ($fileLengh == 1) {
            $this->email_body .= "<br> The report contained no records.";
        }
        $body = $this->email_body . "<br><hr style='color: #666;'><div style='font-size:smaller; color: #666;'>This report was created by " .
            "{$this->webauth_user} - please contact {$this->requestor_email} or " .
            "redcap-help@lists.stanford.edu to stop delivery.  [Scheduled Report #{$this->record_id}]";

        $this->setTo($this->getEmailTo());
        $this->setFrom($this->getEmailFrom());
        $this->setSubject($this->getEmailSubject());
        if (!empty($this->getEmailBcc())) $this->setBcc($this->getEmailBcc());
        $this->setBody(nl2br($body));
        if ($fileLengh > 1) {
            $this->setAttachment($fileName);
        }

        return $this->send();
    }

    /**
     * @return mixed
     */
    public function getEmailTo(): mixed
    {
        return $this->email_to;
    }

    /**
     * @param mixed $email_to
     */
    public function setEmailTo(mixed $email_to): void
    {
        $this->email_to = $email_to;
    }

    /**
     * @return mixed
     */
    public function getEmailBcc()
    {
        return $this->email_bcc;
    }

    /**
     * @param mixed $email_bcc
     */
    public function setEmailBcc($email_bcc): void
    {
        $this->email_bcc = $email_bcc;
    }

    /**
     * @return mixed
     */
    public function getEmailFrom()
    {
        return $this->email_from;
    }

    /**
     * @param mixed $email_from
     */
    public function setEmailFrom($email_from): void
    {
        $this->email_from = $email_from;
    }

    /**
     * @return mixed
     */
    public function getEmailSubject()
    {
        return $this->email_subject;
    }

    /**
     * @param mixed $email_subject
     */
    public function setEmailSubject($email_subject): void
    {
        $this->email_subject = $email_subject;
    }

    /**
     * @return mixed
     */
    public function getEmailBody()
    {
        return $this->email_body;
    }

    /**
     * @param mixed $email_body
     */
    public function setEmailBody($email_body): void
    {
        $this->email_body = $email_body;
    }

    /**
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param mixed $filename
     */
    public function setFilename($filename): void
    {
        $this->filename = $filename;
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


}