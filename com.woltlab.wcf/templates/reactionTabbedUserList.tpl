<nav class="tabMenu">
	<ul>
		{foreach from=$reactions item=reaction}
			<li data-reaction-type-id="{$reaction->reactionTypeID}"{if $reactionTypeID == $reaction->reactionTypeID} class="active ui-state-active"{/if}><a>{@$reaction->renderIcon()} {$reaction->getTitle()}</a></li>
		{/foreach}
	</ul>
</nav>

{if !$reactionUserList|empty}
	<ol class="containerList jsGroupedUserList section">
		{foreach from=$reactionUserList item=reaction}
			{assign var=user value=$reaction->getUserProfile()}
			{include file='userListItem'}
		{/foreach}
	</ol>
	
	<div class="paginationBottom jsPagination"></div>
{else}
	<p class="info">{lang}wcf.reactions.summary.noReactions{/lang}</p>
{/if}

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.user.button.follow': '{lang}wcf.user.button.follow{/lang}',
			'wcf.user.button.ignore': '{lang}wcf.user.button.ignore{/lang}',
			'wcf.user.button.unfollow': '{lang}wcf.user.button.unfollow{/lang}',
			'wcf.user.button.unignore': '{lang}wcf.user.button.unignore{/lang}'
		});
		
		new WCF.User.Action.Follow($('.jsGroupedUserList > li'));
		new WCF.User.Action.Ignore($('.jsGroupedUserList > li'));
	});
</script>