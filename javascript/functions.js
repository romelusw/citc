/**
 * Functionality specific to CITC Web app.
 *
 * @author Woody Romelus
 */
$(document).ready(function() {
    // Be a good programmer now :p
    "use strict";

    /**
     * Delays the execution of a specified callback function.
     *
     * @param callback the function to invoke after a period of time
     * @param time the amount of seconds to wait until invoking the callback
     * @returns {*} the calling object
     */
    $.fn.wait = function (callback, time) {
        window.setTimeout(callback, time);
        return this;
    };

    // Displays the tutorial if not already seen
    if(!$.cookie("seenTutorial") || $.cookie("seenTutorial") == false) {
        var defaultExp = new Date();
        defaultExp.setMonth(defaultExp.getYear() + 1);

        $("#overlay").fadeIn("slow", function() {
            displayTooltip($("button[data-step = 0]").parent(".tooltip"));
            $.cookie({
                name: "seenTutorial",
                value: false,
                expires: defaultExp.toGMTString()
            });
        });
    }

    /**
     * Displays the tutorial tooltip.
     *
     * @param tooltip the HTML object representing the tooltip
     */
    function displayTooltip(tooltip) {
        var padding = 30;
        var tipLocation = $(tooltip.children("button").attr("data-tipcontext"))
                            .position();

        if(tipLocation) {
            // Animate the tooltip onto the page
            tooltip.animate({
                top: tipLocation.top + padding,
                left: tipLocation.left
            }, "slow", function() {
                // Re-adjust the scroll pane to account for the tooltip
                $("html body").animate({
                    scrollTop: (tipLocation.top)
                });
            }).fadeIn();
        }
    }

    /**
     * Replaces the HTML content from elements with results from AJAX requests.
     *
     * @param elements the classes or ids of the elements to replace their contents
     * @param ajaxResult the HTML returned after an AJAX request
     */
    function updateContent(elements, ajaxResult) {
        elements.forEach(function (elem) {
            $(elem).replaceWith(function() {
                return $(ajaxResult).filter(elem).fadeOut(1000).fadeIn(500);
            });
        });
    }

    // Handle Tooltip interaction
    $(".tooltip button").on("click", function() {
        var tooltip = $(this).parent(".tooltip");
        var step = parseInt($(this).attr("data-step"));

        // -1 indicates the end of the tutorial
        if(step == -1) {
            $(tooltip).fadeOut();
            $("#overlay").fadeOut();
        } else {
            var nextStep = $(tooltip).children("button").attr("data-next");
            tooltip.fadeOut();
            var nextTooltip = $("button[data-step = " + nextStep + "]")
                                .parent(".tooltip");
            displayTooltip(nextTooltip);
        }
    });

    // Handle Sign up form interactions
    $("#volunteerDay").change(function() {
        var option = $("#volunteerDay").find("option").filter(":selected");
        if(option.text() != "--") {
            $.ajax({
                url: "volunteerREST.php?positionDate=" + option.val(),
                type: "GET",
                success: function(result) {
                    var positionSection = $("#volunteerPosition");
                    positionSection.find("ul").replaceWith(result);
                    positionSection.fadeIn(500);

                    $('.pos_li').on("click", function() {
                        var val = $.trim($(this).children('h4').text());
                        $('#chosen').val(val);
                    });
                }
            });
        } else {
            $("#volunteerPosition").fadeOut();
        }
    });

    // Handle event day interactions
    $(".sidebar_list").on("click", "li:not('.clicked')", function() {
        $(".sidebar_list li").removeClass("clicked");
        $(this).addClass("clicked");

        var date = $(this).attr("data-date");
        $.ajax({
            url: "volunteerREST.php?specificDate=" + date,
            type: "GET",
            success: function(result) {
                updateContent(["#volCalendar", "#specificDate",
                               "#volunteerDates"], result);

                // Post Request event Bindings
                handlePagination();
                handleDateClick();
                handleActionClick();
            }
        });
    });

    /**
     * Binds events for the action section
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
            var sendUrl = "volunteerREST.php?" +
                encodeURIComponent(action + "=" + actionItems + "&volunteerDate="
                                          + dayVol + "&page=" + page);
            $.ajax({
                url: sendUrl,
                context: $(parent),
                type: reqType,
                success: function (result) {
                    updateContent(["#vol_spec_date"], result);
                    $(this).children(".itemsToModify").children("li").remove();
                    $(parent).trigger("modified");
                    // Post Request event Bindings
                    handlePagination();
                    handleDateClick();
                }
            });
        });
    }

    /**
     * Binds events for the specific date section
     */
    function handleDateClick() {
        var specificDateSection = $("#specificDate");
        specificDateSection.find(".selectable").on("click",
            "tr:not('.def_cursor'):not('.disabled'):not('.granted')", function() {
            var dataBox = $(this).attr("data-box");
            var data = $(this).attr("data-dataElem");
            var dayVol = $(this).attr("data-datevol");

            // Add/Remove element
            if ($(this).hasClass("highlight")) {
                var item = $("#" + dataBox + " li:contains(" + data + ")");
                item.attr("class", "popin").wait(function() {
                    $(item).remove();
                    $("#" + dataBox).trigger("modified");
                }, 250);
            } else {
                $("#" + dataBox).append("<li data-dayvol='" + dayVol 
                    + "' class='popout'>" + data + "</li>").trigger("modified");
            }
            $(this).toggleClass('highlight');
        });
        // Publish-Subscribe pattern
        specificDateSection.find(".actionContainer").on("modified", function () {
            if ($(this).children(".itemsToModify").children("li").length < 1) {
                $(this).fadeOut();
            } else {
                $(this).fadeIn();
            }
        });
    }

    /**
     * Binds events for the pagination section
     */
    function handlePagination() {
        $("#pagination").on("click", "button:not('.active')", function() {
            $.ajax({
                url: $(this).attr("data-link"),
                type: "GET",
                success: function (result) {
                    updateContent(["#specificDate"], result);
                    handlePagination();
                    handleDateClick();
                    handleActionClick();
                }
            });
        });
    }

    $(".gen_field").change(function() {
        var thiz = $(this);
        var state = thiz.data("clicked");

        if(state == null || state == 0) {
            thiz.data("clicked", 1);
            thiz.next().removeAttr("disabled");
        } else {
            thiz.data("clicked", 0);
            thiz.next().attr("disabled","disabled");
        }
    });

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
        fieldset.find('input[type=text]').val('');
        $(this).before(fieldset);
    });

    $(document).on("blur", ".vol_table .modifiable_desc", function(){
        $.ajax({
            url: "volunteerREST.php?modifyDesc",
            type: "POST",
            data: {volDay: $(".sidebar_list li.clicked").attr("data-date"),
                volPos:$(this).prev().text(), updateTxt: $(this).text()}
        });
    });

    $(document).on("click", "a.removeset", function() {
        $(this).parent("fieldset").remove();
    });
});