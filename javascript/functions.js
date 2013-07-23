$(document).ready(function() {

    // Diplays the tutorial if they are new users
    if(!$.cookie("seenTutorial")) {
        var defaultExp = new Date();
        defaultExp.setMonth(defaultExp.getMonth() + 1);

        $("#overlay").fadeIn(1050, function() {
            $("#tooltip").fadeIn("slow", function() {
                $("html body").animate({scrollTop: $("#tooltip").scrollTop() + 50});
                $.cookie({name: "seenTutorial", value: true, 
                    expires: defaultExp.toGMTString()});
            });
        });
    }

    $("#tooltip button").on("click", function() {
        var step = $(this).attr("step");
        var tooltip = $(this).parent("#tooltip");

        switch (parseInt(step)) {
            case 0:
                displayTooltip(tooltip, "#c_1", "", "", 1);
                break;
            case 1:
                displayTooltip(tooltip, "#c_2", "", "", 2);
                break;
            case 2:
                displayTooltip(tooltip, "#c_3", "", "", -1);
                break;
            default:
                $(tooltip).fadeOut();
                $("#overlay").fadeOut();
                break;
        }
    });

    /**
     * Function description
     *
     * @param (Type) Paramater description
     * @return (Type) Return description
     */
    function displayTooltip(tooltip, id, title, message, step) {
        var tooltipXY = $(id).position();

        $(tooltip).fadeOut(500).animate({
            top: tooltipXY.top + 28,
            left: tooltipXY.left
        }, 300, function () {
            $(this).find("h3").text(title);
            $(this).find("p").text(message);
            $(this).find("button").attr("step", step);
            if (step == -1) {
                $(this).find("button").text("close");
            }
            $("html body").animate({
                "scrollTop": $(id).scrollTop() + 50
            });
        }).fadeIn(500);
    }

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

    $("#close_form").on("click", function() {
        $("#newPartyForm").fadeOut();
        $("#overlay").fadeOut();
    });

    $("#add_event").on("click", function() {
        $("#newPartyForm").fadeIn();
        $("#overlay").fadeIn();
    });

    $(".sidebar_list li").on("click", function() {
        $(".sidebar_list li").removeClass("clicked");
        $(this).addClass("clicked");

        var date = $(this).attr("data-date");
        $.ajax({
            url: document.URL + "?specificDate=" + date,
            type: "GET",
            success: function(result) {
                // Calendar
                $("#volCalendar").replaceWith(function() {
                    $("#specificDate").remove();
                    return $(result).fadeOut(1000).fadeIn(500);
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
        $("#pagination button").on("click", function () {
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
});