$(document).ready(function(){function d(a){var b=$(a.children("button").attr("data-tipcontext")).position();b&&a.animate({top:b.top+30,left:b.left},"slow",function(){$("html body").animate({scrollTop:b.top})}).fadeIn()}function g(a,b){a.forEach(function(a){$(a).replaceWith(function(){return $(b).filter(a).fadeOut(1E3).fadeIn(500)})})}function m(){$(".actionButton").on("click",function(){var a=$(this).parents(".actionContainer"),b=$(a).children(".itemsToModify").children("li"),c="",h="",f=$(this).attr("data-action"),
    p=$(this).attr("data-reqType"),e=$(this).attr("data-volday");$(b).each(function(a,f){var e=$.trim($(f).text()),d=$(f).attr("data-pos");c+=a==b.length-1?e:e+"|";h+=a==b.length-1?d:d+"|"});var d=parseInt($("button.active").text()),f="volunteerREST.php?"+encodeURIComponent(f+"="+c+"&volunteerDate="+e+"&positions="+h+"&page="+d);$.ajax({url:f,context:$(a),type:p,success:function(b){g(["#vol_spec_date"],b);$(this).children(".itemsToModify").children("li").remove();$(a).trigger("modified");k();l()}})})}
    function l(){var a=$("#specificDate");a.find(".selectable").on("click","tr:not('.def_cursor'):not('.disabled'):not('.granted')",function(){var a=$(this).attr("data-box"),c=$(this).attr("data-dataElem"),e=$(this).attr("data-datevol"),f=$(this).attr("data-pos");if($(this).hasClass("highlight")){var d=$("#"+a+" li:contains("+c+")");d.attr("class","popin").wait(function(){$(d).remove();$("#"+a).trigger("modified")},250)}else $("#"+a).append($("<li/>",{"data-dayvol":e,"data-pos":f,"class":"popin"}).text(c)).trigger("modified");
        $(this).toggleClass("highlight")});a.find(".actionContainer").on("modified",function(){1>$(this).children(".itemsToModify").children("li").length?$(this).fadeOut():$(this).fadeIn()})}function k(){$("#pagination").on("click","button:not('.active')",function(){$.ajax({url:$(this).attr("data-link"),type:"GET",success:function(a){g(["#specificDate"],a);k();l();m()}})})}function n(){$(".addCount, .subCount").on("click",function(){var a=$(this).parents(".counter"),b=a.find("input[type=text]").first(),c=
        parseInt(b.val()),a=$(a).find(".subCount");isNaN(c)?($(a).attr("disabled","disabled"),$(b).val(0)):(c+=parseInt($(this).attr("data-increment")),0==c?$(a).attr("disabled","disabled"):$(a).removeAttr("disabled"),b.val(c))})}$.fn.wait=function(a,b){isNaN(b)&&(b=100);window.setTimeout(a,b);return this};if("true"!=$.cookie("seenTutorial")){var e=new Date;e.setMonth(e.getYear()+1);$("#overlay").fadeIn("slow",function(){d($("button[data-step = 0]").parent(".tooltip"));$.cookie({name:"seenTutorial",value:!0,
        expires:e.toGMTString()})})}$(".tooltip button").on("click",function(){var a=$(this).parent(".tooltip");if(-1==parseInt($(this).attr("data-step")))$(a).fadeOut(),$("#overlay").fadeOut();else{var b=$(a).children("button").attr("data-next");a.fadeOut();a=$("button[data-step = "+b+"]").parent(".tooltip");d(a)}});$("#volunteer-day").change(function(){var a=$("#volunteer-day").find("option").filter(":selected");-1==a.text().indexOf("--")?$.ajax({url:"volunteerREST.php?positionDate="+a.val(),type:"GET",
        success:function(a){$("#positionList").replaceWith(a).fadeIn(500);$(".pos_li").on("click",function(){$(".pos_li").removeClass("positionSelected");var a=$.trim($(this).children("h4").text());$(this).addClass("positionSelected");$("#signup-pos").val(a)})}}):$("#volunteerPosition").fadeOut()});$(".sidebar_list").on("click","li:not('.clicked')",function(){$(".sidebar_list li").removeClass("clicked");$(this).addClass("clicked");var a=$(this).attr("data-date");$.ajax({url:"volunteerREST.php?specificDate="+
        a,type:"GET",success:function(a){g(["#volCalendar","#specificDate","#volunteerDates"],a);k();l();m()}})});$(".gen_field").change(function(){var a=$(this),b=a.data("clicked");null==b||0==b?(a.data("clicked",1),a.next().removeAttr("disabled")):(a.data("clicked",0),a.next().attr("disabled","disabled"))});$("#new-acct-create").is(":checked")&&$(".hidden").toggle("true");$("#vol_isGroup").is(":checked")&&$("#group-form").toggle("true");$("#vol_isYthGroup").is(":checked")&&$("#youth-group-form").toggle("true");
    $(".close_form").on("click",function(){$("#new-event-form").fadeOut();$("#overlay").fadeOut()});$("#add_event").on("click",function(){$("#new-event-form").fadeIn();$("#overlay").fadeIn()});$("#add_position").on("click",function(a){a.preventDefault();a=$("#vol_pos").clone().append("<a href='#' class='removeset'>Remove Entry</a>").removeAttr("id");a.find("input[type=text]").val("");a.find(".ui-autocomplete-input").timeAutocomplete();$(this).before(a);n()});$(document).on("blur",".vol_table .modifiable_desc",
        function(){$.ajax({url:"volunteerREST.php?modifyDesc",type:"POST",data:{volDay:$(".sidebar_list li.clicked").attr("data-date"),volPos:$(this).prev().text(),updateTxt:$(this).text()}})});$(document).on("click","a.removeset",function(){$(this).parent("fieldset").remove()});$("#overlay").click(function(){var a=this;$("#new-event-form").fadeOut(function(){$(a).fadeOut()})});$.fn.formWizard.validator=new FormValidator;$("#signup-form").formWizard({allowBack:!0,hideDisabledStep:!0});$("input[type=submit]").on("click",
        function(){var a=$(this).find("form").find(".form-field").not(":hidden"),b=new FormValidator;a.each(function(){alert(b.validateField($(this)))})});n();$("#shift-start-time").timeAutocomplete();$("#dateAltCal").datepicker({altFormat:"yy-mm-dd",dateFormat:"DD MM dd, yy",altField:"#dateCal",minDate:0})});