<input type="text" {*
	*}id="{@$field->getPrefixedId()}" {*
	*}name="{@$field->getPrefixedId()}" {*
	*}value="{if !$field->isI18n() || !$field->hasI18nValues() || $availableLanguages|count === 1}{$field->getValue()}{/if}" {*
	*}{if !$field->getFieldClasses()|empty}class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}" {/if}{*
	*}autocomplete="off" {*
	*}pattern="[0-9\s]*" {*
	*}inputmode="numeric"{*
	*}{if $field->getChunks() && $field->getChunkLength()} size="{$field->getChunks() - 1 + $field->getChunks() * $field->getChunkLength()}"{/if}{*
	*}{if $field->isAutofocused()} autofocus{/if}{*
	*}{if $field->isRequired()} required{/if}{*
	*}{if $field->isImmutable()} disabled{/if}{*
	*}{if $field->getMinimumLength() !== null} minlength="{$field->getMinimumLength()}"{/if}{*
	*}{if $field->getMaximumLength() !== null} maxlength="{$field->getMaximumLength()}"{/if}{*
	*}{if $field->getPlaceholder() !== null} placeholder="{$field->getPlaceholder()}"{/if}{*
	*}{if $field->getDocument()->isAjax()} data-dialog-submit-on-enter="true"{/if}{*
*}>
