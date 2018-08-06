{include file='__formFieldHeader'}

{foreach from=$field->getOptions() key=$__fieldValue item=__fieldLabel}
	<label><input type="radio" name="{@$field->getPrefixedId()}" value="{$__fieldValue}"{if $field->getValue() === $__fieldValue} checked{/if}> {@$__fieldLabel}</label>
{/foreach}

{include file='__formFieldFooter'}
