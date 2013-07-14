$(document).ready(function () {

    $("#box").change(function () {
        $(".optional").toggle();
    });

    $("#close_form").on("click", function() {
        $("#newPartyForm").fadeOut();
        $("#overlay").fadeOut();
    });

    $("#add_event").on("click", function () {
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
            success: function(result, textStatus) {
                $("#volCalendar").replaceWith($(result).filter("#volCalendar"));
                
                $("#specificDate").replaceWith($(result).filter("#specificDate"));
                // Toggles selected row and adds it to the appropriate action div
                $("#specificDate .selectable").on("click", "tr:not('.def_cursor'):not('.disabled'):not('.granted')", function() {
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
                        $("#" + dataBox).append("<li data-dayvol='" +dayVol+ "' class='popout'>" + data + "</li>").trigger("modified");
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
                $(".actionButton").on("click", function () {
                    var parent = $(this).parents(".actionContainer");
                    var itemsToModify = $(parent).children(".itemsToModify").children("li");

                    var actionItems = "";
                    var action = $(this).attr("data-action");
                    var reqType = $(this).attr("data-reqType");
                    var dayVol;

                    $(itemsToModify).each(function(i, val) {
                        dayVol = $(this).attr("data-dayvol");
                        var elem = $.trim($(val).text());
                        actionItems += (i == itemsToModify.length - 1) ? elem : elem + "|";
                    });

                    // Make ajax request
                    var sendUrl = "volunteerREST.php?" + encodeURIComponent(action + "="
                        + actionItems + "&volunteerDate=" + dayVol);
                    $.ajax({
                        url: sendUrl,
                        context: $(parent),
                        type: reqType,
                        success: function(result, textStatus) {
                            $('#vol_spec_date').html(result);
                            $(this.children(".itemsToModify").children("li")).each(
                                function() {
                                    $(this).remove();
                                }
                            );
                            $(parent).trigger("modified");
                        }
                    });
                });
            }
        });
    });

    // Wait Function
    $.fn.wait = function (callback, time) {
        window.setTimeout(callback, time);
        return $(this);
    }
});