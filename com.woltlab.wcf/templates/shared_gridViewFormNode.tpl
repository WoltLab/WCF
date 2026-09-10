<div id="{$node->getPrefixedId()}Container"{*
	*}{if !$node->getClasses()|empty} class="{implode from=$node->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{foreach from=$node->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
*}>
	{unsafe:$node->getGridView()->render()}
</div>
