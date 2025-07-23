<div class="dropdown">
	<button type="button" class="{$configuration->cssClassName} jsTooltip dropdownToggle" title="{lang}wcf.global.button.more{/lang}">
		{icon name=$configuration->icon size=$configuration->iconSize}
	</button>

	<ul class="dropdownMenu">
		{unsafe:$contextMenuOptions}
	</ul>
</div>
