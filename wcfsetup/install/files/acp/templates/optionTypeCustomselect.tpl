<ul class="formOptionsLong smallFont">
	{foreach from=$selectOptions key=key item=selectOption}
		<li>
			<label><input type="radio" name="values[{$option->optionName}]" value="{$key}"{if $value == $key} checked="checked"{/if} /> {lang}{@$selectOption}{/lang}</label>
		</li>
	{/foreach}
	<li>
		<label><input type="radio" name="values[{$option->optionName}]" value=""{if $value == $customValue} checked="checked"{/if} /></label>
		<input style="width: 400px" id="{$option->optionName}_custom" type="text" class="inputText" name="values[{$option->optionName}_custom]" value="{$customValue}" />
	</li>
</ul>