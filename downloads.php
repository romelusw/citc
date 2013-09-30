<?php
include("volunteerSignUp.php");
$exportDate = $_GET["specificDate"];

if (isset($exportDate)) {
    $app = new VolunteerAppCreator();
    $fileName = "Volunteers_" . date("d-m-Y_", strtotime($exportDate))
        . microtime(true) . ".csv";

    // Prepare headers
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header('Content-Description: File Transfer');
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=\"$fileName\"");
    header("Expires: 0");
    header("Pragma: public");

    // Add db data into the stream
    $data = $app->retrieveRegisteredVolunteers($exportDate);
    $fh = @fopen('php://output', 'w');

    // Add column headers
    $columnNames = array();
    while ($finfo = $data->fetch_field()) {
        array_push($columnNames, $finfo->name);
    }
    fputcsv($fh, $columnNames);

    // Convert certain values to human readable text
    while ($result = $data->fetch_assoc()) {

        if ($result["Is a group?"] == 0) {
            $result["Is a group?"] = "Not a group";
        } else{
            $result["Is a group?"] = "Is a group";
        }

        if ($result["Is a Youth group?"] == 0) {
            $result["Is a Youth group?"] = "Not a group";
        } else{
            $result["Is a Youth group?"] = "Is a group";
        }

        switch ($result["Status"]) {
            case -1:
                $result["Status"] = "Pending";
                break;
            case 1:
                $result["Status"] = "Accepted";
                break;
            case 0:
                $result["Status"] = "Denied";
                break;
        }
        fputcsv($fh, $result);
    }

    fclose($fh);
    exit();
}