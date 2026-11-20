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

			{hascontent}
				<div class="messageListView__item__userStats">
					{content}
						{event name='userStats'}
					{/content}
				</div>
			{/hascontent}

			{event name='sidebar'}
		{/if}
	</div>
</aside>
