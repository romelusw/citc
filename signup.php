<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
include_once("common_utils/functions.php");
include_once("volunteerSignUp.php");

// Global Variables
$errorMessages = array();
$show = "form";
$app = new VolunteerAppCreator();

if ($app->eventsFull()) $show = "nonAvailable";

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once("common_utils/formValidator.php");

    $vol_firstName = Utils::normalize($_POST["vol_firstName"]);
    $vol_lastName = Utils::normalize($_POST["vol_lastName"]);
    $vol_email = Utils::normalize($_POST["vol_email"]);
    $vol_volPhone = str_replace("-", "", $_POST["vol_phone"]);
    $vol_volDay = $_POST["volDay"];
    $vol_isGroup = intval($_POST["vol_isGroup"] == "on");
    $vol_isYouthGroup = intval($_POST["vol_isYthGroup"] == "on");
    $vol_chaperoneSize = $_POST["youth_chaperoneSize"];
    $vol_groupSize = $_POST["num_volunteers"];
    $vol_groupName = $_POST["group_name"];
    $vol_pos = $_POST["vol_position"];

    $validator = new FormValidator();
    $fields = array(
        "First Name" => array("non_empty_text" => $vol_firstName),
        "Last Name" => array("non_empty_text" => $vol_lastName),
        "Email" => array("email" => $vol_email),
        "Volunteer Day" => array("non_empty_text" => $vol_volDay),
        "Telephone Number" => array("tel" => $vol_volPhone)
    );

    if ($validator->validate($fields)) {
        $result = $app->createVolunteer($vol_firstName, $vol_lastName,
            $vol_email, $vol_volPhone, $vol_volDay, $vol_isGroup,
            $vol_isYouthGroup, $vol_chaperoneSize, $vol_groupSize,
            $vol_groupName, $vol_pos);

        // Determine which view to show based on the result
        switch ($result[0]["sqlstate"]) {
            case "70000":
                $show = "invalidPartyDate";
                break;
            case "70001":
                $show = "spaceNotAvailable";
                break;
            case "23000":
                $show = "dupRegistration";
                break;
            default:
                $show = "spaceAvailable";
                break;
        }
    } else {
        $GLOBALS["errorMessages"] = $validator->getErrors();
    }
}
?>

<?php $pageTitle = "Volunteer Sign Up Form"; include("header.php"); ?>

    <? switch ($show) { case "form": ?>
        <div class="generic-form center">
            <img class="center" src="http://christmasinthecity.org/wp-content/uploads/CITC-Logo.png"/>
            <h1>Sign up to be a Volunteer!</h1>

            <form class="card" id="signup-form" action="<? $_SERVER["PHP_SELF"] ?>" method="post">
                <fieldset id="f1">
                    <div class="form-field-section">
                        <?= Utils::generateUIError($errorMessages['First Name']); ?>
                        <input type="text" id="signup-fname" class="form-field validate"
                               name="vol_firstName" placeholder="First Name"
                               value="<?= $_POST["vol_firstName"]; ?>"/>
                        <label for="signup-fname">
                            <i class="fa fa-user"></i>
                            <span class="require-icon">*</span>
                        </label>

                        <?= Utils::generateUIError($errorMessages['Last Name']); ?>
                        <input type="text" class="form-field validate"
                               name="vol_lastName" placeholder="Last Name"
                               value="<?= $_POST["vol_lastName"] ?>"/>
                    </div>

                    <div class="form-field-section">
                        <?= Utils::generateUIError($errorMessages['Email']); ?>
                        <label for="signup-email"><i class="fa fa-envelope"></i>
                            <span class="require-icon">*</span>
                        </label>
                        <input type="email" id="signup-lname" class="form-field validate"
                               name="vol_email" placeholder="Email Address"
                               value="<?= $_POST["vol_email"] ?>"/>
                    </div>

                    <div class="form-field-section">
                        <?= Utils::generateUIError($errorMessages['Telephone Number']); ?>
                        <label for="signup-tel"><i class="fa fa-phone"></i><span
                                class="require-icon">*</span></label>
                        <input type="tel" id="signup-tel" class="form-field validate"
                               pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"
                               placeholder="888-888-8888" name="vol_phone"
                               title="888-888-8888"
                               value="<?= $_POST["vol_phone"] ?>"/>
                    </div>
                </fieldset>
                <fieldset id="f2">
                    <div class="form-field-section">
                        <?= Utils::generateUIError($errorMessages['Volunteer Day']); ?>
                        <i class="icon-calendar">&nbsp;</i>&nbsp;Volunteer Day<span
                            class="require-icon">*</span>
                    </div>
                    <div id="signUpSelect">
                        <select name="volDay" id="volunteer-day" class="validate">
                            <?= $app->displayAvailVolDateOptions(); ?>
                        </select>
                    </div>
                </fieldset>
                <fieldset id="f3">
                    <div class="form-field-section">
                        <?= Utils::generateUIError($errorMessages['Volunteer Position']); ?>
                        <label for="signup-pos">
                            <i class="fa fa-suitcase"></i> Position(s)
                            <span class="require-icon">*</span>
                            <p>Refer to the
                                <a href="http://christmasinthecity.org/volunteer/"
                                   title="View full  descriptions"
                                   target="_blank">
                                    volunteer tab
                                </a>
                                 on the CITC website for complete position
                                descriptions. (Scroll down for more positions)</p>
                            <p style='text-align:center' class="bold">Click on
                                a position of interest.</p>
                        </label>
                        <input type="hidden" id="signup-pos" name="vol_position"
                               class="validate"/>
                        <ul id="positionList"></ul>
                    </div>
                </fieldset>
                <fieldset id="f4">
                    <input type="checkbox" id="vol_isGroup" name="vol_isGroup"
                           onclick="$('#group-form').toggle();"
                        <?= $_POST['vol_isGroup'] == 'on' ? 'checked' : '' ?>/>
                    <label for="vol_isGroup">Are you registering for others?</label>

                    <!-- GROUP -->
                    <div id="group-form" class="hidden">
                        <input type="checkbox" id="vol_isYthGroup" name="vol_isYthGroup"
                               onclick="$('#youth-group-form').toggle();"
                            <?= $_POST['vol_isYthGroup'] == 'on' ? 'checked' : '' ?>/>
                        <label for="vol_isYthGroup">Is it a Youth Group?</label>

                        <!-- YOUTH GROUP -->
                        <div id="youth-group-form" class="hidden">
                            <p>
                                <span class="bold">Note</span><br/>
                                Youth groups must have at least
                                <span class="bold">1 adult chaperone for every 12
                                youths</span>
                            </p>
                            <label class="block" for="youth_chaperoneSize">Number of Chaperones</label>
                            <div class="counter">
                                <input name="youth_chaperoneSize" class="form-field"
                                       type="text" placeholder="0" id="youth_chaperoneSize"
                                       value="<?= $_POST["youth_chaperoneSize"] ?>"/>

                                <div class="counter-incrementers">
                                    <button class="subCount" onclick="return false;"
                                            data-increment="-1">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                    <button class="addCount" onclick="return false;"
                                            data-increment="1">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>

                        <label class="block" for="group_name">Group/Organization Title</label>
                        <input type="text" id="group_name" name="group_name"
                               placeholder="Name of Group" class="form-field"/>

                        <label class="block" style="margin-top:4px;" for="num_volunteers">
                            Number of volunteers including yourself (if this is a youth group add in number of chaperones)
                        </label>
                        <div class="counter">
                            <input name="num_volunteers" class="form-field"
                                   type="text" placeholder="0" id="num_volunteers"
                                   value="<?= $_POST["num_volunteers"] ?>"/>

                            <div class="counter-incrementers">
                                <button class="subCount" onclick="return false;"
                                        data-increment="-1">
                                    <i class="fa fa-minus"></i>
                                </button>
                                <button class="addCount" onclick="return false;"
                                        data-increment="1">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>

                    <div class="form-field-section">
                        <input type="submit" class="form-button right" value="submit"/>
                    </div>
                </fieldset>
            </form>

            <div class="clear"></div>
        </div>
    <? break; case "spaceAvailable": ?>
        <div class='notification center'>
            <h3>Thank you for registering as a volunteer!</h3>
            <p>
                Someone will be in touch with you soon to confirm your
                registration. If you have any questions or concerns please
                email
                <a href="mailto:volunteer@christmasinthecity.org?Subject=Questions%20About%20Volunteering.">
                    volunteer@christmasinthecity.org
                </a>
            </p>
            <div class="notification-action">
                <i class="icon-reply"></i>
                <a href="http://christmasinthecity.org" title="Head to the Christmas in the city homepage.">
                    Visit the Christmas in the City Home Page
                </a>
            </div>
        </div>
    <? break; case "spaceNotAvailable": ?>
        <div class='notification center'>
            <h3 class="red-color">
                Unfortunately we do not have enough openings for the number
                of volunteers you specified.
            </h3>
            <p>
                We appreciate you attempting to help serve the families and
                children that really need your help. Please try again with a
                different number of volunteers.
            </p>
            <div class="notification-action">
                <i class="icon-reply"></i>
                <a href="http://christmasinthecity.org" title="Head to the Christmas in the city homepage.">
                    Visit the Christmas in the City Home Page
                </a>
            </div>
        </div>
    <? break; case "invalidPartyDate"; ?>
        <div class='notification center'>
            <h3 class="red-color">
                The date specified is not a valid party date.
            </h3>
            <p>
                Please choose a valid party date.
            </p>
            <div class="notification-action">
                <i class="icon-reply"></i>
                <a href="http://christmasinthecity.org" title="Head to the Christmas in the city homepage.">
                    Visit the Christmas in the City Home Page
                </a>
            </div>
        </div>
    <? break; case "dupRegistration"; ?>
        <div class='notification center'>
            <h3 class="yellow-color">
                You have already registered as a volunteer for this day.
            </h3>
            <p>
                Please choose another party date that you have not yet registered for.
            </p>
            <div class="notification-action">
                <i class="icon-reply"></i>
                <a href="<?= $_SERVER["SELF"]; ?>" title="Try another day.">
                    Choose another available day to volunteer.
                </a>
            </div>
        </div>
    <? break; case "nonAvailable"; ?>
    <div class='notification center'>
        <h3 class="red-color">
            Unfortunately we have no openings for volunteers at this time.
        </h3>
        <p>
            We appreciate you attempting to help serve the families and
            children that really need your help. Please try again at a later time.
        </p>
        <div class="notification-action">
            <i class="icon-reply"></i>
            <a href="http://christmasinthecity.org" title="Head to the Christmas in the city homepage.">
                Visit the Christmas in the City Home Page
            </a>
        </div>
    </div>
    <? break; } ?>

<?php include("footer.php"); ?>