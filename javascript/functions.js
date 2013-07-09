/**
 * A Class with common javascript functions
 */
$(document).ready(function () {
    // Toggle Highlight
    $("#box").change(function () {
        $(".optional").toggle();
    });

    // Toggles selected row and adds it to the appropriate action div
    $(".selectable tr:not(:has(th)):not(:nth-child(2)):not('.disabled')").click(function () {

        var dataBox = $(this).attr("data-box");
        var data = $(this).attr("data-dataElem");

        // Add/Remove element
        if ($(this).hasClass("highlight")) {
            var item = $("#" + dataBox + " li:contains(" + data + ")");
            item.attr("class", "popin").wait(function () {
                $(item).remove();
                $("#" + dataBox).trigger("modified");
            }, 250);
        } else {
            $("#" + dataBox).append("<li class='popout'>" + data + "</li>").trigger("modified");
        }
        $(this).toggleClass('highlight');
    });

    // Publish-Subcribe pattern
    $(".actionContainer").on("modified", function () {
        if ($(this).children(".itemsToModify").children("li").length < 1) {
            $(this).fadeOut();
        } else {
            $(this).fadeIn();
        }
    });

    $(".actionButton").click(function () {
        var parent = $(this).parents(".actionContainer");
        var itemsToModify = $(parent).children(".itemsToModify").children("li");
        console.log(itemsToModify);

        // Make ajax request
        // $.ajax({
        //     url: "volunteerModify.php?user=" + $(this).attr("data-user"),
        //     context: $("#holder"),
        //     data: "",
        //     success: function(result,textStatus) {
        //         // $('#row'+row).fadeOut('fast');
        //         console.log("the data" + data + textStatus);
        //         // location.reload();
        //     }
        // });
    });
    // Wait Function
    $.fn.wait = function (callback, time) {
        window.setTimeout(callback, time);
        return $(this);
    }
});