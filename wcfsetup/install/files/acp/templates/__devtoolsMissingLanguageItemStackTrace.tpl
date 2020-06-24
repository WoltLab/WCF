<span class="icon icon16 fa-align-justify jsTooltip pointer jsOutputFormatToggle" title="{lang}wcf.acp.devtools.missingLanguageItem.stackTrace.toggleOutputFormat{/lang}" style="margin-bottom: 10px"></span>

<pre>{*
	*}{foreach from=$stackTrace item=stackEntry name=stackEntries}{*
		*}#{$tpl[foreach][stackEntries][iteration]} {$stackEntry[file]} ({$stackEntry[line]}):
{*		*}    {$stackEntry['class']}{$stackEntry['type']}{$stackEntry['function']}({*
				*}{foreach from=$stackEntry['args'] item=stackEntryArg name=stackEntryArgs}{*
					*}{assign var='argType' value=$stackEntryArg|gettype}{*
					*}{if $argType === 'integer' || $argType === 'double'}{*
						*}{$stackEntryArg}{*
					*}{elseif $argType === 'NULL'}{*
						*}null{*
					*}{elseif $argType === 'string'}{*
						*}'{$stackEntryArg}'{*
					*}{elseif $argType === 'boolean'}{*
						*}{if $stackEntryArg}true{else}false{/if}{*
					*}{elseif $argType === 'array'}{*
						*}[{if $stackEntryArg|count > 5}{$stackEntryArg|count} items{else}{implode from=$stackEntryArg|array_keys item=stackEntryKey}{$stackEntryKey} => {/implode}{/if}]{*
					*}{elseif $argType === 'object'}{*
						*}{$item|get_class}{*
					*}{elseif $argType === 'resource'}{*
						*}resource({$item|get_resource_type}){*
					*}{elseif $argType === 'resource (closed)'}{*
						*}resource (closed){*
					*}{/if}{if !$tpl[foreach][stackEntryArgs][last]},{/if}{*
				*}{/foreach}{*
			*})
{*	*}{/foreach}{*
*}</pre>
