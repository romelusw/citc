<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
include_once("volunteerSignUp.php");
include_once("common_utils/functions.php");

// Ensure user is valid
require("verifyUser.php");
?>

<?php $pageTitle = "Volunteer Administration"; include("header.php"); ?>

    <section id="headerbar">
        <?php if ($isAdmin): ?>
            <div id="AdminBlock">

            </div>
        <?php endif; ?>

        <p style='float:left; padding-left: 15px;'>
            <span class="bold">Volunteer</span>Dashboard
        </p>
        <p style='float:right; padding-right:15px;'>
            <?php echo "Hello <span class='bold'>$u_email</span>"; ?>
            <a href='logout' title='Logout' class='insetButton'>
                <i class='icon-off'>&nbsp;</i>Logout
            </a>
        </p>
    </section>

    <div id="content">

        <div class="tooltip">
            <span class="tip_arrow"></span>
            <h2>Take a look back at past events.</h2>
            <p>This section allows you to take a look at the party dates created
                prior to the given year and see the individuals who were registered.
            </p>
            <button data-step="0" data-next="1" data-tipcontext="#tip1"
                    class="form-button right">Next <i
                    class="icon-chevron-right"></i></button>
        </div>
        <div class="tooltip">
            <span class="tip_arrow"></span>
            <h2>Create/View this years events.</h2>
            <p>This section shows you all the events created for the current year
                and allows to create new events.
            </p>
            <button data-step="1" data-next="-1" data-tipcontext="#tip2"
                    class="form-button right">Next <i
                    class="icon-chevron-right"></i></button>
        </div>
        <div class="tooltip">
            <span class="tip_arrow"></span>
            <h2>Process those who have registered.</h2>
            <p>This section allows you to accept/deny certain volunteers for a
                specific event day.
            </p>
            <button data-step="-1" data-tipcontext="#tip3"
                    class="form-button right">Close
            </button>
        </div>

        <div id="overlay"></div>

        <div id="new-event-form" class="hidden">
            <?
            // Handle Post
            if (isset($_POST["pdate"]) && isset($_POST["ptitle"])) {
                $app->createNewEvent($_POST["pdate"]);
                for ($i = 0; $i < count($_POST["ptitle"]); $i++) {
                    $app->createNewPosition($_POST["ptitle"][$i],
                        $_POST["pdescription"][$i], abs($_POST["pmaxreg"][$i]),
                        $_POST["pdate"], date("H:i:s", strtotime($_POST["pstarttime"][$i])));
                }
            }
            ?>

            <form class="card" action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
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
                            <label class="block" for="ptitle[]">Name of the Shift</label>
                            <input type="text" class="form-field" name="ptitle[]"
                                   placeholder="Shift Title" id="ptitle[]"/>

                            <label class="block" for="pdescription[]">A brief
                                description of the activities involved.
                            </label>
                            <textarea maxlength="700" name="pdescription[]"
                                      class="form-field" id="pdescription[]"
                                      placeholder="Description"></textarea>

                            <label class="block" for="shift-start-time">
                                Start Time
                            </label>
                            <input type="text" name="pstarttime[]"
                                   class="form-field"
                                   id="shift-start-time"/>

                            <label class="block" for="pmaxreg[]">
                                Number of volunteers
                            </label>
                            <div class="counter">
                                <input name="pmaxreg[]" class="form-field"
                                       id="pmaxreg[]" type="text" placeholder="0"/>

                                <div class="counter-incrementers">
                                    <button class="subCount" data-increment="-1" onclick="return false;">
                                        <i class="icon-minus"></i>
                                    </button>
                                    <button class="addCount" data-increment="1" onclick="return false;">
                                        <i class="icon-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                        <button id="add_position" style="float:right"
                                class="form-button">
                            <i class='icon-plus-sign'>&nbsp;</i>Add
                        </button>
                    </div>
                </section>

                <input type="submit" class="form-button right" value="submit"/>
                <div class="clear"></div>
            </form>
            <div class="clear"></div>
        </div>

        <div id="mainbody">
            <section id="sidebar">
                <div class="sidebar_list">
                    <h2 id="tip1">Past Volunteer Events</h2>
                    <?= $app->displayPastEventsList(); ?>
                </div>
                <div class="sidebar_list">
                    <h2 id="tip2">Current Volunteer Events</h2>
                    <?= $app->displayCurrEventsList(); ?>
                    <button id="add_event" style='width:100%' class="form-button">
                        <i class='icon-plus-sign'></i>
                        Add New Event
                    </button>
                </div>
            </section>
            <section id="center">
                <section id="left">
                    <div id="volCalendar">
                        <h2 id="tip3">Manage Volunteers</h2>
                        <?= $app->displayEventCalendar(date("m"), date("Y")); ?>
                    </div>
                </section>
                <section id="right">
                    <div id="volunteerDates"></div>
                </section>
                <div id="specificDate"></div>
                <div class="clear"></div>
            </section>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
    </div>
<?php include("footer.php"); ?>