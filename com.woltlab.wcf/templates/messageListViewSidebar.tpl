{if !$isReply|isset}
	{assign var='isReply' value=false}
{/if}
{if !$enableMicrodata|isset}
	{assign var='enableMicrodata' value=false}
{/if}
{if !$isContentAuthor|isset}
	{assign var='isContentAuthor' value=false}
{/if}
{if !$contentAuthorTitlePhrase|isset}
	{assign var='contentAuthorTitlePhrase' value='wcf.comment.objectAuthor'}
{/if}

<aside class="messageListView__item__sidebar">
	<div class="messageListView__item__author"{if $enableMicrodata} itemprop="author" itemscope itemtype="http://schema.org/Person"{/if}>
		{if $enableMicrodata}
			<meta itemprop="name" content="{$userProfile->username}">
			{if $userProfile->userID}
				<meta itemprop="url" content="{$userProfile->getLink()}">
			{/if}
		{/if}

		<div class="messageListView__item__avatar">
			{user object=$userProfile type='avatar96' ariaHidden='true' tabindex='-1'}

			{if !$isReply}
				{if MESSAGE_SIDEBAR_ENABLE_ONLINE_STATUS && $userProfile->isOnline()}
					<span class="messageListView__item__onlineIndicator jsTooltip" role="img" title="{lang username=$userProfile->username}wcf.user.online.title{/lang}" aria-label="{lang username=$userProfile->username}wcf.user.online.title{/lang}"></span>
				{/if}

				{if $isContentAuthor}
					<span class="messageListView__item__contentAuthorIcon jsTooltip" role="img" title="{lang}{$contentAuthorTitlePhrase}{/lang}" aria-label="{lang}{$contentAuthorTitlePhrase}{/lang}">{icon name='feather-pointed' type='solid'}</span>
				{/if}
			{/if}
		</div>

		{if !$isReply || !$userProfile->isGuest()}
			<div class="messageListView__item__username">
				{user object=$userProfile}
				{if !$isReply && $userProfile->banned}
					<span class="messageListView__item__banned jsTooltip" role="img" title="{lang user=$userProfile}wcf.user.banned{/lang}" aria-label="{lang user=$userProfile}wcf.user.banned{/lang}">{icon name='lock'}</span>
				{/if}
			</div>
		{/if}

		{if !$isReply}
			{if $isContentAuthor}
				<span class="messageListView__item__contentAuthorInline jsTooltip" role="img" title="{lang}{$contentAuthorTitlePhrase}{/lang}" aria-label="{lang}{$contentAuthorTitlePhrase}{/lang}">{icon name='feather-pointed' type='solid'}</span>
			{/if}

			{hascontent}
				<div class="messageListView__item__userBadges">
					{content}
						{if $userProfile->isGuest()}
							<span class="badge">{lang}wcf.user.guest{/lang}</span>
						{elseif MODULE_USER_RANK && $userProfile->getUserTitle()}
							{unsafe:$userProfile->renderUserTitle()}
						{/if}

						{event name='badges'}
					{/content}
				</div>
			{/hascontent}

			{if MODULE_USER_RANK && $userProfile->getRank() && $userProfile->getRank()->rankImageFileID !== null}
				<div class="messageListView__item__rankImage">{unsafe:$userProfile->getRank()->getImage()}</div>
			{/if}

			{if !$userProfile->isGuest()}
				{if $userProfile->showSpecialTrophies()}
					<ul class="messageListView__item__specialTrophies">
						{foreach from=$userProfile->getSpecialTrophies() item=trophy}
							<li><a href="{$trophy->getLink()}">{unsafe:$trophy->renderTrophy(24, true)}</a></li>
						{/foreach}
					</ul>
				{/if}

				{hascontent}
					<div class="messageListView__item__userStats">
						{content}
							{if MODULE_LIKE && MESSAGE_SIDEBAR_ENABLE_LIKES_RECEIVED && $userProfile->likesReceived}
								<a href="{$userProfile->getLink()}#likes" class="messageListView__item__stat">
									<span class="messageListView__item__stat__key">{lang}wcf.user.reactionsReceived{/lang}</span>
									<span class="messageListView__item__stat__value">{#$userProfile->likesReceived}</span>
								</a>
							{/if}

							{if MESSAGE_SIDEBAR_ENABLE_ACTIVITY_POINTS && $userProfile->activityPoints}
								<button type="button" class="messageListView__item__stat activityPointsDisplay" data-user-id="{$userProfile->userID}">
									<span class="messageListView__item__stat__key">{lang}wcf.user.activityPoint{/lang}</span>
									<span class="messageListView__item__stat__value">{#$userProfile->activityPoints}</span>
								</button>
							{/if}

							{if MODULE_TROPHY && MESSAGE_SIDEBAR_ENABLE_TROPHY_POINTS && $userProfile->showTrophyPoints()}
								<button type="button" class="messageListView__item__stat userTrophyOverlayList" data-user-id="{$userProfile->userID}">
									<span class="messageListView__item__stat__key">{lang}wcf.user.trophy.trophyPoints{/lang}</span>
									<span class="messageListView__item__stat__value">{#$userProfile->trophyPoints}</span>
								</button>
							{/if}

							{event name='userStats'}
						{/content}
					</div>
				{/hascontent}

				{if MESSAGE_SIDEBAR_USER_OPTIONS && $userProfile->isAccessible('canViewProfile')}
					{hascontent}
						<div class="messageListView__item__userOptions">
							{content}
								{assign var='__sidebarUserOptions' value=','|explode:MESSAGE_SIDEBAR_USER_OPTIONS}
								{foreach from=$__sidebarUserOptions item='__sidebarUserOption'}
									{if $userProfile->getUserOption($__sidebarUserOption)}
										{assign var='__formattedUserOption' value=$userProfile->getFormattedUserOption($__sidebarUserOption)}
										{if $__formattedUserOption}
											<div class="messageListView__item__userOption">
												<span class="messageListView__item__userOption__key">{$userProfile->getUserOptionTitle($__sidebarUserOption)}</span>
												<span class="messageListView__item__userOption__value">{unsafe:$__formattedUserOption}</span>
											</div>
										{/if}
									{/if}
								{/foreach}
							{/content}
						</div>
					{/hascontent}
				{/if}
			{/if}

			{event name='sidebar'}
		{/if}
	</div>
</aside>
