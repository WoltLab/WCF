<div id="{@$container->getPrefixedId()}" class="tabMenuContent{foreach from=$container->getClasses() item='class'} {$class}{/foreach}"{foreach from=$container->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}>
	{include file='__formContainerChildren'}
</div>
