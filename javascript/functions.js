$(document).ready(function() {

    // Diplays the tutorial if they are new users
    if(!$.cookie("seenTutorial")) {
        var defaultExp = new Date();
        defaultExp.setMonth(defaultExp.getMonth() + 1);

        $("#overlay").fadeIn("slow", function() {
            displayTooltip($("button[data-step = 0]").parent(".tooltip"));
            $.cookie({
                name: "seenTutorial",
                value: true, 
                expires: defaultExp.toGMTString()
            });
        });
    }

    /**
     * Function description
     *
     * @param (Type) Paramater description
     * @return (Type) Return description
     */
    function displayTooltip(tooltip) {
        var padding = 30;
        var tipLocation = $(tooltip.children("button")
            .attr("data-tipcontext")).position();

        if(tipLocation) {
            // Animate the tooltip onto the page
            tooltip.animate({
                top: tipLocation.top + padding,
                left: tipLocation.left
            }, "slow", function() {
                // Position the page to the tooltip y-coordinate
                $("html body").animate({
                    scrollTop: (tipLocation.top)
                });
            }).fadeIn();
        }
    }

    // Handle Tooltip interaction
    $(".tooltip button").on("click", function() {
        var tooltip = $(this).parent(".tooltip");
        var step = parseInt($(this).attr("data-step"));

        if(step == -1) {
            $(tooltip).fadeOut();
            $("#overlay").fadeOut();
        } else {
            var nextStep = $(tooltip).children("button").attr("data-next");
            tooltip.fadeOut();
            var nextTooltip = $("button[data-step = " + nextStep + "]").parent(".tooltip");
            displayTooltip(nextTooltip);
        }
    });

    /**
     * Function description
     *
     * @param (Type) Paramater description
     * @return (Type) Return description
     */
    $.fn.wait = function(callback, time) {
        window.setTimeout(callback, time);
        return this;
    }

    /**
     * Function description
     *
     * @param (Type) Paramater description
     * @return (Type) Return description
     */
     function debug(obj) {
        console.log("Debug: " + obj);
     }

    $("#box").change(function() {
        $(".optional").toggle();
    });

    $(".close_form").on("click", function() {
        $("#newPositionForm").fadeOut();
        $("#newPartyForm").fadeOut();
        $("#overlay").fadeOut();
    });

    $("#add_event").on("click", function() {
        $("#newPartyForm").fadeIn();
        $("#overlay").fadeIn();
    });

    $("#add_position").on("click", function(e) {
        e.preventDefault();
        var fieldset = $("#vol_pos").clone()
        .append("<a href='#' class='removeset'>remove</a></fieldset>")
        .removeAttr("id");
        $(this).before(fieldset);
    });

    $(document).on("click", "a.removeset", function() {
        $(this).parent("fieldset").remove();
    });

    $(".sidebar_list").on("click", "li:not('.clicked')", function() {
        $(".sidebar_list li").removeClass("clicked");
        $(this).addClass("clicked");

        var date = $(this).attr("data-date");
        $.ajax({
            url: "volunteerREST.php" + "?specificDate=" + date,
            type: "GET",
            success: function(result, textStatus, xhr) {
                // Calendar
                $("#volCalendar").replaceWith(function() {
                    return $(result).filter("#volCalendar").fadeOut(1000).fadeIn(500);
                });

                $("#specificDate").replaceWith(function() {
                    return $(result).filter("#specificDate").fadeOut(1000).fadeIn(500);
                });

                $("#volunteerDates").replaceWith(function() {
                    return $(result).filter("#volunteerDates").fadeOut(1000).fadeIn(500);
                });

                // Post Request event Bindings
                handlePagination();
                handleDateClick();
                handleActionClick();
            }
        });
    });

    /**
     * Function description
     *
     * @param (Type) Paramater description
     * @return (Type) Return description
     */
    function handleActionClick() {
        $(".actionButton").on("click", function () {
            var parent = $(this).parents(".actionContainer");
            var itemsToModify = $(parent).children(".itemsToModify").children("li");

            var actionItems = "";
            var action = $(this).attr("data-action");
            var reqType = $(this).attr("data-reqType");
            var dayVol;

            $(itemsToModify).each(function (i, val) {
                dayVol = $(this).attr("data-dayvol");
                var elem = $.trim($(val).text());
                actionItems += (i == itemsToModify.length - 1) ? elem : elem + "|";
            });

            // Make ajax request
            var page = parseInt($("button.active").text()) - 1;
            var sendUrl = "volunteerREST.php?" + encodeURIComponent(action 
                + "=" + actionItems + "&volunteerDate=" + dayVol + "&page=" + page);
            $.ajax({
                url: sendUrl,
                context: $(parent),
                type: reqType,
                success: function (result, textStatus) {
                    $("#vol_spec_date").replaceWith(function() {
                        return $(result).filter("#vol_spec_date").fadeOut(1000).fadeIn(500);
                    });

                    $(this).children(".itemsToModify").children("li").each(
                        function () {
                        $(this).remove();
                    });
                    $(parent).trigger("modified");
                    // Post Request event Bindings
                    handlePagination();
                    handleDateClick();
                }
            });
        });
    }

    /**
     * Function description
     *
     * @param (Type) Paramater description
     * @return (Type) Return description
     */
    function handleDateClick() {
        var elems = "tr:not('.def_cursor'):not('.disabled'):not('.granted')";
        $("#specificDate .selectable").on("click", elems, function () {
            var dataBox = $(this).attr("data-box");
            var data = $(this).attr("data-dataElem");
            var dayVol = $(this).attr("data-datevol");

            // Add/Remove element
            if ($(this).hasClass("highlight")) {
                var item = $("#" + dataBox + " li:contains(" + data + ")");
                item.attr("class", "popin").wait(function () {
                    $(item).remove();
                    $("#" + dataBox).trigger("modified");
                }, 250);
            } else {
                $("#" + dataBox).append("<li data-dayvol='" + dayVol 
                    + "' class='popout'>" + data + "</li>").trigger("modified");
            }
            $(this).toggleClass('highlight');
        });
        // Publish-Subcribe pattern
        $("#specificDate .actionContainer").on("modified", function () {
            if ($(this).children(".itemsToModify").children("li").length < 1) {
                $(this).fadeOut();
            } else {
                $(this).fadeIn();
            }
        });
    }

    /**
     * Function description
     *
     * @param (Type) Paramater description
     * @return (Type) Return description
     */
    function handlePagination() {
        $("#pagination").on("click", "button:not('.active')", function () {
            $.ajax({
                url: $(this).attr("data-link"),
                type: "GET",
                success: function (result) {
                    $("#volCalendar").replaceWith(function() {
                        $("#specificDate").remove();
                        return $(result).fadeOut(1000).fadeIn(500);
                    });
                    handlePagination();
                    handleDateClick();
                    handleActionClick();
                }
            });
        });
    }

    $("#volunteerDay").change(function() {
        var option = $("#volunteerDay option").filter(":selected");
        if(option.text() != "--") {
            $.ajax({
                url: "volunteerREST.php?positionDate=" + option.val(),
                type: "GET",
                success: function(result) {
                    $("#volunteerPosition ul").replaceWith(result);
                    $("#volunteerPosition").fadeIn(500);
                }
            });
        } else {
            $("#volunteerPosition").fadeOut();
        }
    });
});