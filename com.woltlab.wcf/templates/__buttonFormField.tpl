<button {*
	*}type="submit" {*
	*}id="{@$field->getPrefixedId()}" {*
	*}name="{@$field->getPrefixedId()}" {*
	*}value="{$field->getValue()}"{*
	*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
*}>{$field->getButtonLabel()}</button>
