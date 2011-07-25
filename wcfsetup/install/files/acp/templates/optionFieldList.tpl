{foreach from=$options item=optionData}
	{assign var=option value=$optionData[object]}
	{if $errorType|is_array && $errorType[$option->optionName]|isset}
		{assign var=error value=$errorType[$option->optionName]}
	{else}
		{assign var=error value=''}
	{/if}
	<dl class="{$option->optionName}Input">
		<dt><label for="{$option->optionName}">{lang}{@$langPrefix}{$option->optionName}{/lang}</label></dt>
		<dd>{@$optionData[html]}</dd>
		{if $error}
			<p class="innerError">
				{if $error == 'empty'}
					{lang}wcf.global.error.empty{/lang}
				{else}	
					{lang}wcf.user.option.error.{$error}{/lang}
				{/if}
			</p>
		{/if}
		<p>{lang}{@$langPrefix}{$option->optionName}.description{/lang}</p>
	</dl>	
{/foreach}