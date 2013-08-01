<?php
/**
 * HTTP request object.
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
     * Acts as a http "GET" request to a url location.
     */
    function get() {
        $filePointer = fopen("http://" . $this->url, $this->mode);

        if ($filePointer) {
            $contents = stream_get_meta_data($filePointer);
            $body = stream_get_line($filePointer, $contents["unread_bytes"]);
        }
    }

    /**
     * Acts as a http "POST" request to a url location.
     *
     * @param $data the form data to send along with the POST
     */
    function post($data) {
        $wrapperOpts = array('http' =>
        array(
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
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
     * @param $prop the property to set
     * @param $val the value of the property to set
     */
    public function __set($prop, $val) {
        $this->$prop = $val;
    }

    /**
     * Getter for HTTP properties.
     *
     * @param $prop the property to retrieve
     * @return mixed the value of the property
     */
    public function __get($prop) {
        return $this->$prop;
    }
}