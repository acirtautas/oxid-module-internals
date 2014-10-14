[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

<form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="cl" value="ac_module_internals_metadata">
    <input type="hidden" name="editlanguage" value="[{ $editlanguage }]">
</form>

[{$sRawMetadata}]

[{include file="bottomitem.tpl"}]