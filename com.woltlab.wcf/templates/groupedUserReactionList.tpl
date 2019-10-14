{foreach from=$groupedUsers item=group}
	{if $group}
		<section class="section sectionContainerList">
			<h2 class="sectionTitle">{@$group}</h2>
	{/if}
	
	{if $group|count}
		<ol class="containerList jsGroupedUserList">
			{foreach from=$group item=user}
				{include file='userListItem'}
			{/foreach}
		</ol>
	{else}
		<p>{$group->getNoUsersMessage()}</p>
	{/if}
	
	{if $group}
		</section>
	{/if}
{/foreach}

<div class="paginationBottom jsPagination"></div>

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

{event name='groupedUserReactionList'}
