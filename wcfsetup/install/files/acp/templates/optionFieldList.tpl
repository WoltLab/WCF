{foreach from=$options item=optionData}
	{assign var=option value=$optionData[object]}
	{if $errorType|is_array && $errorType[$option->optionName]|isset}
		{assign var=error value=$errorType[$option->optionName]}
	{else}
		{assign var=error value=''}
	{/if}
	<dl class="{$option->optionName}Input">
		<dt{if $optionData[cssClassName]} class="{$optionData[cssClassName]}"{/if}><label for="{$option->optionName}">{lang}{@$langPrefix}{$option->optionName}{/lang}</label></dt>
		<dd>{@$optionData[html]}
			{if $error}
				<small class="innerError">
					{if $error == 'empty'}
						{lang}wcf.global.error.empty{/lang}
					{else}	
						{lang}wcf.user.option.error.{$error}{/lang}
					{/if}
				</small>
			{/if}
			<small>{lang}{@$langPrefix}{$option->optionName}.description{/lang}</small>
		</dd>
	</dl>	
{/foreach}