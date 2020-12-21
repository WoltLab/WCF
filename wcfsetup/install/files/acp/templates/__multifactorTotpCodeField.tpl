<input type="text" {*
	*}id="{@$field->getPrefixedId()}" {*
	*}name="{@$field->getPrefixedId()}" {*
	*}value="{if !$field->isI18n() || !$field->hasI18nValues() || $availableLanguages|count === 1}{$field->getValue()}{/if}" {*
	*}{if !$field->getFieldClasses()|empty}class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}" {/if}{*
	*}autocomplete="off" {*
	*}{if $field->getMaximumLength() !== null}size="{$field->getMaximumLength()}" {/if}{*
	*}pattern="[0-9]*" {*
	*}inputmode="numeric"{*
	*}{if $field->isAutofocused()} autofocus{/if}{*
	*}{if $field->isRequired()} required{/if}{*
	*}{if $field->isImmutable()} disabled{/if}{*
	*}{if $field->getMinimumLength() !== null} minlength="{$field->getMinimumLength()}"{/if}{*
	*}{if $field->getMaximumLength() !== null} maxlength="{$field->getMaximumLength()}"{/if}{*
	*}{if $field->getPlaceholder() !== null} placeholder="{$field->getPlaceholder()}"{/if}{*
	*}{if $field->getDocument()->isAjax()} data-dialog-submit-on-enter="true"{/if}{*
*}>
