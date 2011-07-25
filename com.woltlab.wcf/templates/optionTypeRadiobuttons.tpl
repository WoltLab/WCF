<ul class="formOptionsLong">
	{foreach from=$options item=option key=key}
		<li>
			<label><input {if $option.enableOptions}onclick="if (IS_SAFARI) {@$option.enableOptions}" onfocus="{@$option.enableOptions}" {/if}
			type="radio" name="values[{$optionData.optionName}]" value="{$key}"
			{if $optionData.optionValue == $key}checked="checked" {/if}/>
			{lang}{@$option.value}{/lang}</label>
		</li>
	{/foreach}
</ul>