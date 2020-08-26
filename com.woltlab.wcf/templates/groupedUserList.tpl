{foreach from=$groupedUsers item=group}
	{if $group}
		<section class="section sectionContainerList">
			<h2 class="sectionTitle">{$group}</h2>
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
				'wcf.user.button.follow': '{jslang}wcf.user.button.follow{/jslang}',
				'wcf.user.button.ignore': '{jslang}wcf.user.button.ignore{/jslang}',
				'wcf.user.button.unfollow': '{jslang}wcf.user.button.unfollow{/jslang}',
				'wcf.user.button.unignore': '{jslang}wcf.user.button.unignore{/jslang}'
			});
			
			new WCF.User.Action.Follow($('.jsGroupedUserList > li'));
			new WCF.User.Action.Ignore($('.jsGroupedUserList > li'));
		});
</script>
