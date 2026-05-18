<div class="nodeTreeView" id="{$view->getID()}">
	<ol class="nodeTreeView__list" data-parent-object-id="0">
		{unsafe:$view->renderItems()}
	</ol>
</div>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Component/NodeTreeView'], ({ NodeTreeView }) => {
		new NodeTreeView(
			'{unsafe:$view->getID()|encodeJS}',
		);
	});
</script>
{unsafe:$view->renderInteractionInitialization()}
