<div class="floatContainer">
	{foreach from=$dateInputOrder item=element}
		<div class="floatedElement">
			<label for="{$optionData.optionName}{$element|ucfirst}">{lang}wcf.global.date.{$element}{/lang}</label>
			
			{if $element == 'day'}
				<select id="{$optionData.optionName}Day" name="values[{$optionData.optionName}][day]">
					{htmlOptions options=$days selected=$day}
				</select>
			{/if}
			
			{if $element == 'month'}
				<select id="{$optionData.optionName}Month" name="values[{$optionData.optionName}][month]">
					{htmlOptions options=$months selected=$month}
				</select>
			{/if}
			
			{if $element == 'year'}
				<input id="{$optionData.optionName}Year" class="inputText fourDigitInput" type="text" name="values[{$optionData.optionName}][year]" value="{$year}" maxlength="4" />
			{/if}
		</div>
	{/foreach}
</div>
{if !$yearRequired}
	<p class="smallFont light">{lang}wcf.global.date.year.notRequired{/lang}</p>
{/if}