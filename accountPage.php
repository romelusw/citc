<?php
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
include_once("volunteerSignUp.php");
include_once("common_utils/functions.php");

// Ensure user is valid
require("verifyUser.php");
?>

<?php $pageTitle = "Volunteer Administration"; include("header.php"); ?>
    <div id="content">
        <?php if($isAdmin): ?>
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
                    class="bbutton">Next <i class="icon-chevron-right"></i></button>
        </div>
        <div class="tooltip">
            <span class="tip_arrow"></span>
            <h3>Create/View this years events.</h3>
            <p>This section shows you all the events created for the
                current year and allows to create new events.</p>
            <button data-step="1" data-next="-1" data-tipcontext="#tip2"
                    class="bbutton">Next <i class="icon-chevron-right"></i></button>
        </div>
        <div class="tooltip">
            <span class="tip_arrow"></span>
            <h3>Process those who have registered.</h3>
            <p>This section allows you to accept/deny certain volunteers
                for a specific event day</p>
            <button data-step="-1" data-tipcontext="#tip3"
                    class="bbutton">Close</button>
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

            <form id="newEventForm" class="card animate" action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
                <section>
                    <div class="directions">
                        <h2 class="numbers">Pick a Date</h2>
                    </div>
                    <div>
                        <span id="dateAltCal"></span>
                        <input type="text" name="pdate" id="dateCal" style='display: none;' />
                    </div>
                </section>
                <section>
                    <div class="directions">
                        <h2>Volunteer Positions of ole</h2>
                    </div>
                    <div>
                        <?= $app->displayPositionsCreated(); ?>
                    </div>
                </section>
                <section>
                    <div class="directions">
                        <h2>Create new Volunteer Positions</h2>
                    </div>
                    <div class="">
                        <fieldset id="vol_pos">
                            <input type="text" class="formField" name="ptitle[]" placeholder="Position Title" />
                            <textarea maxlength="700" name="pdescription[]" class="formField" placeholder="Description"></textarea>
                            <div class="counter">
                                <button class="subCount" data-increment=-1><i class="icon-minus"></i></button>
                                <input name="pmaxreg[]" type="text"/>
                                <button class="addCount" data-increment=1><i class="icon-plus"></i></button>
                            </div>
                        </fieldset>
                    </div>
                </section>
                
                <button id="add_position" class="button_r">
                    <i class='icon-plus-sign'></i>Add
                </button>
                <input type="submit" class="button_r" value="submit"/>
                <button class="close_form" onclick="return false;">Close</button>
            </form>
            </div>
        </div>
        <div id="overlay">
            <script type="text/javascript">
                $(function () {
                    var disabledDays = "<?= $app->getThisYearsEvents(); ?>".split(" ");
                    $("#dateAltCal").datepicker({
                        altFormat: "yy-mm-dd",
                        dateFormat: "DD MM dd, yy",
                        altField: "#dateCal",
                        minDate: 0,
                        constrainInput: true,
                        beforeShowDay: undefinedEventDay
                    });

                    /**
                     * Determines if a date is restricted from being chosen
                     * @param date the calendar date to check against
                     * @returns {Array} containing true/false values
                     */
                    function undefinedEventDay(date) {
                        var d = new Date(date);
                        var formattedDate = d.getFullYear() + "-"
                            + ("0" + (d.getMonth() + 1)).slice(-2) + "-"
                            + ("0" + d.getDate()).slice(-2);

                        if (disabledDays.indexOf(formattedDate) > -1) {
                            return [false];
                        } else {
                            return [true];
                        }
                    }
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
                    Add New Event</button>
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
