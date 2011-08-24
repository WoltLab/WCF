<select name="values[{$optionData.optionName}]" id="{$optionData.optionName}">
{foreach from=$options item=option key=key}
	<option value="{$key}"{if $optionData.optionValue == $key} selected="selected"{/if}>{lang}{@$option}{/lang}</option>
{/foreach}
</select>