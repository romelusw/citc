<?php
$pageTitle = "Demo"; include("header.php");
?>

<form id="fid">
    <fieldset>
        <label for="f1">Who</label>
        <input id="f1" type="text" placeholder="input text"/>
    </fieldset>

    <fieldset>
        <label for="f2">Number</label>
        <input id="f2" type="tel" placeholder="input telephone"/>
    </fieldset>

    <fieldset>
        <label for="f3">On?</label>
        <input id="f3" type="checkbox"/>

        <label for="f4">When</label>
        <input id="f4" type="date"/>
    </fieldset>
</form>
<form>
    <fieldset></fieldset>
</form>
<form>
    <fieldset></fieldset>
    <fieldset>
        <label for="f5">Number</label>
        <input id="f5" type="text" placeholder="input text"/>
    </fieldset>
</form>

<script type="text/javascript">
    $("form").formWizard({cycleSteps:true});
</script>