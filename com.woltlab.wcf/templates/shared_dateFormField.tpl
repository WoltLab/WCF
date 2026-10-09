<input {*
	*}type="{if $field->supportsTime()}datetime-local{else}date{/if}" {*
	*}data-native-date-picker {*
	*}id="{$field->getPrefixedId()}" {*
	*}name="{$field->getPrefixedId()}" {*
	*}value="{$dateFormFieldValue}"{*
	*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{if $field->isAutofocused()} autofocus{/if}{*
	*}{if $field->isRequired()} required{/if}{*
	*}{if $field->isImmutable()} disabled{/if}{*
	*}{if $field->getEarliestDate() !== null} min="{$dateFormFieldEarliestDate}"{/if}{*
	*}{if $field->getLatestDate() !== null} max="{$dateFormFieldLatestDate}"{/if}{*
	*}{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*

*}>
