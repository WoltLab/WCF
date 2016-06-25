{foreach from=$bbCodes item='bbCode'}
	<label><input type="checkbox" name="values[{$option->optionName}][]" value="{$bbCode}" {if $bbCode|in_array:$selectedBBCodes}checked {/if}> {$bbCode}</label>
{/foreach}