<div id="{$container->getPrefixedId()}Container"{*
	*} class="{implode from=$container->getClasses() item='class' glue=' '}{$class}{/implode} messageTabMenuContent--textModules"{*
	*}{foreach from=$container->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
*}>
	<div class="textModuleList__header">
		<button type="button" class="button small textModuleList__addButton" data-endpoint="{$container->getAddEndpoint()}">
			{icon name='plus'}
			<span>{lang}wcf.textModule.add{/lang}</span>
		</button>
	</div>
	<div class="textModuleList__gridView"></div>
</div>

<script data-relocate="true">
	require(["WoltLabSuite/Core/Component/TextModule/List"], ({ setup }) => {
		setup("{unsafe:$container->getPrefixedWysiwygId()|encodeJS}", "{unsafe:$container->getPrefixedId()|encodeJS}Container");
	});
</script>

{include file='shared_formContainerDependencies'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/WysiwygTab'], ({ WysiwygTab }) => {
		new WysiwygTab('{unsafe:$container->getPrefixedId()|encodeJS}Container', '{unsafe:$container->getName()|encodeJS}', '{unsafe:$container->getPrefixedWysiwygId()|encodeJS}');
	});
</script>
