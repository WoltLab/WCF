<script data-relocate="true">
	require(["WoltLabSuite/Core/Acp/Component/Label/Availability"], ({ setup }) => {
		setup();
	});
</script>

<div id="connect" class="formSection">
	{foreach from=$labelObjectTypeContainers item=container}
		<div class="formField" role="group" aria-labelledby="labelObjectTypes{$container->objectTypeID}Label">
			<div class="formField__label"><span id="labelObjectTypes{$container->objectTypeID}Label">{$container->getTitle()}</span></div>
			<div class="formField__input">
				<ul class="structuredList">
					{foreach from=$container item=objectType}
						<li class="{if $objectType->isCategory()} category{/if}"{if $objectType->getDepth()} style="padding-left: {$objectType->getDepth() * 20}px"{/if} data-depth="{$objectType->getDepth()}">
							<span>{$objectType->getLabel()}</span>
							<label><input id="checkbox_{$container->objectTypeID}_{$objectType->getObjectID()}" type="checkbox" name="objectTypes[{$container->objectTypeID}][]" value="{$objectType->getObjectID()}"{if $objectType->getOptionValue()} checked{/if}></label>
						</li>
					{/foreach}
				</ul>
			</div>
		</div>
	{/foreach}
</div>
