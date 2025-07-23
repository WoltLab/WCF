<div class="dropdown {$configuration->containerCssClassName}">
	<button type="button" class="jsTooltip dropdownToggle {$configuration->cssClassName}" title="{lang}wcf.global.button.more{/lang}">
		{icon name=$configuration->icon size=$configuration->iconSize}
	</button>

	<ul class="dropdownMenu">
		{unsafe:$contextMenuOptions}
	</ul>
</div>
