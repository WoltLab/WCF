<img
	src="{@$__wcf->getPath()}images/trophy/{$trophy->iconFile}"
	style="width:{$size}px;height:{$size}px"
	{if $showTooltip}title="{$trophy->getTitle()}"{/if}
	class="trophyIcon{if $showTooltip} jsTooltip{/if}"
	data-trophy-id="{$trophy->getObjectID()}"
/>
