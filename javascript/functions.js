$(document).ready(function () {
    // $("#overlay").fadeIn(1050, function() {
    //     $("#tooltip").fadeIn("slow", function(){
    //         $("html body").animate({"scrollTop": $("#tooltip").scrollTop() + 50});
    //     });
    // });

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

    $("#tooltip button").on("click", function() {
        var step = $(this).attr("step");
        var parent = $(this).parent("#tooltip");
        var position;

        $(parent).fadeOut(500);
        switch(parseInt(step)) {
           case 0:
                position = $("#c_1").position();
                $(parent).animate({ top: position.top + 28, left: position.left}, 300, function (){
                     $(this).find("h3").text($("#c_1").text());
                     $(this).find("p").text("Hello World");
                     $(this).find("button").attr("step", 1);
                     $("html body").animate({"scrollTop": $("#c_1").scrollTop() + 50});
                }).fadeIn();
               
           break;

           case 1:
               position = $("#c_2").position();
                $(parent).animate({ top: position.top + 28, left: position.left}, 300, function (){
                    $(this).find("h3").text($("#c_2").text());
                    $(this).find("p").text("Hello World x2");
                    $(this).find("button").attr("step", 2);
                    $("html body").animate({"scrollTop": $("#c_2").scrollTop() + 50});
               }).fadeIn();
               
           break;

           case 2:
               position = $("#c_3").position();
                $(parent).animate({ top: position.top + 28, left: position.left}, 300, function (){
                    $(this).find("h3").text($("#c_3").text());
                    $(this).find("p").text("Hello World x3");
                    $(this).find("button").attr("step", -1);
                    $(this).find("button").text("Close");
                    $("html body").animate({"scrollTop": $("#c_3").scrollTop() + 50});
               }).fadeIn();
               
           break;

           default:
               $(parent).fadeOut();
               $("#overlay").fadeOut();
           break;
       }
    });

    $(".sidebar_list li").on("click", function() {
        $(".sidebar_list li").removeClass("clicked");
        $(this).addClass("clicked");

        var date = $(this).attr("data-date");
        $.ajax({
            url: document.URL + "?specificDate=" + date,
            type: "GET",
            success: function(result, textStatus) {
                $("#volCalendar").fadeOut(420).fadeIn(420, function() {
                    $(this).replaceWith($(result).filter("#volCalendar"));
                });

                $("#specificDate").fadeOut(420).fadeIn(420, function() {
                    $(this).replaceWith($(result).filter("#specificDate"));
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
                    $("#pagination button").on("click", function() {
                        $.ajax({
                            url: $(this).attr("data-link"),
                            type: "GET",
                            success: function(result, textStatus) {
                                $("#volCalendar").fadeOut(420).fadeIn(420, function() {
                                    $(this).replaceWith($(result).filter("#volCalendar"));
                                });
                                $("#specificDate").fadeOut(420).fadeIn(420, function() {
                                    $(this).replaceWith($(result).filter("#specificDate"));
                                });
                                $("#pagination").fadeOut(420).fadeIn(420, function() {
                                    $(this).replaceWith($(result).filter("#pagination"));
                                });
                            }
                        });
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
                                $('#vol_spec_date').hide().html(result).fadeIn();
                                $(this.children(".itemsToModify").children("li")).each(
                                    function() {
                                        $(this).remove();
                                    }
                                );
                                $(parent).trigger("modified");
                            }
                        });
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