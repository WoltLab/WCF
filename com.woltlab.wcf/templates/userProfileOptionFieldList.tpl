{foreach from=$options item=optionData}
	{assign var=option value=$optionData[object]}
	{if $errorType|is_array && $errorType[$option->optionName]|isset}
		{assign var=error value=$errorType[$option->optionName]}
	{else}
		{assign var=error value=''}
	{/if}
	<dl class="{$option->optionName}Input{if $error} formError{/if}">
		<dt{if $optionData[cssClassName]} class="{$optionData[cssClassName]}"{/if}><label for="{$option->optionName}">{$langPrefix|concat:$option->optionName|phrase}</label></dt>
		<dd>{@$optionData[html]}
			{if $error}
				<small class="innerError">
					{if $error == 'empty'}
						{lang}wcf.global.form.error.empty{/lang}
					{else}
						{lang}{@$langPrefix}error.{$error}{/lang}
					{/if}
				</small>
			{/if}
			<small>{lang __optional=true}{@$langPrefix}{$option->optionName}.description{/lang}</small>
		</dd>
	</dl>
{/foreach}
