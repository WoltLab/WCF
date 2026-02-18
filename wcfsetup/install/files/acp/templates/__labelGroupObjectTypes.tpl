<script data-relocate="true">
	require(["WoltLabSuite/Core/Acp/Component/Label/Availability"], ({ setup }) => {
		setup();
	});
</script>

<div id="connect">
	{foreach from=$labelObjectTypeContainers item=container}
		<dl>
			<dt>{$container->getTitle()}</dt>
			<dd>
				<ul class="structuredList">
					{foreach from=$container item=objectType}
						<li class="{if $objectType->isCategory()} category{/if}"{if $objectType->getDepth()} style="padding-left: {$objectType->getDepth() * 20}px"{/if} data-depth="{$objectType->getDepth()}">
							<span>{$objectType->getLabel()}</span>
							<label><input id="checkbox_{$container->objectTypeID}_{$objectType->getObjectID()}" type="checkbox" name="objectTypes[{$container->objectTypeID}][]" value="{$objectType->getObjectID()}"{if $objectType->getOptionValue()} checked{/if}></label>
						</li>
					{/foreach}
				</ul>
			</dd>
		</dl>
	{/foreach}
</div>
