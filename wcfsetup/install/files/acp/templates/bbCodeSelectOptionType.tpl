{foreach from=$bbCodes item='bbCode'}
	<label{if $bbCode === 'html'} class="jsBbcodeSelectOptionHtml"{/if}><input type="checkbox" name="values[{$option->optionName}][]" value="{$bbCode}" {if $bbCode|in_array:$selectedBBCodes}checked {/if}> {$bbCode}</label>
{/foreach}