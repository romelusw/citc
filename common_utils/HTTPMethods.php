<?php
/**
 * A pseudo "enum" interface for HTTP methods.
 *
 * @author Woody Romelus
 */
interface HTTPMethods {
    /**
     * The HTTP GET constant.
     */
    const GET = "GET";

    /**
     * The HTTP POST constant.
     */
    const POST = "POST";

    /**
     * The HTTP PUT constant.
     */
    const PUT = "PUT";

    /**
     * The HTTP DELETE constant.
     */
    const DELETE = "DELETE";
}