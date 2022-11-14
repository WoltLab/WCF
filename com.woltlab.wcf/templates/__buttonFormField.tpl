<button {*
	*}type="submit" {*
	*}id="{$field->getPrefixedId()}" {*
	*}name="{$field->getPrefixedId()}" {*
	*}value="{$field->getValue()}"{*
	*}class="button {implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{*
	*}{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
*}>{$field->getButtonLabel()}</button>
