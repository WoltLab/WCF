{capture assign='pageTitle'}{$__wcf->getActivePage()->getTitle()}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='headContent'}
	<link rel="canonical" href="{link controller='UsersOnlineList'}{if $pageNo > 1}pageNo={@$pageNo}{/if}{/link}">
	
	{if USERS_ONLINE_PAGE_REFRESH > 0}
		<meta http-equiv="refresh" content="{@USERS_ONLINE_PAGE_REFRESH}; url={link controller='UsersOnlineList'}{if $pageNo > 1}pageNo={@$pageNo}&{/if}sortField={@$sortField}&sortOrder={@$sortOrder}{/link}">
	{/if}
{/capture}

{capture assign='sidebarRight'}
	<section class="box" data-static-box-identifier="com.woltlab.wcf.UsersOnlineListSorting">
		<form method="post" action="{link controller='UsersOnlineList'}{/link}">
			<h2 class="boxTitle">{lang}wcf.user.members.sort{/lang}</h2>
				
			<div class="boxContent">
				<dl>
					<dt></dt>
					<dd>
						<select id="sortField" name="sortField">
							<option value="username"{if $sortField == 'username'} selected{/if}>{lang}wcf.user.username{/lang}</option>
							<option value="lastActivityTime"{if $sortField == 'lastActivityTime'} selected{/if}>{lang}wcf.user.usersOnline.lastActivity{/lang}</option>
							<option value="requestURI"{if $sortField == 'requestURI'} selected{/if}>{lang}wcf.user.usersOnline.location{/lang}</option>
							
							{if $__wcf->session->getPermission('admin.user.canViewIpAddress')}
								<option value="ipAddress"{if $sortField == 'ipAddress'} selected{/if}>{lang}wcf.user.usersOnline.ipAddress{/lang}</option>
								<option value="userAgent"{if $sortField == 'userAgent'} selected{/if}>{lang}wcf.user.usersOnline.userAgent{/lang}</option>
							{/if}
						</select>
						<select name="sortOrder">
							<option value="ASC"{if $sortOrder == 'ASC'} selected{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
							<option value="DESC"{if $sortOrder == 'DESC'} selected{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
						</select>
					</dd>
				</dl>
			
				<div class="formSubmit">
					<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
				</div>
			</div>
		</form>
	</section>
	
	<section class="box" data-static-box-identifier="com.woltlab.wcf.UsersOnlineListInfo">
		<h2 class="boxTitle">{lang}wcf.user.usersOnline{/lang}</h2>
		
		<div class="boxContent">
			<p>{lang usersOnlineList=$objects}wcf.user.usersOnline.detail{/lang}</p>
			{if USERS_ONLINE_RECORD}<p>{lang}wcf.user.usersOnline.record{/lang}</p>{/if}
		</div>
		
		{if USERS_ONLINE_ENABLE_LEGEND && $objects->getUsersOnlineMarkings()|count}
			<div class="boxContent">
				<dl class="plain inlineDataList usersOnlineLegend">
					<dt>{lang}wcf.user.usersOnline.marking.legend{/lang}</dt>
					<dd>
						<ul class="inlineList commaSeparated">
							{foreach from=$objects->getUsersOnlineMarkings() item=usersOnlineMarking}
								<li>{@$usersOnlineMarking}</li>
							{/foreach}
						</ul>
					</dd>
				
				</dl>
			</div>
		{/if}
	</section>
{/capture}

{include file='header'}

{hascontent}
	<div class="paginationTop">
		{content}
			{pages print=true assign=pagesLinks controller='UsersOnlineList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
		{/content}
	</div>
{/hascontent}

{assign var=usersOnlineList value=''}
{assign var=usersOnline value=0}
{assign var=robotsOnlineList value=''}
{assign var=robotsOnline value=0}
{assign var=guestsOnlineList value=''}
{assign var=guestsOnline value=0}
{foreach from=$objects item=user}
	{capture assign=locationData}
		<p>
			{if $user->getLocation()}{@$user->getLocation()}{else}{lang}wcf.user.usersOnline.location.unknown{/lang}{/if} <small class="separatorLeft">{@$user->lastActivityTime|time}</small>
		</p>
	{/capture}
	
	{capture assign=sessionData}
		{if $__wcf->session->getPermission('admin.user.canViewIpAddress')}
			<dl class="plain inlineDataList small">
				<dt>{lang}wcf.user.usersOnline.ipAddress{/lang}</dt>
				<dd title="{$user->getFormattedIPAddress()}">{@$user->getFormattedIPAddress()|ipSearch}</dd>
				
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
					{user object=$user type='avatar48' title=$user->username ariaHidden='true'}
					
					<div class="details userInformation">
						<div class="containerHeadline">
							<h3>{user object=$user}
								{if MODULE_USER_RANK}
									{if $user->getUserTitle()}
										<span class="badge userTitleBadge{if $user->getRank() && $user->getRank()->cssClassName} {@$user->getRank()->cssClassName}{/if}">{$user->getUserTitle()}</span>
									{/if}
									{if $user->getRank() && $user->getRank()->rankImage}
										<span class="userRankImage">{@$user->getRank()->getImage()}</span>
									{/if}
								{/if}
							</h3>
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
					<div><img src="{$__wcf->getPath()}images/avatars/avatar-spider-default.svg" alt="" class="userAvatarImage icon48"></div>
					
					<div class="details userInformation">
						<div class="containerHeadline">
							<h3>{if $user->getSpider()->spiderURL}<a {anchorAttributes url=$user->getSpider()->spiderURL}>{$user->getSpider()->spiderName}</a>{else}{$user->getSpider()->spiderName}{/if}</h3>
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
					<div><img src="{$__wcf->getPath()}images/avatars/avatar-default.svg" alt="" class="userAvatarImage icon48"></div>
					
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

{if $usersOnline}
	<section class="section sectionContainerList">
		<h2 class="sectionTitle">{lang}wcf.user.usersOnline.users{/lang}</h2>
		
		<ol class="containerList userList">
			{@$usersOnlineList}
		</ol>
	</section>
{/if}

{if $guestsOnline && USERS_ONLINE_SHOW_GUESTS}
	<section class="section sectionContainerList">
		<h2 class="sectionTitle">{lang}wcf.user.usersOnline.guests{/lang}</h2>
		
		<ol class="containerList userList">
			{@$guestsOnlineList}
		</ol>
	</section>
{/if}

{if $robotsOnline && USERS_ONLINE_SHOW_ROBOTS}
	<section class="section sectionContainerList">
		<h2 class="sectionTitle">{lang}wcf.user.usersOnline.robots{/lang}</h2>
		
		<ol class="containerList userList">
			{@$robotsOnlineList}
		</ol>
	</section>
{/if}

<footer class="contentFooter">
	{hascontent}
		<div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
		</div>
	{/hascontent}
	
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
			'wcf.user.button.follow': '{lang}wcf.user.button.follow{/lang}',
			'wcf.user.button.ignore': '{lang}wcf.user.button.ignore{/lang}',
			'wcf.user.button.unfollow': '{lang}wcf.user.button.unfollow{/lang}',
			'wcf.user.button.unignore': '{lang}wcf.user.button.unignore{/lang}'
		});
		
		new WCF.User.Action.Follow($('.userList > li'));
		new WCF.User.Action.Ignore($('.userList > li'));
	});
</script>

{include file='footer'}
