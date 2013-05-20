{foreach from=$availableOptions item=availableOption}
	<label><input type="checkbox" name="values[{$option->optionName}][]" value="{$availableOption}" {if $availableOption|in_array:$value}checked="checked" {/if}/> {lang}wcf.user.option.{$availableOption}{/lang}</label>
{/foreach}
