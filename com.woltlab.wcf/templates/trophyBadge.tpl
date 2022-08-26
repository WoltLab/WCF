<span
	class="trophyIcon{if $showTooltip} jsTooltip{/if}"
	style="color: {$trophy->iconColor}; background-color: {$trophy->badgeColor}"
	data-trophy-id="{$trophy->trophyID}"
	{if $showTooltip}title="{$trophy->getTitle()}"{/if}
>
	{@$trophy->getIcon()->toHtml($size)}
</span>
