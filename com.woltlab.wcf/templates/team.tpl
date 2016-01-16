{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.team{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
	
	<link rel="canonical" href="{link controller='Team'}{/link}" />
	
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
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{capture assign='sidebar'}
	{@$__boxSidebar}
{/capture}

{include file='header' sidebarOrientation='right'}

<header class="boxHeadline">
	<h1>{lang}wcf.user.team{/lang}</h1>
</header>

{include file='userNotice'}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

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

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsBottom'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{include file='footer'}

</body>
</html>
