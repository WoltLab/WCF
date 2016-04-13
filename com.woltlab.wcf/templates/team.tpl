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

{include file='header'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.user.team{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{include file='userNotice'}

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

{include file='footer'}

</body>
</html>
