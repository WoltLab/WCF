{foreach from=$selectOptions key=key item=selectOption}
	<label><input type="checkbox" name="values[{$option->optionName}][]" value="{$key}"{if $key|in_array:$value} checked{/if}> {lang}{@$selectOption}{/lang}</label>
{/foreach}
