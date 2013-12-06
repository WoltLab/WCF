{if !$__messageSidebarJavascript|isset}
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			WCF.Language.addObject({
				'wcf.user.activityPoint': '{lang}wcf.user.activityPoint{/lang}'
			});
			
			WCF.User.Profile.ActivityPointList.init();
		});
		//]]>
	</script>
	{assign var=__messageSidebarJavascript value=true}
{/if}

<aside class="messageSidebar{if MESSAGE_SIDEBAR_ENABLE_ONLINE_STATUS && $userProfile->isOnline()} userOnline{/if} {if $userProfile->userID}member{else}guest{/if}"{if $userProfile->userID} itemscope="itemscope" itemtype="http://data-vocabulary.org/Person"{/if}>
	<div>
		{if $userProfile->userID}
			{assign var='username' value=$userProfile->username}
			
			<header>
				<h2 class="username">
					<a href="{link controller='User' object=$userProfile->getDecoratedObject()}{/link}" class="userLink" data-user-id="{@$userProfile->userID}" rel="author">
						<span itemprop="name">{$username}</span>
					</a>
				</h2>
				
				{event name='header'}
			</header>
			
			{if MESSAGE_SIDEBAR_ENABLE_AVATAR}
				{if $userProfile->getAvatar()}
					<div class="userAvatar">
						{capture assign='__userAvatar'}{@$userProfile->getAvatar()->getImageTag(128)}{/capture}
						<a href="{link controller='User' object=$userProfile->getDecoratedObject()}{/link}" class="framed">{@'<img'|str_replace:'<img itemprop="photo"':$__userAvatar}</a>
						
						{if MESSAGE_SIDEBAR_ENABLE_ONLINE_STATUS && $userProfile->isOnline()}<span class="badge green badgeOnline" title="{lang}wcf.user.online.title{/lang}">{lang}wcf.user.online{/lang}</span>{/if}
					</div>
				{/if}
			{/if}
			
			{if MODULE_USER_RANK && MESSAGE_SIDEBAR_ENABLE_RANK}
				{if $userProfile->getUserTitle()}
					<div class="userTitle">
						<p class="badge userTitleBadge{if $userProfile->getRank() && $userProfile->getRank()->cssClassName} {@$userProfile->getRank()->cssClassName}{/if}" itemprop="title">{$userProfile->getUserTitle()}</p>
					</div>
				{/if}
				{if $userProfile->getRank() && $userProfile->getRank()->rankImage}
					<div class="userRank">{@$userProfile->getRank()->getImage()}</div>
				{/if}
			{/if}
		{else}
			<header>
				<h2 class="username">
					<span>{@$userProfile->username}</span>
				</h2>
				
				<div class="userTitle">
					<p class="badge">{lang}wcf.user.guest{/lang}</p>
				</div>
				
				{event name='header'}
			</header>
		{/if}
		
		{event name='beforeCredits'}
		
		{if $userProfile->userID}
			{hascontent}
				<div class="userCredits">
					<dl class="plain dataList">
						{content}
							{if MODULE_LIKE && MESSAGE_SIDEBAR_ENABLE_LIKES_RECEIVED && $userProfile->likesReceived}
								<dt>{lang}wcf.like.likesReceived{/lang}</dt>
								<dd>{#$userProfile->likesReceived}</dd>
							{/if}
							
							{if MESSAGE_SIDEBAR_ENABLE_ACTIVITY_POINTS && $userProfile->activityPoints}
								<dt><a class="activityPointsDisplay jsTooltip" title="{lang}wcf.user.activityPoint.showDetails{/lang}" data-user-id="{@$userProfile->userID}">{lang}wcf.user.activityPoint{/lang}</a></dt>
								<dd>{#$userProfile->activityPoints}</dd>
							{/if}
							{event name='userCredits'}
							{if MESSAGE_SIDEBAR_USER_OPTIONS && $userProfile->isAccessible('canViewProfile')}
								{assign var='__sidebarUserOptions' value=','|explode:MESSAGE_SIDEBAR_USER_OPTIONS}
								{foreach from=$__sidebarUserOptions item='__sidebarUserOption'}
									{if $userProfile->getUserOption($__sidebarUserOption)}
										{assign var='__formattedUserOption' value=$userProfile->getFormattedUserOption($__sidebarUserOption)}
										{if $__formattedUserOption}
											<dt>{lang}wcf.user.option.{$__sidebarUserOption}{/lang}</dt>
											<dd{if $__sidebarUserOption == 'location'} itemprop="locality"{/if}>{@$__formattedUserOption}</dd>
										{/if}
									{/if}
								{/foreach}
							{/if}
						{/content}
					</dl>
				</div>
			{/hascontent}
		{/if}
		
		{event name='afterCredits'}
	</div>
</aside>
