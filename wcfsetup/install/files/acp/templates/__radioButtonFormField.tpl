{foreach from=$field->getOptions() key=$__fieldValue item=__fieldLabel}
	<label>
		<input {*
			*}type="radio" {*
			*}name="{@$field->getPrefixedId()}" {*
			*}value="{$__fieldValue}"{*
			*}{if $field->getValue() === $__fieldValue} checked{/if}{*
			*}{if $field->isImmutable()} disabled{/if}{*
		*}> {@$__fieldLabel}
	</label>
{/foreach}
