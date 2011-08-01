<ul class="formOptionsLong">
	{foreach from=$selectOptions key=key item=selectOption}
		<li>
			<label><input type="radio" {if $disableOptions[$key]|isset || $enableOptions[$key]|isset}class="enablesOptions" data-disableOptions="[ {@$disableOptions[$key]}]" data-enableOptions="[ {@$enableOptions[$key]}]" {/if}
			name="values[{$option->optionName}]" value="{$key}"
			{if $value == $key} checked="checked"{/if} />
			{lang}{@$selectOption}{/lang}</label>
		</li>
	{/foreach}
</ul>
