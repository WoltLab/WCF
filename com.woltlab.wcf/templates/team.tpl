{include file='header'}

{foreach from=$objects->getTeams() item=team}
	<section class="section sectionContainerList">
		<header class="sectionHeader">
			<h2 class="sectionTitle" id="group{@$team->groupID}">{$team->getTitle()} <span class="badge">{#$team->getMembers()|count}</span></h2>
			<p class="sectionDescription">{$team->getDescription()}</p>
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
	$(function() {
		WCF.Language.addObject({
			'wcf.user.button.follow': '{jslang}wcf.user.button.follow{/jslang}',
			'wcf.user.button.ignore': '{jslang}wcf.user.button.ignore{/jslang}',
			'wcf.user.button.unfollow': '{jslang}wcf.user.button.unfollow{/jslang}',
			'wcf.user.button.unignore': '{jslang}wcf.user.button.unignore{/jslang}'
		});
		
		new WCF.User.Action.Follow($('.userList > li'));
		new WCF.User.Action.Ignore($('.userList > li'));
	});
</script>

{include file='footer'}
