{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.ignoredUsers{/lang} - {lang}wcf.user.usercp{/lang} - {PAGE_TITLE|language}</title>
	{include file='headInclude'}
	
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			new WCF.Action.Delete('wcf\\data\\user\\ignore\\UserIgnoreAction', '.jsIgnoredUser');
		});
		//]]>
	</script>
</head>

<body id="tpl{$templateName|ucfirst}">

{include file='userMenuSidebar'}

{include file='header' sidebarOrientation='left'}

<header class="boxHeadline">
	<h1>{lang}wcf.user.ignoredUsers{/lang} <span class="badge">{#$items}</span></h1>
</header>

{include file='userNotice'}

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller='IgnoredUsers' link="pageNo=%d"}
	
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

{if $objects|count}
	<div class="container marginTop">
		<ol class="containerList doubleColumned userList">
			{foreach from=$objects item=user}
				<li class="jsIgnoredUser">
					<div class="box48">
						<a href="{link controller='User' object=$user}{/link}" title="{$user->username}" class="framed">{@$user->getAvatar()->getImageTag(48)}</a>
							
						<div class="details userInformation">
							{include file='userInformationHeadline'}
							
							<nav class="jsMobileNavigation buttonGroupNavigation">
								<ul class="buttonList iconList jsOnly">
									<li><span class="icon icon16 icon-remove pointer jsTooltip jsDeleteButton" title="{lang}wcf.user.button.unignore{/lang}" data-object-id="{@$user->ignoreID}"></span></li>
									{event name='userButtons'}
								</ul>
							</nav>
							
							{include file='userInformationStatistics'}
						</div>
					</div>
				</li>
			{/foreach}
		</ol>
	</div>
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
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
{else}
	<p class="info">{lang}wcf.user.ignoredUsers.noUsers{/lang}</p>
{/if}

{include file='footer'}

</body>
</html>
