<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/Reaction/Profile/Loader', 'Language'], function(UiReactionProfileLoader, Language) {
		Language.addObject({
			'wcf.like.reaction.noMoreEntries': '{lang}wcf.like.reaction.noMoreEntries{/lang}',
			'wcf.like.reaction.more': '{lang}wcf.like.reaction.more{/lang}'
		});
		
		new UiReactionProfileLoader({@$userID});
	});
</script>

<ul id="likeList" class="containerList recentActivityList likeList" data-last-like-time="{@$lastLikeTime}">
	<li class="containerListButtonGroup likeTypeSelection">
		<ul class="buttonGroup" id="likeType">
			<li><a class="button small active" data-like-type="received">{lang}wcf.like.reactionsReceived{/lang}</a></li>
			<li><a class="button small" data-like-type="given">{lang}wcf.like.reactionsGiven{/lang}</a></li>
		</ul>
		
		<ul class="buttonGroup" id="reactionType">
			{foreach from=$__wcf->getReactionHandler()->getReactionTypes() item=reactionType name=reactionTypeLoop}
				<li><a class="button small jsTooltip" data-reaction-type-id="{$reactionType->reactionTypeID}" title="{$reactionType->getTitle()}">{@$reactionType->renderIcon()} <span class="invisible">{$reactionType->getTitle()}</span></a></li>
			{/foreach}
		</ul>
	</li>
	
	{include file='userProfileLikeItem'}
</ul>