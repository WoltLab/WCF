<ol class="flexibleButtonGroup optionTypeBoolean">
	<li>
		<input type="radio" id="{$option->optionName}"{if $value == 1} checked{/if} name="values[{$option->optionName}]" value="1"{if $disableOptions || $enableOptions} class="jsEnablesOptions" data-is-boolean="true" data-disable-options="[ {@$disableOptions}]" data-enable-options="[ {@$enableOptions}]"{/if}>
		<label for="{$option->optionName}" class="green">{icon name='check'} {lang}wcf.acp.option.type.boolean.yes{/lang}</label>
	</li>
	<li>
		<input type="radio" id="{$option->optionName}_no"{if $value == 0} checked{/if} name="values[{$option->optionName}]" value="0"{if $disableOptions || $enableOptions} class="jsEnablesOptions" data-is-boolean="true" data-disable-options="[ {@$disableOptions}]" data-enable-options="[ {@$enableOptions}]"{/if}>
		<label for="{$option->optionName}_no" class="red">{icon name='xmark'} {lang}wcf.acp.option.type.boolean.no{/lang}</label>
	</li>
	{if !$option->optionName|str_starts_with:'admin.' && ($group === null || (!$group->isEveryone() && !$group->isUsers()))}
		<li>
			<input type="radio" id="{$option->optionName}_never"{if $value == -1} checked{/if} name="values[{$option->optionName}]" value="-1"{if $disableOptions || $enableOptions} class="jsEnablesOptions" data-is-boolean="true" data-disable-options="[ {@$disableOptions}]" data-enable-options="[ {@$enableOptions}]"{/if}>
			<label for="{$option->optionName}_never" class="yellow">{icon name='ban'} {lang}wcf.acp.option.type.boolean.never{/lang}</label>
		</li>
	{/if}
</ol>
