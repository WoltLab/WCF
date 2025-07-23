<div class="userCard">
	<div class="userCard__header">
		<div class="userCard__header__background">
			<img
				class="userCard__header__background__image"
				src="{$user->getCoverPhoto()->getThumbnailURL()}"
				alt="">
		</div>
		<div class="userCard__header__avatar">
			{unsafe:$user->getAvatar()->getImageTag(64)}

			{if $user->isOnline()}<span class="userCard__onlineIndicator jsTooltip" title="{lang username=$user->username}wcf.user.online.title{/lang}"></span>{/if}
		</div>
	</div>

	<div class="userCard__content">
		<h3 class="userCard__username">
			<a href="{$user->getLink()}" class="userCard__link">{unsafe:$user->getFormattedUsername()}</a>
			
			{if $user->banned}
				<span class="jsTooltip jsUserBanned" title="{lang}wcf.user.banned{/lang}">
					{icon name='lock'}
				</span>
			{/if}

			{event name='icons'}
		</h3>

		{event name='afterUsername'}

		{if MODULE_USER_RANK}
			{hascontent}
				<div class="userCard__title">
					{content}
						{if $user->getUserTitle()}
							<span class="badge userTitleBadge{if $user->getRank() && $user->getRank()->cssClassName} {$user->getRank()->cssClassName}{/if}">{$user->getUserTitle()}</span>
						{/if}
						{if $user->getRank() && $user->getRank()->rankImage}
							<span class="userRankImage">{unsafe:$user->getRank()->getImage()}</span>
						{/if}
					{/content}
				</div>
			{/hascontent}
		{/if}

		{event name='afterUserTitle'}

		{hascontent}
			<div class="userCard__buttons">
				{content}
					{unsafe:$quickInteractions}
					
					{* @deprecated 6.2: Use interaction provider instead. *}{event name='buttons'}

					{unsafe:$contextMenuButton}
				{/content}
			</div>
		{/hascontent}

		{event name='afterButtons'}

		{hascontent}
			<div class="userCard__details">
				<dl class="plain dataList">
					{content}
						{event name='beforeDetails'}

						{if $user->canViewOnlineStatus() && $user->getLastActivityTime()}
							<dt>{lang}wcf.user.usersOnline.lastActivity{/lang}</dt>
							<dd>{time time=$user->getLastActivityTime()}</dd>
							{if $user->getCurrentLocation()}
								<dt>{lang}wcf.user.usersOnline.location{/lang}</dt>
								<dd>{unsafe:$user->getCurrentLocation()}</dd>
							{/if}
						{/if}

						{event name='afterDetails'}
					{/content}
				</dl>
			</div>
		{/hascontent}
	</div>

	{hascontent}
		<div class="userCard__footer">
			<div class="userCard__footer__stats">
				{content}
					{event name='beforeStats'}
					
					{if MODULE_LIKE && $user->likesReceived}
						<div class="userCard__footer__statsItem">
							<span class="userCard__footer__statsItem__key">{lang}wcf.user.reactionsReceived{/lang}</span>
							<span class="userCard__footer__statsItem__value">{#$user->likesReceived}</span>
						</div>
					{/if}

					{if $user->activityPoints}
						<div class="userCard__footer__statsItem">
							<span class="userCard__footer__statsItem__key">{lang}wcf.user.activityPoint{/lang}</span>
							<span class="userCard__footer__statsItem__value">{#$user->activityPoints}</span>
						</div>
					{/if}

					{if $user->showTrophyPoints()}
						<div class="userCard__footer__statsItem">
							<span class="userCard__footer__statsItem__key">{lang}wcf.user.trophy.trophyPoints{/lang}</span>
							<span class="userCard__footer__statsItem__value">{#$user->trophyPoints}</span>
						</div>
					{/if}

					{event name='afterStats'}
				{/content}
			</div>
		</div>
	{/hascontent}
</div>
