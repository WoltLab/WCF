{if !$isGuestGroup|isset}{assign var=isGuestGroup value=false}{/if}
{foreach from=$options item=optionData}
	{assign var=option value=$optionData[object]}
	{if $errorType|is_array && $errorType[$option->optionName]|isset}
		{assign var=error value=$errorType[$option->optionName]}
	{else}
		{assign var=error value=''}
	{/if}
	<dl class="{$option->optionName}Input{if $error} formError{/if}">
		<dt{if $optionData[cssClassName]} class="{$optionData[cssClassName]}"{/if}>
			{if $isSearchMode|empty || !$optionData[hideLabelInSearch]}
				<label for="{$option->optionName}">
					{if VISITOR_USE_TINY_BUILD && $isGuestGroup && $option->excludedInTinyBuild}
						<span class="jsTooltip" title="{lang}wcf.acp.group.excludedInTinyBuild{/lang}">
							{icon name='bolt'}
						</span>
					{/if}
					{if $optionData[title]|isset}{$optionData[title]}{else}{lang}{$langPrefix}{$option->optionName}{/lang}{/if}
				</label>
			{/if}
		</dt>
		<dd>{unsafe:$optionData[html]}
			{if $error}
				<small class="innerError">
					{if $error == 'empty'}
						{lang}wcf.global.form.error.empty{/lang}
					{else}
						{lang}{$langPrefix}error.{$error}{/lang}
					{/if}
				</small>
			{/if}
			<small>{if $optionData[description]|isset}{$optionData[description]}{else}{lang __optional=true}{$langPrefix}{$option->optionName}.description{/lang}{/if}</small>
		</dd>
	</dl>
{/foreach}
