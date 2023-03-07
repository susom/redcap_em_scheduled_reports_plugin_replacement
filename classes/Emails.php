<?php

namespace Stanford\ScheduledReports;

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

    public function __construct($record)
    {
        parent::__construct();
        foreach ($this as $key => $value) {
            if (array_key_exists($key, $record)) {
                $this->$key = $record[$key];
            }
        }
    }
}