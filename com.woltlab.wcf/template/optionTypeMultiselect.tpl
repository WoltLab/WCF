<select name="values[{$optionData.optionName}][]" id="{$optionData.optionName}" multiple="multiple" size="{if $options|count > 10}10{else}{@$options|count}{/if}">
{foreach from=$options item=option key=key}
	<option value="{$key}"{if $key|in_array:$optionData.optionValue} selected="selected"{/if}>{lang}{@$option}{/lang}</option>
{/foreach}
</select>