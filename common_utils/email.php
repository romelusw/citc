<?php

/**
 * An Email transport class
 * Uses defined headers from RFC 2076 
 * <http://www.ietf.org/rfc/rfc2076.txt>
 *
 * @author Woody Romelus
 */
class EmailTransport {

    // Global Variables
    private $subject;
    private $message;
    private $from;
    private $cc;
    private $bcc;

    // Constructor
    function __construct($sub, $msg, $frm, $carbon = "", $bcarbon = "") {
        $this->subject = $sub;
        $this->message = $msg;
        $this->from = $frm;
        $this->cc = $carbon;
        $this->bcc = $bcarbon;
    }

    // Getters
    public function getSubject() { return $this->subject; } 
    public function getMessage() { return $this->message; } 
    public function getFrom() { return $this->from; } 
    public function getCc() { return $this->cc; } 
    public function getBcc() { return $this->bcc; } 

    /**
     * Sends an email message to the specified email address(es)
     *
     * @param (String) $address The email recipient(s)
     * @return (Boolean) flag indicating if the email was successfully accepted for delivery
     */
    public function sendMail($address) {
        return mail($address, $this->getSubject(), $this->getMessage(), $this->getHeaders());
    }

    /**
     * Builds the email header content
     *
     * @return (String) email headers
     */
    public function getHeaders() {
        return "From: " . $this->getFrom() . "\r\n" . 
            "CC: " . $this->getCc() . "\r\n" . 
            "BCC: " . $this->getBcc() . "\r\n" .
            "Date: " . date("d M y");
    }
}
