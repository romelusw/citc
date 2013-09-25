<?php
/**
 * Establishes a database connection & provides an interface into the
 * properties/methods of the data source.
 *
 * @author Woody Romelus
 */
class DatabaseConnector {

    public $connection;

    /**
     * Establishes a connection to the database.
     *
     * @param $host IP address or hostname of the DB server
     * @param $username the username
     * @param $password the password
     * @param null $db the database
     */
    public function connect($host, $username, $password, $db = null) {
        $this->connection = new mysqli($host, $username, $password, $db);

        if (!$this->connection) {
            die("Could not connect to database: "
                . $this->connection->connect_error());
        }
    }

    /**
     * Executes a query against the data source.
     *
     * @param $query the query to execute
     * @return mysqli_result the query results
     */
    public function runQuery($query) {
        return $result = $this->connection->query($query);
    }

    /**
     * Executes a Prepared SQL Query against the data source.
     *
     * @param $query the query to execute
     * @param $params the parameters to bind within the query
     * @return mixed either a flag indicating if the statement was successful
     * or the error for the statement
     * @throws Exception when the number of required arguments are not met
     */
    public function runPreparedQuery($query, $params) {
        // Check if we do not have the right amount of params for the query
        if (substr_count($query, "?") != count($params)) {
            throw new Exception("Incorrect number of arguments to
            'runPreparedQuery'.");
        }

        // Create Prepared Statement
        $pStatement = $this->connection->prepare($query);

        // Place the data types string in the beginning of the array
        array_unshift($params, $this->resolveTypes($params));
        // On the prepare statement, invoke 'bind_param' using 
        // the referenced parameters
        call_user_func_array(array(&$pStatement, 'bind_param'),
            $this->referenceArrayValues($params));

        $result = $pStatement->execute();

        // Handle errors after statement execution
        if ($result == false) {
            $result = $pStatement->error_list;
        }

        return $result;
    }

    /**
     * Determines the primitive types of an object.
     * Types: {i => "Integer",
     *         s => "String",
     *         d => "Double",
     *         b => "Blob"}
     *
     * @param $datum the data to inspect and find the data type
     * @return string containing all the types found
     */
    private function resolveTypes($datum) {
        $types = '';
        foreach ($datum as $para) {
            if (is_int($para)) {
                $types .= 'i';
            } elseif (is_float($para)) {
                $types .= 'd';
            } elseif (is_string($para)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
        }
        return $types;
    }

    /**
     * Creates a reference array for the array given.
     *
     * @param $arr the array to reference
     * @return array the referenced array
     */
    private function referenceArrayValues(&$arr) {
        $refs = array();
        foreach ($arr as $key => $value) {
            $refs[$key] = & $arr[$key];
        }
        return $refs;
    }

    /**
     * Closes the database connection.
     *
     * @return bool flag indicating if the connection was closed successfully
     */
    public function close() {
        return mysqli_close($this->connection);
    }

    /**
     * Protects against MySQL injection by escaping special characters
     * reserved within the MySQL charset.
     *
     * @param $datum the data to sanitize
     * @return string the escaped string
     */
    public function cleanSQLInputs($datum) {
        return $this->connection->real_escape_string($datum);
    }

    /**
     * Getter that retrieves a property from the connection object.
     *
     * @param $prop the property to retrieve
     * @return mixed the property value
     */
    public function __get($prop) {
        return $this->connection->$prop;
    }

    /**
     * Checks if a database table exists.
     *
     * @param $table the table to check for
     * @return bool flag indicating if the table exists
     */
    public function table_exists($table) {
        return $this->runQuery("SHOW TABLES LIKE '$table'")->num_rows > 0;
    }
}