<?php
class HTTPRequest {
    private $url;
    private $mode = "r";
    private $contents;
    private $body;

    // Default Constructor
    public function __construct() {
        // No Body
    }

    // Send GET Request
    function get() {
        $filePointer = fopen("http://" . $this->url, $this->mode);

        if ($filePointer) {
            $contents = stream_get_meta_data($filePointer);
            $body = stream_get_line($filePointer, $contents["unread_bytes"]);
        }
    }

    // Send POST Request
    function post($data) {
        $wrapperOpts = array('http' =>
            array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($data, '', '&'),
            'timeout' => 5,
            )
        );
        $streamContext = stream_context_create($wrapperOpts);
        $fp = fopen("http://" . $this->url, $this->mode, 0, $streamContext);
    }

    /**
     * Setter for HTTP properties.
     *
     * @param (String) $prop the property to set.
     * @param (String) $val the value to set $prop to.
     */
    public function __set($prop, $val) {
        $this->$prop = $val; 
    }

    /**
     * Getter for HTTP properties.
     *
     * @param (String) $prop the property to retrieve.
     * @return (String) the value of the HTTP property
     */
    public function __get($prop) {
        return $this->$prop; 
    }
}
