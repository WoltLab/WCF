{if $button->isSubmit()}
	<input id="{$button->getPrefixedId()}" {*
		*}type="submit" {*
		*}value="{$button->getLabel()}"{*
		*}{if $button->getAccessKey()} accesskey="{$button->getAccessKey()}"{/if}{*
		*}{if !$button->getClasses()|empty} class="{implode from=$button->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
		*}{foreach from=$button->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
		*}>
{else}
	<button id="{$button->getPrefixedId()}"{*
		*}type="button" {*
		*}class="button{if !$button->getClasses()|empty} {implode from=$button->getClasses() item='class' glue=' '}{$class}{/implode}{/if}"{*
		*}{foreach from=$button->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
		*}{if $button->getAccessKey()} accesskey="{$button->getAccessKey()}"{/if}{*
	*}>{$button->getLabel()}</button>
{/if}
