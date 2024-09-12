<div class="userCard">
	<div class="userCard__header">
		<div class="userCard__header__background">
			<img
				class="userCard__header__background__image"
				src="{$user->getCoverPhoto()->getURL()}"
				loading="lazy">
		</div>
		<div class="userCard__header__avatar">
			{user object=$user type='avatar64' ariaHidden='true' tabindex='-1'}

			{if $user->isOnline()}<span class="userCard__onlineIndicator jsTooltip" title="{lang username=$user->username}wcf.user.online.title{/lang}"></span>{/if}
		</div>
	</div>

	<div class="userCard__content">
		<h3 class="userCard__username">
			<a href="{$user->getLink()}">{unsafe:$user->getFormattedUsername()}</a>
			
			{if $user->banned}
				<span class="jsTooltip jsUserBanned" title="{lang}wcf.user.banned{/lang}">
					{icon name='lock'}
				</span>
			{/if}
		</h3>

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

		{hascontent}
			<div class="userCard__buttons">
				{content}
					{if $user->homepage && $user->homepage != 'http://'}
						<a class="userCard__button jsTooltip" title="{lang}wcf.user.option.homepage{/lang}" {anchorAttributes url=$user->homepage appendClassname=false isUgc=true}>{icon name='house' size=24 type='solid'}</a>
					{/if}
					{if $user->userID != $__wcf->user->userID}
						{if $user->isAccessible('canViewEmailAddress')}
							<a class="userCard__button jsTooltip" href="mailto:{unsafe:$user->getEncodedEmail()}" title="{lang}wcf.user.button.mail{/lang}">{icon name='envelope' size=24 type='solid'}</a>
						{/if}
					{/if}
					{if $__wcf->user->userID && $user->userID != $__wcf->user->userID}
						{if !$__wcf->getUserProfileHandler()->isIgnoredByUser($user->userID)}
							{if $__wcf->getUserProfileHandler()->isFollowing($user->userID)}
								<button
									type="button"
									data-following="1"
									data-follow-user="{link controller='UserFollow' id=$user->userID}{/link}"
									class="userCard__button jsTooltip"
									title="{lang}wcf.user.button.unfollow{/lang}"
								>{icon name='circle-minus' size=24 type='solid'}</button>
							{else}
								<button
									type="button"
									data-following="0"
									data-follow-user="{link controller='UserFollow' id=$user->userID}{/link}"
									class="userCard__button jsTooltip"
									title="{lang}wcf.user.button.follow{/lang}"
								>{icon name='circle-plus' size=24 type='solid'}</button>
							{/if}
						{/if}

						{if $__wcf->getUserProfileHandler()->isIgnoredUser($user->userID)}
							<button
								type="button"
								data-ignored="1"
								data-ignore-user="{link controller='UserIgnore' id=$user->userID}{/link}"
								class="userCard__button jsTooltip"
								title="{lang}wcf.user.button.unignore{/lang}"
							>{icon name='eye' size=24 type='solid'}</button>
						{else}
							<button
								type="button"
								data-ignored="0"
								data-ignore-user="{link controller='UserIgnore' id=$user->userID}{/link}"
								class="userCard__button jsTooltip"
								title="{lang}wcf.user.button.ignore{/lang}"
							>{icon name='eye-slash' size=24 type='solid'}</button>
						{/if}
					{/if}
					{event name='buttons'}
				{/content}
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
