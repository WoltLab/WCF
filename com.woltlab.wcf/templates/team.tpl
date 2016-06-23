{include file='header'}

{foreach from=$objects->getTeams() item=team}
	<section class="section sectionContainerList">
		<header class="sectionHeader">
			<h2 class="sectionTitle" id="group{@$team->groupID}">{$team->groupName|language} <span class="badge">{#$team->getMembers()|count}</span></h2>
			<small class="sectionDescription">{$team->groupDescription|language}</small>
		</header>
			
		<ol class="containerList userList">
			{foreach from=$team->getMembers() item=user}
				{include file='userListItem'}
			{/foreach}
		</ol>
	</section>
{/foreach}

<footer class="contentFooter">
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}{event name='contentFooterNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.user.button.follow': '{lang}wcf.user.button.follow{/lang}',
			'wcf.user.button.ignore': '{lang}wcf.user.button.ignore{/lang}',
			'wcf.user.button.unfollow': '{lang}wcf.user.button.unfollow{/lang}',
			'wcf.user.button.unignore': '{lang}wcf.user.button.unignore{/lang}'
		});
		
		new WCF.User.Action.Follow($('.userList > li'));
		new WCF.User.Action.Ignore($('.userList > li'));
	});
	//]]>
</script>

{include file='footer'}
