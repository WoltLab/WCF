{foreach from=$field->getOptions() key=$__fieldValue item=__fieldLabel}
	<label>
		<input {*
			*}type="radio" {*
			*}name="{@$field->getPrefixedId()}" {*
			*}value="{$__fieldValue}"{*
			*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
			*}{if $field->getValue() !== null && $field->getValue() == $__fieldValue} checked{/if}{*
			*}{if $field->isImmutable()} disabled{/if}{*
		*}> {@$__fieldLabel}
	</label>
{/foreach}
