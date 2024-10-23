{if !$isGuestGroup|isset}{assign var=isGuestGroup value=false}{/if}
{if !$groupIsOwner|isset}{assign var=groupIsOwner value=false}{/if}
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
					{if $groupIsOwner && $option->optionName|in_array:$ownerGroupPermissions}
						<span class="jsTooltip" title="{lang}wcf.acp.group.ownerGroupPermission{/lang}">
							{icon name='shield-halved'}
						</span>
					{/if}
					
					{$langPrefix|concat:$option->optionName|phrase}

					{event name='afterOptionName'}
				</label>
			{/if}
		</dt>
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
