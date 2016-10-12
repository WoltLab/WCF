{foreach from=$selectOptions key=key item=selectOption}
	<label><input type="radio" name="values[{$option->optionName}]" value="{$key}" {if $value == $key} checked{/if} {if $disableOptions[$key]|isset || $enableOptions[$key]|isset}class="jsEnablesOptions" data-disable-options="[ {@$disableOptions[$key]}]" data-enable-options="[ {@$enableOptions[$key]}]"{/if}> {lang}{@$selectOption}{/lang}</label>
{/foreach}
