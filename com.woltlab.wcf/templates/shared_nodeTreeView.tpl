<div class="nodeTreeView" id="{$view->getID()}">
	<ol class="nodeTreeView__list" data-parent-object-id="0">
		{unsafe:$view->renderItems()}
	</ol>

	<div class="nodeTreeView__footer" id="{$view->getID()}_footer" hidden>
		<button
			type="button"
			class="button buttonPrimary small nodeTreeView__submitButton"
			id="{$view->getID()}_submitButton"
		>
			{lang}wcf.global.button.saveSorting{/lang}
		</button>
	</div>
</div>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Component/NodeTreeView'], ({ NodeTreeView }) => {
		new NodeTreeView(
			'{unsafe:$view->getID()|encodeJS}',
			'{unsafe:$view->getSetPositionsEndpoint()|encodeJS}',
		);
	});
</script>
{unsafe:$view->renderInteractionInitialization()}
