<html>

<body>
<style>
    span.state.sok {color:green;}
    span.state.swarning {color:orange;}
    span.state.serror {color:red;}
    span.state.sfatalm {color:red;text-decoration:line-through;}
    span.state.sfatals {color:red;text-decoration:underline;}
    button.fix {position: absolute;top:0; right: 0;}
    .actions i {margin-right:20px;display:inline_blocks;}
    h3 {
        font-size: 14px;
        font-weight: bold;
        margin: 7px 0 10px 0;
        padding-top: 7px;
        border-top: 1px solid #ddd;
    }
    .actions{border-top: 1px solid #ddd;}
</style>
[{foreach from=$aModules key=ModulId item=ModId}]

    <h2>[{oxmultilang ident="AC_MI_MODULE"}]: [{$ModId.title}]</h2>
    [{if $ModId.aVersions|@count > 0}]
        <div style="position: relative;">
            <h3>[{oxmultilang ident="AC_MI_VERSION"}]:</h3>
            [{assign var="_ok" value=1}]
            [{foreach from=$ModId.aVersions key=sVersion item=iState}]
                <span class="state [{$sState.$iState}]">[{$sVersion}]</span>
                [{if $iState < 1 && $iState != -2 }][{assign var="_ok" value=0}][{/if}]
            [{/foreach}]
            [{*if !$_ok}]
                <button class="fix" onclick="module_internals_fix('fix_version')">[{oxmultilang ident="AC_MI_FIXBTN"}]</button>
            [{/if*}]
            <br>
        </div>
    [{/if}]

    [{if $aControllers|@count > 0}]
        <div style="position: relative;">
            <h3>[{oxmultilang ident="AC_MI_CONTROLLER"}]:</h3>
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
            [{*if !$_ok}]
                <button class="fix" onclick="module_internals_fix('fix_extend')">[{oxmultilang ident="AC_MI_FIXBTN"}]</button>
            [{/if*}]
            <br>
        </div>
    [{/if}]


    [{if $ModId.aExtended|@count > 0}]
        <div style="position: relative;">
            <h3>[{oxmultilang ident="AC_MI_EXTEND"}]:</h3>
            <table>
                [{assign var="_ok" value=1}]
                [{foreach from=$ModId.aExtended key=sClass item=aModules}]
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
            [{*if !$_ok}]
                <button class="fix" onclick="module_internals_fix('fix_extend')">[{oxmultilang ident="AC_MI_FIXBTN"}]</button>
            [{/if*}]
            <br>
        </div>
    [{/if}]

    [{if $ModId.aFiles|@count > 0}]
        <div style="position: relative;">
            <h3>[{oxmultilang ident="AC_MI_FILES"}]:</h3>
            <table>
                [{assign var="_ok" value=1}]
                [{foreach from=$ModId.aFiles key=sClass item=aFiles}]
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
            [{*if !$_ok}]
                <button class="fix" onclick="module_internals_fix('fix_files')">[{oxmultilang ident="AC_MI_FIXBTN"}]</button>
            [{/if*}]
            <br>
        </div>
    [{/if}]

    [{if $ModId.aBlocks|@count > 0}]
        <div style="position: relative;">
            <h3>[{oxmultilang ident="AC_MI_BLOCKS"}]:</h3>
            <table>
                [{assign var="_ok" value=1}]
                [{foreach from=$ModId.aBlocks key=sTemplate item=aFiles}]
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
            [{*if !$_ok}]
                <button class="fix" onclick="module_internals_fix('fix_blocks')">[{oxmultilang ident="AC_MI_FIXBTN"}]</button>
            [{/if*}]
            <br>
        </div>
    [{/if}]

    [{if $ModId.aTemplates|@count > 0}]
        <div style="position: relative;">
            <h3>[{oxmultilang ident="AC_MI_TEMPLATES"}]:</h3>
            <table>
                [{assign var="_ok" value=1}]
                [{foreach from=$ModId.aTemplates key=sTemplate item=aFiles}]
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
            [{*if !$_ok}]
                <button class="fix" onclick="module_internals_fix('fix_templates')">[{oxmultilang ident="AC_MI_FIXBTN"}]</button>
            [{/if*}]
            <br>
        </div>
    [{/if}]

    [{if $ModId.aSettings|@count > 0}]
        <div style="position: relative;">
            <h3>[{oxmultilang ident="AC_MI_SETTINGS"}]:</h3>
            <table>
                [{assign var="_ok" value=1}]
                [{foreach from=$ModId.aSettings key=sName item=iState}]
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
            [{*if !$_ok}]
                <button class="fix" onclick="module_internals_fix('fix_settings')">[{oxmultilang ident="AC_MI_FIXBTN"}]</button>
            [{/if*}]
            <br>
        </div>
    [{/if}]

    [{if $ModId.aEvents|@count > 0}]
        <div style="position: relative;">
            <h3>[{oxmultilang ident="AC_MI_EVENTS"}]:</h3>
            <table>
                [{assign var="_ok" value=1}]
                [{foreach from=$ModId.aEvents key=sEvent item=aCallbacks}]
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
            [{*if !$_ok}]
                <button class="fix" onclick="module_internals_fix('fix_events')">[{oxmultilang ident="AC_MI_FIXBTN"}]</button>
            [{/if*}]
            <br>
        </div>
    [{/if}]

    [{if $ModId.aEvents|@count == 0
    && $ModId.aSettings|@count == 0
    && $ModId.aControllers|@count == 0
    && $ModId.aTemplates|@count == 0
    && $ModId.aBlocks|@count == 0
    && $ModId.aFiles|@count == 0
    && $ModId.aExtended|@count == 0
    && $ModId.aVersions|@count == 0
    }]
        -
    [{/if}]
[{/foreach}]

<div class="actions">
    <b>[{oxmultilang ident="AC_LEGEND"}] : </b>
    <span class="state sok">[{oxmultilang ident="AC_STATE_OK"}]</span> <i>[{oxmultilang ident="AC_STATE_OK_LABEL"}]</i>
    <span class="state swarning">[{oxmultilang ident="AC_STATE_WA"}]</span> <i>[{oxmultilang ident="AC_STATE_WA_LABEL"}]</i>
    <span class="state serror">[{oxmultilang ident="AC_STATE_ER"}]</span> <i>[{oxmultilang ident="AC_STATE_ER_LABEL"}]</i>
    <span class="state sfatalm">[{oxmultilang ident="AC_STATE_FM"}]</span> <i>[{oxmultilang ident="AC_STATE_FM_LABEL"}]</i>
    <span class="state sfatals">[{oxmultilang ident="AC_STATE_FS"}]</span> <i>[{oxmultilang ident="AC_STATE_FS_LABEL"}]</i>
</div>

</body>
</html>