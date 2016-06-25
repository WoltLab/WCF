<select id="{$option->optionName}" name="values[{$option->optionName}]">
	{if !$allowEmptyValue|empty}<option value="">{lang}wcf.global.noSelection{/lang}</option>{/if}
	{foreach from=$selectOptions key=key item=selectOption}
		<option value="{$key}"{if $value == $key} selected{/if}>{lang}{@$selectOption}{/lang}</option>
	{/foreach}
</select>