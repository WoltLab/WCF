{include file='header' pageTitle='wcf.acp.user.authentication.failure.list'}

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.user.authentication.failure.list{/lang}</h1>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller='UserAuthenticationFailureList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
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
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnFailureID{if $sortField == 'failureID'} active {@$sortOrder}{/if}"><a href="{link controller='UserAuthenticationFailureList'}pageNo={@$pageNo}&sortField=failureID&sortOrder={if $sortField == 'failureID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnEnvironment{if $sortField == 'environment'} active {@$sortOrder}{/if}"><a href="{link controller='UserAuthenticationFailureList'}pageNo={@$pageNo}&sortField=environment&sortOrder={if $sortField == 'environment' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.user.authentication.failure.environment{/lang}</a></th>
					<th class="columnTitle columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}"><a href="{link controller='UserAuthenticationFailureList'}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.username{/lang}</a></th>
					<th class="columnDate columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link controller='UserAuthenticationFailureList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.user.authentication.failure.time{/lang}</a></th>
					<th class="columnURL columnIpAddress{if $sortField == 'ipAddress'} active {@$sortOrder}{/if}"><a href="{link controller='UserAuthenticationFailureList'}pageNo={@$pageNo}&sortField=ipAddress&sortOrder={if $sortField == 'ipAddress' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.ipAddress{/lang}</a></th>
					<th class="columnText columnUserAgent{if $sortField == 'userAgent'} active {@$sortOrder}{/if}"><a href="{link controller='UserAuthenticationFailureList'}pageNo={@$pageNo}&sortField=userAgent&sortOrder={if $sortField == 'userAgent' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.userAgent{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item='authenticationFailure'}
					<tr>
						<td class="columnID columnFailureID">{@$authenticationFailure->failureID}</td>
						<td class="columnText columnEnvironment">{lang}wcf.acp.user.authentication.failure.environment.{@$authenticationFailure->environment}{/lang}</td>
						<td class="columnTitle columnUsername">{if $authenticationFailure->userID}<a href="{link controller='UserEdit' id=$authenticationFailure->userID}{/link}">{$authenticationFailure->username}</a>{else}{$authenticationFailure->username}{/if}</td>
						<td class="columnDate columnTime">{@$authenticationFailure->time|time}</td>
						<td class="columnSmallText columnIpAddress">{$authenticationFailure->getIpAddress()}</td>
						<td class="columnSmallText columnUserAgent" title="{$authenticationFailure->userAgent}">{$authenticationFailure->userAgent|truncate:75|tableWordwrap}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
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
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
