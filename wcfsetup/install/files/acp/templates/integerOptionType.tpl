{if $option->suffix}
	<div class="inputAddon">
{/if}

<input type="number" id="{$option->optionName}" name="values[{$option->optionName}]" value="{$value}"{if $option->minvalue !== null} min="{$option->minvalue}"{/if}{if $option->maxvalue !== null} max="{$option->maxvalue}"{/if}{if $inputClass} class="{@$inputClass}"{/if}>

{if $option->suffix}
		<span class="inputSuffix">{lang}wcf.acp.option.suffix.{@$option->suffix}{/lang}</span>
	</div>
{/if}