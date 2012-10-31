[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

<form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="cl" value="ac_module_internals_utils">
    <input type="hidden" name="fnc" value="" id="fnc">
    <input type="hidden" name="editlanguage" value="[{ $editlanguage }]">
</form>

<script>
    function module_internals_fix(fnc) {
        document.getElementById('fnc').value = fnc;
        document.getElementById('transfer').submit();
    }
</script>

<div>
    <h3>[{oxmultilang ident="AC_MI_ACTIVATION"}]</h3>
    [{if $blIsActive }]
    <button [{if $oxid == 'ac_module_internals'}]disabled[{/if}] onclick="module_internals_fix('deactivate_module')">[{oxmultilang ident="AC_MI_DEACTIVATEBTN"}]</button>
    [{else}]
    <button [{if $oxid == 'ac_module_internals'}]disabled[{/if}] onclick="module_internals_fix('activate_module')">[{oxmultilang ident="AC_MI_ACTIVATEBTN"}]</button>
    [{/if}]
</div>

<div>
    <h3>[{oxmultilang ident="AC_MI_CACHE"}]</h3>
    <button onclick="module_internals_fix('reset_cache')">[{oxmultilang ident="AC_MI_RESETBTN"}]</button>
</div>

[{include file="bottomitem.tpl"}]