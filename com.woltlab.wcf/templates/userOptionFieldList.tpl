{foreach from=$options item=optionData}
	{assign var=option value=$optionData[object]}
	<dl class="{$option->optionName}Input{if $errorType|is_array && $errorType[$option->optionName]|isset} formError{/if}">
		<dt{if $optionData[cssClassName]} class="{$optionData[cssClassName]}"{/if}>{if $isSearchMode|empty || !$optionData[hideLabelInSearch]}<label for="{$option->optionName}">{lang}{@$langPrefix}{$option->optionName}{/lang}</label>{if $inSearchMode|empty && $option->required} <span class="customOptionRequired">*</span>{/if}{/if}</dt>
		<dd>{@$optionData[html]}
			<small>{lang __optional=true}{@$langPrefix}{$option->optionName}.description{/lang}</small>
			
			{if $errorType|is_array && $errorType[$option->optionName]|isset}
				<small class="innerError">
					{if $errorType[$option->optionName] == 'empty'}
						{lang}wcf.global.form.error.empty{/lang}
					{else}
						{lang}{@$langPrefix}error.{$errorType[$option->optionName]}{/lang}
					{/if}
				</small>
			{/if}
		</dd>
	</dl>
{/foreach}
