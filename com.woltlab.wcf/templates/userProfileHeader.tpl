<header class="userProfileHeader" data-object-id="{$view->user->userID}">
	<div class="userProfileHeader__coverPhotoContainer">
		<div class="userProfileHeader__coverPhoto">
			<img src="{$view->user->getCoverPhoto()->getURL()}" data-object-id="{$view->user->getCoverPhoto()->getObjectID()}" class="userProfileHeader__coverPhotoImage">
		</div>
		
		<div class="userProfileHeader__manageButtons">
			{event name='beforeManageButtons'}
			
			{if $view->canEditCoverPhoto()}
				<ul class="userProfileManageCoverPhoto buttonGroup buttonList smallButtons">
					<li>
						<button type="button" data-edit-cover-photo="{link controller="UserCoverPhoto" id=$view->user->userID}{/link}" data-default-cover-photo="{$__wcf->styleHandler->getStyle()->getCoverPhotoUrl()}" class="button small">
							{icon name='camera'}
							<span>{lang}wcf.user.coverPhoto.edit{/lang}</span>
						</button>
					</li>
				</ul>
			{/if}

			{if $view->user->canEditAvatar()}
				<button type="button" data-edit-avatar="{link controller="UserAvatar" id=$view->user->userID}{/link}" class="button small">
					{icon name='circle-user' type='solid'}
					<span>{lang}wcf.user.avatar.edit{/lang}</span>
				</button>
			{/if}

			{if $view->canEditUser()}
				<button type="button" class="jsButtonEditProfile button small">
					{icon name='pencil'}
					<span>{lang}wcf.user.editProfile{/lang}</span>
				</button>
			{/if}

			{event name='afterManageButtons'}
		</div>
	</div>

	<div class="userProfileHeader__content">
		<div class="userProfileHeader__avatar">
			<div class="userProfileHeader__avatarBorder">
				{unsafe:$view->user->getAvatar()->getImageTag(128)}
				
				{if $view->user->isOnline()}<span class="userProfileHeader__onlineIndicator jsTooltip" title="{lang username=$view->user->username}wcf.user.online.title{/lang}"></span>{/if}
			</div>
		</div>
		<div class="userProfileHeader__title">
			<h1 class="userProfileHeader__username">
				<span class="userProfileUsername">{$view->user->username}</span>
				{if $view->user->banned}
					<span class="jsTooltip jsUserBanned" title="{lang user=$view->user}wcf.user.banned{/lang}">
						{icon name='lock'}
					</span>
				{/if}
			</h1>
			<div class="userProfileHeader__rank">
				{if MODULE_USER_RANK}
					{if $view->user->getUserTitle()}
						<span class="badge userTitleBadge{if $view->user->getRank() && $view->user->getRank()->cssClassName} {$view->user->getRank()->cssClassName}{/if}">{$view->user->getUserTitle()}</span>
					{/if}
					{if $view->user->getRank() && $view->user->getRank()->rankImage}
						<span class="userRankImage">{unsafe:$view->user->getRank()->getImage()}</span>
					{/if}
				{/if}
			</div>
			{event name='afterTitle'}
		</div>
		<div class="userProfileHeader__stats">
			{foreach from=$view->getStatItems() item='statItem'}
				{if $statItem->link}
					<a href="{$statItem->link}" class="userProfileHeader__statItem {$statItem->cssClassName}" {unsafe:$statItem->attributes}>
						<span class="userProfileHeader__statTitle">{unsafe:$statItem->title}</span>
						<span class="userProfileHeader__statValue">{unsafe:$statItem->value}</span>
					</a>
				{elseif $statItem->isButton}
					<button type="button" class="userProfileHeader__statItem {$statItem->cssClassName}" {unsafe:$statItem->attributes}>
						<span class="userProfileHeader__statTitle">{unsafe:$statItem->title}</span>
						<span class="userProfileHeader__statValue">{unsafe:$statItem->value}</span>
					</button>
				{else}
					<div class="userProfileHeader__statItem {$statItem->cssClassName}">
						<span class="userProfileHeader__statTitle">{unsafe:$statItem->title}</span>
						<span class="userProfileHeader__statValue">{unsafe:$statItem->value}</span>
					</div>
				{/if}
			{/foreach}
		</div>
		<div class="userProfileHeader__buttons">
			{event name='beforeButtons'}

			{if $__wcf->user->userID && $view->user->userID != $__wcf->user->userID}
				{if !$__wcf->getUserProfileHandler()->isIgnoredByUser($view->user->userID)}
					{if $__wcf->getUserProfileHandler()->isFollowing($view->user->userID)}
						<button
							type="button"
							data-following="1"
							data-follow-user="{link controller='UserFollow' id=$view->user->userID}{/link}"
							class="userProfileHeader__button button small jsTooltip"
							title="{lang}wcf.user.button.unfollow{/lang}"
						>{icon name='minus' type='solid'}</button>
					{else}
						<button
							type="button"
							data-following="0"
							data-follow-user="{link controller='UserFollow' id=$view->user->userID}{/link}"
							class="userProfileHeader__button button small jsTooltip"
							title="{lang}wcf.user.button.follow{/lang}"
						>{icon name='plus' type='solid'}</button>
					{/if}
				{/if}
			{/if}
			
			{unsafe:$view->getInteractionContextMenu()->render()}

			{unsafe:$view->getManagementContextMenu()->render()}

			{if $view->hasSearchContentLinks()}
				<div class="userProfileHeader__button dropdown">
					<button type="button" class="button buttonPrimary small dropdownToggle">
						{icon name='magnifying-glass'}
						<span>{lang}wcf.user.searchUserContent{/lang}</span>
					</button>
					<ul class="dropdownMenu">
						{foreach from=$view->getSearchContentLinks() item='searchContentLink'}
							<li><a href="{$searchContentLink->link}">{$searchContentLink->title}</a></li>
						{/foreach}
					</ul>
				</div>
			{/if}

			{event name='afterButtons'}
		</div>

		<div class="userProfileHeader__meta">
			<dl class="plain userProfileHeader__meta__dataList">
				{event name='beforeMeta'}

				<dt>{icon name='calendar'} {lang}wcf.user.registrationDate{/lang}</dt>
				<dd>{time time=$view->user->registrationDate type='plainDate'}</dd>
				{if $view->user->getOldUsername()}
					<dt>{icon name='scroll'} {lang}wcf.user.oldUsername{/lang}</dt>
					<dd>{$view->user->getOldUsername()}</dd>
				{/if}
				{if $view->user->canViewOnlineStatus() && $view->user->getLastActivityTime()}
					<dt>{icon name='clock'} {lang}wcf.user.usersOnline.lastActivity{/lang}</dt>
					<dd>{time time=$view->user->getLastActivityTime()}</dd>
					{if $view->user->getCurrentLocation()}
						<dt>{icon name='location-arrow'} {lang}wcf.user.usersOnline.location{/lang}</dt>
						<dd>{unsafe:$view->user->getCurrentLocation()}</dd>
					{/if}
				{/if}
				{if $__wcf->session->getPermission('admin.user.canViewIpAddress') && $view->user->registrationIpAddress}
					<dt>{icon name='network-wired'} {lang}wcf.user.registrationIpAddress{/lang}</dt>
					<dd>{unsafe:$view->user->getRegistrationIpAddress()|ipSearch}</dd>
				{/if}

				{event name='afterMeta'}
			</dl>
		</div>
	</div>
</header>
