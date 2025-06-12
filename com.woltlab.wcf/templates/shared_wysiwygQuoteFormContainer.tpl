<div id="{$container->getPrefixedId()|encodeJS}Container"{*
	*} class="messageTabMenuContent messageTabMenuContent--quotes"></div>

<script data-relocate="true">
	require(["WoltLabSuite/Core/Component/Quote/List"], ({ setup }) => {
		setup("{unsafe:$container->getWysiwygId()|encodeJS}", "{unsafe:$container->getPrefixedId()|encodeJS}Container");
	});
</script>

{include file='shared_formContainerDependencies'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Container/WysiwygTab'], ({ WysiwygTab }) => {
		new WysiwygTab('{unsafe:$container->getPrefixedId()|encodeJS}Container', '{unsafe:$container->getName()|encodeJS}', '{unsafe:$container->getWysiwygId()|encodeJS}');
	});
</script>
