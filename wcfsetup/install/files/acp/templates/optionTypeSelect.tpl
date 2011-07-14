<select name="values[{$option->optionName}]" id="{$option->optionName}">
{foreach from=$selectOptions key=key item=selectOption}
	<option value="{$key}"{if $value == $key} selected="selected"{/if}>{lang}{@$selectOption}{/lang}</option>
{/foreach}
</select>