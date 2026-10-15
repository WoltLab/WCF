<span
	class="trophyIcon{if $showTooltip} jsTooltip{/if}"
	style="color: {$trophy->iconColor}; background-color: {$trophy->badgeColor}{if $trophy->borderColor}; --trophy-border-color: {$trophy->borderColor}{/if}"
	data-trophy-id="{$trophy->trophyID}"
	{if $showTooltip}title="{$trophy->getTitle()}"{/if}
>
	{unsafe:$trophy->getIcon()->toHtml($size)}
</span>
