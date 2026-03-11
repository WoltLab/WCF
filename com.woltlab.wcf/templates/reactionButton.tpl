<button
	type="button"
	class="reactionButton jsTooltip button{if $reactionData->reactionTypeID} active{/if}"
	title="{lang}wcf.reactions.react{/lang}"
	data-reaction-type-id="{$reactionData->reactionTypeID}"
	data-reaction-object-type="{$reactionData->objectType}"
	data-object-id="{$reactionData->objectID}"
>
	{icon name='face-smile'}
</button>
