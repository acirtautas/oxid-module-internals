[{include file="headitem.tpl" box="box" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

<form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="cl" value="module_internals_state">
    <input type="hidden" name="fnc" value="" id="fnc">
    <input type="hidden" name="editlanguage" value="[{ $editlanguage }]">
</form>

<style>
    span.state.sok {color:green;}
    span.state.swarning {color:orange;}
    span.state.serror {color:red;}
    span.state.sfatalm {color:red;text-decoration:line-through;}
    span.state.sfatals {color:red;text-decoration:underline;}
    button.fix {position: absolute;top:0; right: 0;}
    .actions i {margin-right:20px;display:inline_blocks;}
</style>

<script>
    function module_internals_fix(fnc) {
        document.getElementById('fnc').value = fnc;
        document.getElementById('transfer').submit();
    }
</script>

[{if $aVersions|@count > 0}]
    <div style="position: relative;">
        <h3>[{oxmultilang ident="AC_MI_VERSION"}]</h3>
        [{assign var="_ok" value=1}]
        [{foreach from=$aVersions key=sVersion item=iState}]
        <span class="state [{$sState.$iState}]">[{$sVersion}]</span>
        [{if $iState < 1 && $iState != -2 }][{assign var="_ok" value=0}][{/if}]
        [{/foreach}]
        [{if !$_ok}]
        <button class="fix" onclick="module_internals_fix('fix_version')">[{oxmultilang ident="AC_MI_FIXBTN"}]</button>
        [{/if}]
        <br>
    </div>
[{/if}]

[{if $aControllers|@count > 0}]
    <div style="position: relative;">
        <h3>[{oxmultilang ident="AC_MI_CONTROLLER"}]</h3>
        <table>
            [{assign var="_ok" value=1}]
            [{foreach from=$aControllers key=sClass item=aModules}]
            <tr>
                <td style="vertical-align: top;"><b>[{$sClass}]</b></td>
                <td>
                    [{foreach from=$aModules key=sModule item=iState}]
                    <span class="state [{$sState.$iState}]">[{$sModule}]</span>
                    [{if $iState < 1 && $iState != -2 }][{assign var="_ok" value=0}][{/if}]
                    [{/foreach}]
                </td>
            </tr>
            [{/foreach}]
        </table>
        [{if !$_ok}]
        <button class="fix" onclick="module_internals_fix('fix_extend')">[{oxmultilang ident="AC_MI_FIXBTN"}]</button>
        [{/if}]
        <br>
    </div>
    [{/if}]

[{if $aExtended|@count > 0}]
<div style="position: relative;">
    <h3>[{oxmultilang ident="AC_MI_EXTEND"}]</h3>
    <table>
        [{assign var="_ok" value=1}]
        [{foreach from=$aExtended key=sClass item=aModules}]
        <tr>
            <td style="vertical-align: top;"><b>[{$sClass}]</b></td>
            <td>
                [{foreach from=$aModules key=sModule item=iState}]
                    <span class="state [{$sState.$iState}]">[{$sModule}]</span>
                    [{if $iState < 1 && $iState != -2 }][{assign var="_ok" value=0}][{/if}]
                [{/foreach}]
            </td>
        </tr>
        [{/foreach}]
    </table>
    [{if !$_ok}]
        <button class="fix" onclick="module_internals_fix('fix_extend')">[{oxmultilang ident="AC_MI_FIXBTN"}]</button>
    [{/if}]
    <br>
</div>
[{/if}]

[{if $aFiles|@count > 0}]
<div style="position: relative;">
    <h3>[{oxmultilang ident="AC_MI_FILES"}]</h3>
    <table>
        [{assign var="_ok" value=1}]
        [{foreach from=$aFiles key=sClass item=aFiles}]
        <tr>
            <td style="vertical-align: top;"><b>[{$sClass}]</b></td>
            <td>
                [{foreach from=$aFiles key=sFile item=iState}]
                    <span class="state [{$sState.$iState}]">[{$sFile}]</span>
                    [{if $iState < 1 && $iState != -2 }][{assign var="_ok" value=0}][{/if}]
                [{/foreach}]
            </td>
        </tr>
        [{/foreach}]
    </table>
    [{if !$_ok}]
        <button class="fix" onclick="module_internals_fix('fix_files')">[{oxmultilang ident="AC_MI_FIXBTN"}]</button>
    [{/if}]
    <br>
</div>
[{/if}]

[{if $aBlocks|@count > 0}]
<div style="position: relative;">
    <h3>[{oxmultilang ident="AC_MI_BLOCKS"}]</h3>
    <table>
        [{assign var="_ok" value=1}]
        [{foreach from=$aBlocks key=sTemplate item=aFiles}]
        <tr>
            [{assign var="_tstate" value=1}]
            [{foreach from=$aFiles key=sFile item=aStates}]
                [{if $aStates.template < $_tstate}]
                    [{assign var="_tstate" value=$aStates.template}]
                [{/if}]
            [{/foreach}]
            <td style="vertical-align: top;"><b><span class="state [{$sState.$_tstate}]">[{$sTemplate}]</span></b></td>
            <td>
                [{foreach from=$aFiles key=sFile item=aStates}]
                [{assign var="_state" value=$aStates.file}]
                <div>
                    <span class="state [{$sState.$_state}]">[{$sFile}]</span>
                    [{if $_state < 1 && $_state != -2 }][{assign var="_ok" value=0}][{/if}]
                </div>
                [{/foreach}]
            </td>
        </tr>
        [{/foreach}]
    </table>
    [{if !$_ok}]
        <button class="fix" onclick="module_internals_fix('fix_blocks')">[{oxmultilang ident="AC_MI_FIXBTN"}]</button>
    [{/if}]
    <br>
</div>
[{/if}]

[{if $aTemplates|@count > 0}]
<div style="position: relative;">
    <h3>[{oxmultilang ident="AC_MI_TEMPLATES"}]</h3>
    <table>
        [{assign var="_ok" value=1}]
        [{foreach from=$aTemplates key=sTemplate item=aFiles}]
        <tr>
            <td style="vertical-align: top;"><b>[{$sTemplate}]</b></td>
            <td>
                [{foreach from=$aFiles key=sFile item=iState}]
                    <span class="state [{$sState.$iState}]">[{$sFile}]</span>
                    [{if $iState < 1 && $iState != -2 }][{assign var="_ok" value=0}][{/if}]
                [{/foreach}]
            </td>
        </tr>
        [{/foreach}]
    </table>
    [{if !$_ok}]
        <button class="fix" onclick="module_internals_fix('fix_templates')">[{oxmultilang ident="AC_MI_FIXBTN"}]</button>
    [{/if}]
    <br>
</div>
[{/if}]

[{if $aSettings|@count > 0}]
<div style="position: relative;">
    <h3>[{oxmultilang ident="AC_MI_SETTINGS"}]</h3>
    <table>
        [{assign var="_ok" value=1}]
        [{foreach from=$aSettings key=sName item=iState}]
        <tr>
            <td style="vertical-align: top;"><b>[{$sName}]</b></td>
            <td>
                <div>
                    <span class="state [{$sState.$iState}]">[{$sName}]</span>
                    [{if $iState < 1 && $iState != -2 }][{assign var="_ok" value=0}][{/if}]
                </div>
            </td>
        </tr>
        [{/foreach}]
    </table>
    [{if !$_ok}]
        <button class="fix" onclick="module_internals_fix('fix_settings')">[{oxmultilang ident="AC_MI_FIXBTN"}]</button>
    [{/if}]
    <br>
</div>
[{/if}]

[{if $aEvents|@count > 0}]
<div style="position: relative;">
    <h3>[{oxmultilang ident="AC_MI_EVENTS"}]</h3>
    <table>
        [{assign var="_ok" value=1}]
        [{foreach from=$aEvents key=sEvent item=aCallbacks}]
        <tr>
            <td style="vertical-align: top;"><b>[{$sEvent}]</b></td>
            <td>
                [{foreach from=$aCallbacks key=sCallback item=iState}]
                    <span class="state [{$sState.$iState}]">[{$sCallback}]</span>
                    [{if $iState < 1 && $iState != -2 }][{assign var="_ok" value=0}][{/if}]
                [{/foreach}]
            </td>
        </tr>
        [{/foreach}]
    </table>
    [{if !$_ok}]
        <button class="fix" onclick="module_internals_fix('fix_events')">[{oxmultilang ident="AC_MI_FIXBTN"}]</button>
    [{/if}]
    <br>
</div>
[{/if}]

[{include file="bottomitem.tpl"}]

</div>
<div class="actions">
    <b>[{oxmultilang ident="AC_LEGEND"}] : </b>
    <span class="state sok">[{oxmultilang ident="AC_STATE_OK"}]</span> <i>[{oxmultilang ident="AC_STATE_OK_LABEL"}]</i>
    <span class="state swarning">[{oxmultilang ident="AC_STATE_WA"}]</span> <i>[{oxmultilang ident="AC_STATE_WA_LABEL"}]</i>
    <span class="state serror">[{oxmultilang ident="AC_STATE_ER"}]</span> <i>[{oxmultilang ident="AC_STATE_ER_LABEL"}]</i>
    <span class="state sfatalm">[{oxmultilang ident="AC_STATE_FM"}]</span> <i>[{oxmultilang ident="AC_STATE_FM_LABEL"}]</i>
    <span class="state sfatals">[{oxmultilang ident="AC_STATE_FS"}]</span> <i>[{oxmultilang ident="AC_STATE_FS_LABEL"}]</i>
</div>