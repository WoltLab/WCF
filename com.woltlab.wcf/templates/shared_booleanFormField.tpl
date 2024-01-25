<ol class="flexibleButtonGroup">
	<li>
		<input type="radio" {*
			*}id="{$field->getPrefixedId()}" {*
			*}name="{$field->getPrefixedId()}" {*
			*}value="1" {*
			*}data-no-input-id="{$field->getPrefixedId()}_no"{*
			*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
			*}{if $field->isAutofocused()} autofocus{/if}{*
			*}{if $field->isRequired()} required{/if}{*
			*}{if $field->isImmutable()} disabled{/if}{*
			*}{if $field->getValue()} checked{/if}{*
			*}{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
		*}>
		<label for="{$field->getPrefixedId()}" class="green">{icon name='check'} {lang}wcf.global.form.boolean.yes{/lang}</label>
	</li>
	<li>
		<input type="radio" {*
			*}id="{$field->getPrefixedId()}_no" {*
			*}name="{$field->getPrefixedId()}" {*
			*}value="0"{*
			*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
			*}{if $field->isImmutable()} disabled{/if}{*
			*}{if !$field->getValue()} checked{/if}{*
			*}{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
		*}>
		<label for="{$field->getPrefixedId()}_no" class="red">{icon name='xmark'} {lang}wcf.global.form.boolean.no{/lang}</label>
	</li>
</ol>
