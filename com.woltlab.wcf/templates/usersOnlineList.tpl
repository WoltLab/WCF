{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.usersOnline{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
	
	<link rel="canonical" href="{link controller='UsersOnlineList'}{/link}" />
	
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
	
	{if USERS_ONLINE_PAGE_REFRESH > 0}
		<meta http-equiv="refresh" content="{@USERS_ONLINE_PAGE_REFRESH}; url={link controller='UsersOnlineList'}sortField={@$sortField}&sortOrder={@$sortOrder}{/link}" />
	{/if}
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{capture assign='sidebar'}
	<div>
		<form method="post" action="{link controller='UsersOnlineList'}{/link}">
			<fieldset>
				<legend><label for="sortField">{lang}wcf.user.members.sort{/lang}</label></legend>
				
				<dl>
					<dt></dt>
					<dd>
						<select id="sortField" name="sortField">
							<option value="username"{if $sortField == 'username'} selected="selected"{/if}>{lang}wcf.user.username{/lang}</option>
							<option value="lastActivityTime"{if $sortField == 'lastActivityTime'} selected="selected"{/if}>{lang}wcf.user.usersOnline.lastActivity{/lang}</option>
							<option value="requestURI"{if $sortField == 'requestURI'} selected="selected"{/if}>{lang}wcf.user.usersOnline.location{/lang}</option>
							
							{if $__wcf->session->getPermission('admin.user.canViewIpAddress')}
								<option value="ipAddress"{if $sortField == 'ipAddress'} selected="selected"{/if}>{lang}wcf.user.usersOnline.ipAddress{/lang}</option>
								<option value="userAgent"{if $sortField == 'userAgent'} selected="selected"{/if}>{lang}wcf.user.usersOnline.userAgent{/lang}</option>
							{/if}
						</select>
						<select name="sortOrder">
							<option value="ASC"{if $sortOrder == 'ASC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
							<option value="DESC"{if $sortOrder == 'DESC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
						</select>
					</dd>
				</dl>
			</fieldset>
			
			<div class="formSubmit">
				<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
				{@SID_INPUT_TAG}
			</div>
		</form>
	</div>
	
	<fieldset>
		<legend>{lang}wcf.user.usersOnline{/lang}</legend>
		
		<p><small>{lang usersOnlineList=$objects}wcf.user.usersOnline.detail{/lang}</small></p>
		{if USERS_ONLINE_RECORD}<p><small>{lang}wcf.user.usersOnline.record{/lang}</small></p>{/if}
		
		{if USERS_ONLINE_ENABLE_LEGEND && $objects->getUsersOnlineMarkings()|count}
			<div class="marginTopSmall">
				<p><small>{lang}wcf.user.usersOnline.marking.legend{/lang}:</small></p>
				<ul class="dataList">
					{foreach from=$objects->getUsersOnlineMarkings() item=usersOnlineMarking}
						<li><small>{@$usersOnlineMarking}</small></li>
					{/foreach}
				</ul>
			</div>
		{/if}
	</fieldset>
	
	{@$__boxSidebar}
{/capture}

{include file='header' sidebarOrientation='right'}

{include file='userNotice'}

{assign var=usersOnlineList value=''}
{assign var=usersOnline value=0}
{assign var=robotsOnlineList value=''}
{assign var=robotsOnline value=0}
{assign var=guestsOnlineList value=''}
{assign var=guestsOnline value=0}
{foreach from=$objects item=user}
	{capture assign=locationData}
		<p>
			{if $user->getLocation()}{@$user->getLocation()}{else}{lang}wcf.user.usersOnline.location.unknown{/lang}{/if} <small>- {@$user->lastActivityTime|time}</small>
		</p>
	{/capture}
	
	{capture assign=sessionData}
		{if $__wcf->session->getPermission('admin.user.canViewIpAddress')}
			<dl class="plain inlineDataList">
				<dt>{lang}wcf.user.usersOnline.ipAddress{/lang}</dt>
				<dd title="{$user->getFormattedIPAddress()}">{$user->getFormattedIPAddress()|truncate:30}</dd>
				
				{if !$user->spiderID}
					<dt>{lang}wcf.user.usersOnline.userAgent{/lang}</dt>
					<dd title="{$user->userAgent}">{$user->getBrowser()|truncate:30}</dd>
				{/if}
			</dl>
		{/if}
	{/capture}
	
	{if $user->userID}
		{* member *}
		{capture append=usersOnlineList}
			<li>
				<div class="box48">
					<a href="{link controller='User' object=$user}{/link}" title="{$user->username}" class="framed">{@$user->getAvatar()->getImageTag(48)}</a>
					
					<div class="details userInformation">
						<div class="containerHeadline">
							<h3><a href="{link controller='User' object=$user}{/link}">{@$user->getFormattedUsername()}</a>{if MODULE_USER_RANK}
								{if $user->getUserTitle()}
									<span class="badge userTitleBadge{if $user->getRank() && $user->getRank()->cssClassName} {@$user->getRank()->cssClassName}{/if}">{$user->getUserTitle()}</span>
								{/if}
								{if $user->getRank() && $user->getRank()->rankImage}
									<span class="userRankImage">{@$user->getRank()->getImage()}</span>
								{/if}
							{/if}</h3>
							
							{@$locationData}
						</div>
						
						{@$sessionData}
						
						{include file='userInformationButtons'}
					</div>
				</div>
			</li>
		{/capture}
		
		{assign var=usersOnline value=$usersOnline+1}
	{elseif $user->spiderID}
		{* search robot *}
		{capture append=robotsOnlineList}
			<li>
				<div class="box48">
					<p class="framed"><img src="{$__wcf->getPath()}images/avatars/avatar-spider-default.svg" alt="" class="icon48" /></p>
					
					<div class="details userInformation">
						<div class="containerHeadline">
							<h3>{if $user->getSpider()->spiderURL}<a href="{$user->getSpider()->spiderURL}" class="externalURL"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}{if EXTERNAL_LINK_REL_NOFOLLOW} rel="nofollow"{/if}>{$user->getSpider()->spiderName}</a>{else}{$user->getSpider()->spiderName}{/if}</h3>
							{@$locationData}
						</div>
						
						{@$sessionData}
					</div>
				</div>
			</li>
		{/capture}
		
		{assign var=robotsOnline value=$robotsOnline+1}
	{else}
		{* unregistered *}
		{capture append=guestsOnlineList}
			<li>
				<div class="box48">
					<p class="framed"><img src="{$__wcf->getPath()}images/avatars/avatar-default.svg" alt="" class="icon48" /></p>
					
					<div class="details userInformation">
						<div class="containerHeadline">
							<h3>{lang}wcf.user.guest{/lang}</h3>
							{@$locationData}
						</div>
						
						{@$sessionData}
					</div>
				</div>
			</li>
		{/capture}
		
		{assign var=guestsOnline value=$guestsOnline+1}
	{/if}
{/foreach}

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

{if $usersOnline}
	<header class="boxHeadline">
		<h1>{lang}wcf.user.usersOnline{/lang} <span class="badge">{#$usersOnline}</span></h1>
	</header>
	
	<div class="container marginTop">
		<ol class="containerList userList">
			{@$usersOnlineList}
		</ol>
	</div>
{/if}

{if $guestsOnline && USERS_ONLINE_SHOW_GUESTS}
	<header class="boxHeadline">
		<h1>{lang}wcf.user.usersOnline.guests{/lang} <span class="badge">{#$guestsOnline}</span></h1>
	</header>
	
	<div class="container marginTop">
		<ol class="containerList">
			{@$guestsOnlineList}
		</ol>
	</div>
{/if}

{if $robotsOnline && USERS_ONLINE_SHOW_ROBOTS}
	<header class="boxHeadline">
		<h1>{lang}wcf.user.usersOnline.robots{/lang} <span class="badge">{#$robotsOnline}</span></h1>
	</header>
	
	<div class="container marginTop">
		<ol class="containerList">
			{@$robotsOnlineList}
		</ol>
	</div>
{/if}

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
