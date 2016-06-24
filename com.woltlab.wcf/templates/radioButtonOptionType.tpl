{foreach name=radioButtons from=$selectOptions key=key item=selectOption}
	<label><input {if $tpl.foreach.radioButtons.first}id="{$option->optionName}" {/if}type="radio" name="values[{$option->optionName}]" value="{$key}" {if $value == $key} checked{/if} {if $disableOptions[$key]|isset || $enableOptions[$key]|isset}class="jsEnablesOptions" data-disable-options="[ {@$disableOptions[$key]}]" data-enable-options="[ {@$enableOptions[$key]}]"{/if}> {lang}{@$selectOption}{/lang}</label>
{/foreach}
