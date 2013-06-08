<select id="{$option->optionName}" name="values[{$option->optionName}][]" multiple="multiple" size="{if $selectOptions|count > 10}10{else}{@$selectOptions|count}{/if}">
{foreach from=$selectOptions key=key item=selectOption}
	<option value="{$key}"{if $key|in_array:$value} selected="selected"{/if}>{lang}{@$selectOption}{/lang}</option>
{/foreach}
</select>
