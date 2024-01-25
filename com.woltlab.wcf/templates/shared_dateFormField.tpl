<input {*
	*}type="{if $field->supportsTime()}datetime{else}date{/if}" {*
	*}id="{$field->getPrefixedId()}" {*
	*}name="{$field->getPrefixedId()}" {*
	*}value="{$field->getValue()}"{*
	*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{if $field->isAutofocused()} autofocus{/if}{*
	*}{if $field->isRequired()} required{/if}{*
	*}{if $field->isImmutable()} disabled{/if}{*
	*}{if $field->getEarliestDate() !== null} min="{$dateFormFieldEarliestDate}"{/if}{*
	*}{if $field->getLatestDate() !== null} max="{$dateFormFieldLatestDate}"{/if}{*
	*}{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*

*}>
