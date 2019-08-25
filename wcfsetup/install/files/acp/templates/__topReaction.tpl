{assign var='topReaction' value=$__wcf->getReactionHandler()->getTopReaction($cachedReactions)}
{if $topReaction}
	{if $render === 'tiny'}
		<span class="topReactionTiny jsTooltip" title="{lang reaction=$topReaction[reaction] count=$topReaction[count] other=$topReaction[other]}wcf.like.reaction.topReaction{/lang}">
			{@$topReaction[reaction]->renderIcon()} {#$topReaction[count]}
		</span>
	{elseif $render === 'short'}
		<span class="topReactionShort jsTooltip" title="{lang reaction=$topReaction[reaction] count=$topReaction[count] other=$topReaction[other]}wcf.like.reaction.topReaction{/lang}">
			{@$topReaction[reaction]->renderIcon()} × {#$topReaction[count]}
		</span>
	{elseif $render === 'full'}
		<span class="topReactionFull">
			{@$topReaction[reaction]->renderIcon()} {lang reaction=$topReaction[reaction] count=$topReaction[count] other=$topReaction[other]}wcf.like.reaction.topReaction{/lang}
		</span>
	{/if}
{/if}
