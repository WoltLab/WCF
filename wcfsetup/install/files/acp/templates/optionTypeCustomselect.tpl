<ul class="formOptionsLong smallFont">
	{foreach from=$selectOptions key=key item=selectOption}
		<li>
			<label><input type="radio" name="values[{$option->optionName}]"{if $value == $key} checked="checked"{/if} value="{$key}" /> {lang}{@$selectOption}{/lang}</label>
		</li>
	{/foreach}
	<li>
		<label><input type="radio" name="values[{$option->optionName}]"{if $value == $customValue} checked="checked"{/if} value="" /></label>
		<input type="text" id="{$option->optionName}_custom" name="values[{$option->optionName}_custom]" value="{$customValue}" class="inputText" />
	</li>
</ul>