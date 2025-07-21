<img
	src="{$trophy->getFile()->getFullSizeImageSource()}"
	width="{$size}"
	height="{$size}"
	{if $showTooltip}title="{$trophy->getTitle()}"{/if}
	class="trophyIcon{if $showTooltip} jsTooltip{/if}"
	data-trophy-id="{$trophy->getObjectID()}"
	loading="lazy"
	alt="{$trophy->getTitle()}"
/>
