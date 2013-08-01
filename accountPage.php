<?php
include_once("volunteerSignUp.php");
include_once("common_utils/functions.php");

// Ensure user is valid
require("verifyUser.php");
?>

<?php $pageTitle = "Volunteer Administation";
include("header.php"); ?>
    <div id="content">
        <div class="tooltip">
            <span class="tip_arrow"></span>

            <h3>Past Volunteer Events can be reviewed easily</h3>

            <p>This section displays all the parties created older than the current year.</p>
            <button data-step="0" data-next="-1" data-tipcontext="#headerbar">Next</button>
        </div>
        <div class="tooltip">
            <span class="tip_arrow"></span>

            <h3>Blah Blah</h3>

            <p>womp womp</p>
            <button data-step="-1" data-tipcontext="#sidebar">Close</button>
        </div>
        <div id="overlay">
            <script type="text/javascript">
                $(function () {
                    var disabledDays = "<?= $app->getThisYearsEvents(); ?>".split(" ");
                    $("#dateCal").datepicker({
                        dateFormat: "yy-mm-dd",
                        minDate: 0,
                        constrainInput: true,
                        beforeShowDay: undefinedEventDay
                    });

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
            <div id="newPartyForm" class="hidden">
                <?
                function calcTotal($arr) {
                    $ret = 0;
                    foreach ($arr as $val) {
                        $ret += $val;
                    }
                    return $ret;
                }

                if (isset($_POST["pdate"]) && isset($_POST["ptitle"])) {
                    $app->insertVolunteerDate($_POST["pdate"], calcTotal($_POST["pmaxreg"]));
                    for ($i = 0; $i < count($_POST["ptitle"]); $i++) {
                        $app->insertNewVolPosition($_POST["ptitle"][$i],
                            $_POST["pdescription"][$i], $_POST["pmaxreg"][$i],
                            $_POST["pdate"]);
                    }
                }
                ?>

                <form class="card animate" action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
                    <fieldset>
                        <legend>Insert Volunteer Date</legend>
                        <input type="text" name="pdate" id="dateCal" readonly='true'
                               placeholder="Click to choose date"/>
                    </fieldset>

                    <fieldset id="vol_pos">
                        <legend>Volunteer Position</legend>
                        <input type="text" name="ptitle[]" placeholder="Position Title" value="position title"/>
                        <textarea name="pdescription[]" placeholder="Description">Some description text</textarea>
                        <input type="number" name="pmaxreg[]" min="1" max="1000"/>
                    </fieldset>
                    <button id="add_position">+ Add</button>

                    <input type="submit" value="submit"/>

                    <button class="close_form" onclick="return false;">Close</button>
                </form>
            </div>
        </div>
        <section id="headerbar">
            <?php $message = "";
            $hour = date("G");
            switch ($hour) {
                case $hour <= 11: // 12am - 11am
                    $message = "Good Morning, ";
                    break;
                case $hour <= 17: // 12pm - 5pm
                    $message = "Good Afternoon, ";
                    break;
                case $hour <= 23: // 6pm - 11pm
                    $message = "Good Evening, ";
                    break;
            }
            ?>
            <p><?php echo $message . "<span style='font-weight:bold; margin-right:7px;'>"
                    . $u_email . "</span>" ?> <a href="logout.php" title="Logout"
                                                 class="button">Logout</a></p>
            <span class="clear"></span>
        </section>
        <section id="sidebar">
            <div class="sidebar_list">
                <h3>Past Volunteer Events</h3>
                <?= $app->displayPastEventByYear(); ?>
            </div>
            <div class="sidebar_list">
                <h3>Current Volunteer Events</h3>
                <?= $app->displayCurrentYearsEvent(); ?>
                <button class='btn' id="add_event">+ Add</button>
            </div>
        </section>
        <section id="mainbody">
            <div id="left">
                <div id="volCalendar">
                    <h3>Volunteer Action</h3>
                    <?= $app->displayVolunteerCalendar(date("m"), date("Y")); ?>
                </div>
                <div id="specificDate">
                </div>
                <span class="clear"></span>
            </div>
            <div id="right">
                <div id="volunteerDates">
                </div>
            </div>
        </section>
        <span class="clear"></span>
    </div>
<?php include("footer.php"); ?>