{if !$isReply|isset}
	{assign var=isReply value=false} 
{/if}
{if !$enableMicrodata|isset}
	{assign var=enableMicrodata value=false}
{/if}
{if !$__messageSidebarJavascript|isset}
	{assign var=__messageSidebarJavascript value=true}
{/if}

<aside role="presentation" class="messageSidebar{if MESSAGE_SIDEBAR_ENABLE_ONLINE_STATUS && !$isReply && $userProfile->isOnline()} userOnline{/if} {if $userProfile->userID}member{else}guest{/if}"{if $enableMicrodata} itemprop="author" itemscope itemtype="http://schema.org/Person"{/if}>
	<div class="messageAuthor">
		{event name='messageAuthor'}
		
		{if $userProfile->userID}
			{assign var='username' value=$userProfile->username}
			
			{if $userProfile->getAvatar()}
				<div class="userAvatar">
					<a href="{link controller='User' object=$userProfile->getDecoratedObject()}{/link}" aria-hidden="true">{@$userProfile->getAvatar()->getImageTag(128)}</a>
					
					{if MESSAGE_SIDEBAR_ENABLE_ONLINE_STATUS && !$isReply && $userProfile->isOnline()}<span class="badge green badgeOnline" title="{lang}wcf.user.online.title{/lang}">{lang}wcf.user.online{/lang}</span>{/if}
				</div>
			{/if}
			
			<div class="messageAuthorContainer">
				<a href="{link controller='User' object=$userProfile->getDecoratedObject()}{/link}" class="username userLink" data-user-id="{@$userProfile->userID}"{if $enableMicrodata} itemprop="url"{/if}>
					<span{if $enableMicrodata} itemprop="name"{/if}>{if MESSAGE_SIDEBAR_ENABLE_USER_ONLINE_MARKING}{@$userProfile->getFormattedUsername()}{else}{$username}{/if}</span>
				</a>
				{if !$isReply}
					{if $userProfile->banned}<span class="icon icon16 fa-lock jsTooltip jsUserBanned" title="{lang user=$userProfile}wcf.user.banned{/lang}"></span>{/if}
					
					{event name='messageAuthorContainer'}
				{/if}
			</div>
			
			{if MODULE_USER_RANK && !$isReply}
				{if $userProfile->getUserTitle()}
					<div class="userTitle">
						<span class="badge userTitleBadge{if $userProfile->getRank() && $userProfile->getRank()->cssClassName} {@$userProfile->getRank()->cssClassName}{/if}">{$userProfile->getUserTitle()}</span>
					</div>
				{/if}
				{if $userProfile->getRank() && $userProfile->getRank()->rankImage}
					<div class="userRank">{@$userProfile->getRank()->getImage()}</div>
				{/if}
			{/if}

			{if !$isReply && MODULE_TROPHY && $__wcf->session->getPermission('user.profile.trophy.canSeeTrophies') && ($userProfile->isAccessible('canViewTrophies') || $userProfile->userID == $__wcf->session->userID) && $userProfile->getSpecialTrophies()|count}
				<div class="specialTrophyContainer">
					<ul>
						{foreach from=$userProfile->getSpecialTrophies() item=trophy}
							<li><a href="{@$trophy->getLink()}">{@$trophy->renderTrophy(32, true)}</a></li>
						{/foreach}
					</ul>
				</div>
			{/if}
		{else}
			<div class="userAvatar">
				<span>{@$userProfile->getAvatar()->getImageTag(128)}</span>
			</div>
			
			<div class="messageAuthorContainer">
				{if $userProfile->username}
					<span class="username"{if $enableMicrodata} itemprop="name"{/if}>{$userProfile->username}</span>
				{/if}
				
				{event name='messageAuthorContainer'}
			</div>
			
			<div class="userTitle">
				<span class="badge">{lang}wcf.user.guest{/lang}</span>
			</div>
		{/if}
	</div>
	
	{if !$isReply}
		{event name='beforeCredits'}
		
		{if $userProfile->userID}
			{hascontent}
				<div class="userCredits">
					<dl class="plain dataList">
						{content}
							{if MODULE_LIKE && MESSAGE_SIDEBAR_ENABLE_LIKES_RECEIVED && !$isReply}
								<dt><a href="{link controller='User' object=$userProfile}{/link}#likes" class="jsTooltip" title="{lang user=$userProfile}wcf.like.showLikesReceived{/lang}">{lang}wcf.like.likesReceived{/lang}</a></dt>
								<dd>{#$userProfile->cumulativeLikes}</dd>
							{/if}
							
							{if MESSAGE_SIDEBAR_ENABLE_ACTIVITY_POINTS && $userProfile->activityPoints}
								<dt><a href="#" class="activityPointsDisplay jsTooltip" title="{lang user=$userProfile}wcf.user.activityPoint.showActivityPoints{/lang}" data-user-id="{@$userProfile->userID}">{lang}wcf.user.activityPoint{/lang}</a></dt>
								<dd>{#$userProfile->activityPoints}</dd>
							{/if}
							
							{if MODULE_TROPHY && MESSAGE_SIDEBAR_ENABLE_TROPHY_POINTS && $userProfile->trophyPoints && $__wcf->session->getPermission('user.profile.trophy.canSeeTrophies') && ($userProfile->isAccessible('canViewTrophies') || $userProfile->userID == $__wcf->session->userID)}
								<dt><a href="#" class="trophyPoints jsTooltip userTrophyOverlayList" data-user-id="{$userProfile->userID}" title="{lang user=$userProfile}wcf.user.trophy.showTrophies{/lang}">{lang}wcf.user.trophy.trophyPoints{/lang}</a></dt>
								<dd>{#$userProfile->trophyPoints}</dd>
							{/if}
							
							{if MESSAGE_SIDEBAR_ENABLE_ARTICLES && $userProfile->articles}
								<dt><a href="{link controller='ArticleList' userID=$userProfile->userID}{/link}" class="jsTooltip" title="{lang user=$userProfile}wcf.article.showArticlesWritten{/lang}">{lang}wcf.user.articles{/lang}</a></dt>
								<dd>{#$userProfile->articles}</dd>
							{/if}
							
							{event name='userCredits'}
							
							{if MESSAGE_SIDEBAR_USER_OPTIONS && $userProfile->isAccessible('canViewProfile')}
								{assign var='__sidebarUserOptions' value=','|explode:MESSAGE_SIDEBAR_USER_OPTIONS}
								{foreach from=$__sidebarUserOptions item='__sidebarUserOption'}
									{if $userProfile->getUserOption($__sidebarUserOption)}
										{assign var='__formattedUserOption' value=$userProfile->getFormattedUserOption($__sidebarUserOption)}
										{if $__formattedUserOption}
											<dt>{lang}wcf.user.option.{$__sidebarUserOption}{/lang}</dt>
											<dd>{@$__formattedUserOption}</dd>
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
	{/if}
</aside>
