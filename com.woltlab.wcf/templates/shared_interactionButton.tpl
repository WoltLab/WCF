<div class="dropdown {$configuration->cssClassName}">
	<button
		type="button"
		class="dropdownToggle {$configuration->buttonCssClassName}{if !$configuration->label} jsTooltip{/if}"
		{if !$configuration->label}title="{lang}{$configuration->tooltip}{/lang}"{/if}
	>
		{unsafe:$configuration->getIconHtml()}
		{if $configuration->label}
			<span>{$configuration->label}</span>
		{/if}
	</button>

	<ul class="dropdownMenu {$configuration->dropdownMenuCssClassName}">
		{unsafe:$contextMenuOptions}
	</ul>
</div>
