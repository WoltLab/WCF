{if $view->hasNodes()}
	<div class="nodeTreeView" id="{$view->getID()}">
		<ol class="nodeTreeView__list" data-parent-object-id="0">
			{unsafe:$view->renderItems()}
		</ol>

		<div class="nodeTreeView__footer" id="{$view->getID()}_footer" hidden>
			<div class="nodeTreeView__footer__container">
				<button
					type="button"
					class="button buttonPrimary small nodeTreeView__submitButton"
					id="{$view->getID()}_submitButton"
				>
					{lang}wcf.global.button.saveSorting{/lang}
				</button>
			</div>
		</div>
	</div>

	<script data-relocate="true">
		require(['WoltLabSuite/Core/Component/NodeTreeView'], ({ NodeTreeView }) => {
			new NodeTreeView(
				'{unsafe:$view->getID()|encodeJS}',
				'{unsafe:$view->getClassName()|encodeJS}',
				new Map([
					{foreach from=$view->getParameters() key='name' item='value'}
						['{unsafe:$name|encodeJS}', {unsafe:$value|json}],
					{/foreach}
				]),
				'{unsafe:$view->getSetPositionsEndpoint()|encodeJS}',
			);
		});
	</script>
	{unsafe:$view->renderInteractionInitialization()}
{else}
	<woltlab-core-notice type="info">{lang}wcf.global.noItems{/lang}</woltlab-core-notice>
{/if}
