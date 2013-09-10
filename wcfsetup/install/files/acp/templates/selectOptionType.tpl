<select id="{$option->optionName}" name="values[{$option->optionName}]">
	{if $option->allowEmptyValue}<option value="">{lang}wcf.global.noSelection{/lang}</option>{/if}
	{foreach from=$selectOptions key=key item=selectOption}
		<option value="{$key}"{if $value == $key} selected="selected"{/if}>{lang}{@$selectOption}{/lang}</option>
	{/foreach}
</select>