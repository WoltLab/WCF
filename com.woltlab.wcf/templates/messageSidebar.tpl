{if !$__messageSidebarJavascript|isset}
	{assign var=__messageSidebarJavascript value=true}
{/if}

<aside class="messageSidebar{if MESSAGE_SIDEBAR_ENABLE_ONLINE_STATUS && $userProfile->isOnline()} userOnline{/if} {if $userProfile->userID}member{else}guest{/if}"{if $userProfile->userID} itemscope itemtype="http://data-vocabulary.org/Person"{/if}>
	<div class="messageAuthor">
		{event name='messageAuthor'}
		
		{if $userProfile->userID}
			{assign var='username' value=$userProfile->username}
			
			{if $userProfile->getAvatar()}
				<div class="userAvatar">
					{capture assign='__userAvatar'}{@$userProfile->getAvatar()->getImageTag(128)}{/capture}
					<a href="{link controller='User' object=$userProfile->getDecoratedObject()}{/link}">{@'<img'|str_replace:'<img itemprop="photo"':$__userAvatar}</a>
					
					{if MESSAGE_SIDEBAR_ENABLE_ONLINE_STATUS && $userProfile->isOnline()}<span class="badge green badgeOnline" title="{lang}wcf.user.online.title{/lang}">{lang}wcf.user.online{/lang}</span>{/if}
				</div>
			{/if}
			
			<div class="messageAuthorContainer">
				<a href="{link controller='User' object=$userProfile->getDecoratedObject()}{/link}" class="username userLink" data-user-id="{@$userProfile->userID}" rel="author">
					<span itemprop="name">{if MESSAGE_SIDEBAR_ENABLE_USER_ONLINE_MARKING}{@$userProfile->getFormattedUsername()}{else}{$username}{/if}</span>
				</a>
				{if $userProfile->banned}<span class="icon icon16 fa-lock jsTooltip jsUserBanned" title="{lang user=$userProfile}wcf.user.banned{/lang}"></span>{/if}
				
				{event name='messageAuthorContainer'}
			</div>
			
			{if MODULE_USER_RANK}
				{if $userProfile->getUserTitle()}
					<div class="userTitle">
						<span class="badge userTitleBadge{if $userProfile->getRank() && $userProfile->getRank()->cssClassName} {@$userProfile->getRank()->cssClassName}{/if}" itemprop="title">{$userProfile->getUserTitle()}</span>
					</div>
				{/if}
				{if $userProfile->getRank() && $userProfile->getRank()->rankImage}
					<div class="userRank">{@$userProfile->getRank()->getImage()}</div>
				{/if}
			{/if}
		{else}
			<div class="messageAuthorContainer">
				<span class="username">{$userProfile->username}</span>
				
				{event name='messageAuthorContainer'}
			</div>
			
			<div class="userTitle">
				<span class="badge">{lang}wcf.user.guest{/lang}</span>
			</div>
		{/if}
	</div>
	
	{event name='beforeCredits'}
	
	{if $userProfile->userID}
		{hascontent}
			<div class="userCredits">
				<dl class="plain dataList">
					{content}
						{if MODULE_LIKE && MESSAGE_SIDEBAR_ENABLE_LIKES_RECEIVED && $userProfile->likesReceived}
							<dt><a href="{link controller='User' object=$userProfile}{/link}#likes" class="jsTooltip" title="{lang user=$userProfile}wcf.like.showLikesReceived{/lang}">{lang}wcf.like.likesReceived{/lang}</a></dt>
							<dd>{#$userProfile->likesReceived}</dd>
						{/if}
						
						{if MESSAGE_SIDEBAR_ENABLE_ACTIVITY_POINTS && $userProfile->activityPoints}
							<dt><a href="#" class="activityPointsDisplay jsTooltip" title="{lang user=$userProfile}wcf.user.activityPoint.showActivityPoints{/lang}" data-user-id="{@$userProfile->userID}">{lang}wcf.user.activityPoint{/lang}</a></dt>
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
</aside>
