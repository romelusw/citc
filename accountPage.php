<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
include_once("volunteerSignUp.php");
include_once("common_utils/functions.php");

// Ensure user is valid
require("verifyUser.php");
?>

<?php $pageTitle = "Volunteer Administration";
include("header.php"); ?>
<div id="content">
<?php if ($isAdmin): ?>
    <div id="AdminBlock">

    </div>
<?php endif; ?>
<div class="tooltip">
    <span class="tip_arrow"></span>

    <h3>Take a look back at past events.</h3>

    <p>This section allows you to take a look at the party dates
        created prior to the given year and see the individuals who
        were registered.</p>
    <button data-step="0" data-next="1" data-tipcontext="#tip1"
            class="formButton right">Next <i
            class="icon-chevron-right"></i></button>
</div>
<div class="tooltip">
    <span class="tip_arrow"></span>

    <h3>Create/View this years events.</h3>

    <p>This section shows you all the events created for the
        current year and allows to create new events.</p>
    <button data-step="1" data-next="-1" data-tipcontext="#tip2"
            class="formButton right">Next <i
            class="icon-chevron-right"></i></button>
</div>
<div class="tooltip">
    <span class="tip_arrow"></span>

    <h3>Process those who have registered.</h3>

    <p>This section allows you to accept/deny certain volunteers
        for a specific event day</p>
    <button data-step="-1" data-tipcontext="#tip3"
            class="formButton right">Close
    </button>
</div>
<div class="centerForm">
    <div id="newPartyForm" class="hidden">
        <?
        /**
         * Sums an array of numeric values.
         *
         * @param $arr array containing numeric values
         * @return int the total value summed up
         */
        function calcTotal($arr) {
            $ret = 0;
            foreach ($arr as $val) {
                $ret += $val;
            }
            return $ret;
        }

        if (isset($_POST["pdate"]) && isset($_POST["ptitle"])) {
            $app->createNewEvent($_POST["pdate"], calcTotal($_POST["pmaxreg"]));
            for ($i = 0; $i < count($_POST["ptitle"]); $i++) {
                $app->createNewPosition($_POST["ptitle"][$i],
                    $_POST["pdescription"][$i], $_POST["pmaxreg"][$i],
                    $_POST["pdate"]);
            }
        }
        ?>

        <form id="newEventForm" class="card animate"
              action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
            <section>
                <div class="directions">
                    <h2 class="numbers">
                        <span class="regularH2">Choose an event date</span>
                    </h2>

                    <p>Choose a date that you will be in need of
                        volunteers. This can be a date in the future or
                        one that you have already created in case you
                        need to add more positions.
                    </p>
                </div>
                <div class="newEventStep">
                    <span id="dateAltCal" class="ll-skin-latoja"></span>
                    <input type="text" name="pdate" id="dateCal"
                           style='display: none;'/>
                </div>
            </section>

            <section>
                <div class="directions">
                    <h2 class="numbers">
                            <span
                                class="regularH2">Create new volunteer shifts</span>
                    </h2>

                    <p>Each event day needs to have a "shift" which
                        describes the time slot that can be chosen as
                        well as a position of work.</p>
                </div>
                <div class="newEventStep">
                    <fieldset id="vol_pos">
                        <input type="text" class="formField" name="ptitle[]"
                               placeholder="Position Title"/>
                        <textarea maxlength="700" name="pdescription[]"
                                  class="formField"
                                  placeholder="Description"></textarea>

                        <div class="counter">
                            <input name="pmaxreg[]" class="formField"
                                   type="text" placeholder="0"/>

                            <div class="counter-incrementers">
                                <button class="subCount"
                                        data-increment="-1">
                                    <i class="icon-minus"></i>
                                </button>
                                <button class="addCount" data-increment="1">
                                    <i class="icon-plus"></i>
                                </button>
                            </div>
                        </div>
                    </fieldset>
                    <button id="add_position" style="float:right"
                            class="formButton">
                        <i class='icon-plus-sign'>&nbsp;</i>Add
                    </button>
                </div>
            </section>

            <input type="submit" class="formButton right" value="submit"/>

            <div class="clear"></div>
        </form>
    </div>
</div>
<div id="overlay">
    <script type="text/javascript">
        $(function () {
            // var disabledDays = "
            <?= $app->getThisYearsEvents(); ?>".split("");
            $("#dateAltCal").datepicker({
                altFormat: "yy-mm-dd",
                dateFormat: "DD MM dd, yy",
                altField: "#dateCal",
                minDate: 0,
                constrainInput: true
            });
        });
    </script>
</div>
<section id="headerbar">
    <p style="float:left; font-weight: bold;">Volunteer Dashboard</p>
    <?php
    $message = "Good ";
    $hour = intval(date("G"));
    switch ($hour) {
        case $hour <= 11: // 12am - 11am
            $message .= "Morning, ";
            break;
        case $hour <= 17: // 12pm - 5pm
            $message .= "Afternoon, ";
            break;
        case $hour <= 23: // 6pm - 11pm
            $message .= "Evening, ";
            break;
    }
    ?>
    <p><?php echo $message . "<span style='font-weight:bold; margin-right:7px;'>"
            . $u_email . "</span>" ?>
        <a href="logout.php" title="Logout" class="button"><i
                class='icon-off'>&nbsp;</i>Logout</a></p>
    <span class="clear"></span>
</section>
<section id="sidebar">
    <div class="sidebar_list">
        <h3 id="tip1">Past Volunteer Events</h3>
        <?= $app->displayPastEventsList(); ?>
    </div>
    <div class="sidebar_list">
        <h3 id="tip2">Current Volunteer Events</h3>
        <?= $app->displayCurrEventsList(); ?>
        <button id="add_event" style='width:100%' class="bbutton"><i
                class='icon-plus-sign'></i>
            Add New Event
        </button>
    </div>
</section>
<section id="mainbody">
    <div id="left">
        <div id="volCalendar">
            <h3 id="tip3">Manage Volunteers</h3>
            <?= $app->displayEventCalendar(date("m"), date("Y")); ?>
        </div>
        <div id="specificDate"></div>
        <span class="clear"></span>
    </div>
    <div id="right">
        <div id="volunteerDates"></div>
    </div>
</section>
<span class="clear"></span>
</div>
<?php include("footer.php"); ?>
