<?php
/**
 * An object providing support for HTTP requests.
 *
 * @author Woody Romelus
 */
class HTTPRequest {
    private $url;
    private $mode = "r";
    private $contents;
    private $body;

    /**
     * Default Constructor.
     */
    public function __construct() {
        // Empty Body
    }

    /**
     * Acts as a http "GET" request to a url location
     */
    function get() {
        $filePointer = fopen("http://" . $this->url, $this->mode);

        if ($filePointer) {
            $contents = stream_get_meta_data($filePointer);
            $body = stream_get_line($filePointer, $contents["unread_bytes"]);
        }
    }

    /**
     * Acts as a http "POST" request to a url location
     *
     * @param (Array) $data the form data to send with the POST
     */
    function post($data) {
        $wrapperOpts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($data, '', '&'),
                'timeout' => 200
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
