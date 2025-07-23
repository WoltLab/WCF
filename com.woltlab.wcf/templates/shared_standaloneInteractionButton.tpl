<div class="dropdown {$configuration->cssClassName}" id="{$containerID}">
	<button 
		type="button"
		class="dropdownToggle {$configuration->buttonCssClassName}{if !$configuration->label} jsTooltip{/if}"
		{if !$configuration->label}title="{lang}{$configuration->tooltip}{/lang}"{/if}
	>
		{icon name=$configuration->icon size=$configuration->iconSize}
		{if $configuration->label}
			<span>{$configuration->label}</span>
		{/if}
	</button>

	<ul class="dropdownMenu {$configuration->dropdownMenuCssClassName}">
		{unsafe:$contextMenuOptions}
	</ul>
</div>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Component/Interaction/StandaloneButton'], ({ StandaloneButton }) => {
		new StandaloneButton(
			document.getElementById('{unsafe:$containerID|encodeJS}'),
			'{unsafe:$providerClassName|encodeJS}',
			'{unsafe:$objectID|encodeJS}',
			'{unsafe:$redirectUrl|encodeJS}',
			'{unsafe:$reloadHeaderEndpoint|encodeJS}'
		);
	});
</script>

{unsafe:$initializationCode}
