<button
	type="button"
	class="reactionButton jsTooltip button small{if $reactionData->reactionTypeID} active{/if}"
	title="{lang}wcf.reactions.react{/lang}"
	aria-pressed="{if $reactionData->reactionTypeID}true{else}false{/if}"
	data-reaction-type-id="{$reactionData->reactionTypeID}"
	data-reaction-object-type="{$reactionData->objectType}"
	data-object-id="{$reactionData->objectID}"
>
	{icon name='face-smile'}
</button>
