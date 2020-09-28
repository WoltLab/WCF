<select id="{$option->optionName}" name="values[{$option->optionName}]"{if $option->required} required{/if}{if $disableOptions || $enableOptions} class="jsEnablesOptions" data-is-boolean="true" data-disable-options="[ {@$disableOptions}]" data-enable-options="[ {@$enableOptions}]"{/if}>
	<option value="">{lang}wcf.global.noSelection{/lang}</option>
	{foreach from=$selectOptions key=key item=selectOption}
		<option value="{$key}"{if $value == $key} selected{/if}>{lang}{@$selectOption}{/lang}</option>
	{/foreach}
</select>
