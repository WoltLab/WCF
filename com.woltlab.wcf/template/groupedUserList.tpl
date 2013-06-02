{foreach from=$groupedUsers item=group}
	{if $group}
		<header class="boxHeadline">
			<h1>{$group}</h1>
		</header>
	{/if}
	
	{if $group|count}
		<div class="container marginTop">
			<ol class="containerList jsGroupedUserList">
				{foreach from=$group item=user}
					{include file='userListItem'}
				{/foreach}
			</ol>
		</div>
	{else}
		<p class="marginTop">{$group->getNoUsersMessage()}</p>
	{/if}
{/foreach}

<div class="contentNavigation"><div class="jsPagination"></div></div>

<script type="text/javascript">
	//<![CDATA[
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
	//]]>
</script>