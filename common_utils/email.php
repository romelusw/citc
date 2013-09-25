<?php

/**
 * An Email transport class.
 * Uses defined headers from RFC 2076
 * <http://www.ietf.org/rfc/rfc2076.txt>
 *
 * @author Woody Romelus
 */
class EmailTransport {

    private $subject;
    private $message;
    private $from;
    private $cc;
    private $bcc;

    /**
     * Default Constructor
     *
     * @param $sub the email subject
     * @param $msg the email body content
     * @param $frm the email from address
     * @param string $carbon the email 'cc' address(es)
     * @param string $bcarbon the email 'bcc' address(es)
     */
    function __construct($sub, $msg, $frm, $carbon = "", $bcarbon = "") {
        $this->subject = $sub;
        $this->message = $msg;
        $this->from = $frm;
        $this->cc = $carbon;
        $this->bcc = $bcarbon;
    }

    /**
     * Sends an email message to the specified email address(es).
     *
     * @param $address the email recipient(s)
     * @return bool indicating if the email was successfully accepted for
     *              delivery
     */
    public function sendMail($address) {
        return mail($address, $this->subject, $this->message,
            $this->getHeaders());
    }

    /**
     * Builds the email header content.
     *
     * @return string the email headers
     */
    public function getHeaders() {
        return "From: " . $this->from . PHP_EOL .
        "CC: " . $this->cc . PHP_EOL .
        "BCC: " . $this->bcc . PHP_EOL .
        "Date: " . date("d M y");
    }

    /**
     * Getter for email properties.
     *
     * @param $prop the property to retrieve
     * @return mixed the value of the email property
     */
    function __get($prop) {
        return $this->$prop;
    }
}