<span 
	class="icon icon{$size} fa-{$trophy->iconName} trophyIcon{if $showTooltip} jsTooltip{/if}" 
	style="color: {$trophy->iconColor}; background-color: {$trophy->badgeColor}"
	data-trophy-id="{$trophy->trophyID}"
	{if $showTooltip}title="{$trophy->getTitle()}"{/if}
></span>
