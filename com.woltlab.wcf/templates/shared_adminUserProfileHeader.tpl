<header class="userProfileHeader" data-object-id="{$view->user->userID}">
	<div class="userProfileHeader__coverPhotoContainer">
		<div class="userProfileHeader__coverPhoto">
			<img src="{$view->getCoverPhoto()->getURL()}" data-object-id="{$view->getCoverPhoto()->getObjectID()}" class="userProfileHeader__coverPhotoImage" alt="">
		</div>

		<div class="userProfileHeader__manageButtons">
			{event name='beforeManageButtons'}

			{if $view->canEditCoverPhoto()}
				<button type="button" data-edit-cover-photo="{link controller='UserCoverPhoto' forceFrontend=true id=$view->user->userID}{/link}" data-default-cover-photo="{$view->getDefaultCoverPhotoUrl()}" class="button small">
					{icon name='camera'}
					<span>{lang}wcf.user.coverPhoto.edit{/lang}</span>
				</button>
			{/if}

			{if $view->canEditAvatar()}
				<button type="button" data-edit-avatar="{link controller='UserAvatar' forceFrontend=true id=$view->user->userID}{/link}" data-default-avatar="{$__wcf->getPath()}images/avatars/avatar-default.svg" class="button small">
					{icon name='circle-user' type='solid'}
					<span>{lang}wcf.user.avatar.edit{/lang}</span>
				</button>
			{/if}

			{event name='afterManageButtons'}
		</div>
	</div>

	<div class="userProfileHeader__content">
		<div class="userProfileHeader__avatar">
			<div class="userProfileHeader__avatarBorder">
				{unsafe:$view->getAvatarImageTag()}

				{if $view->user->isOnline()}<span class="userProfileHeader__onlineIndicator jsTooltip" title="{lang username=$view->user->username}wcf.user.online.title{/lang}"></span>{/if}
			</div>
		</div>
		<div class="userProfileHeader__title">
			<div class="userProfileHeader__headline">
				<h2 class="userProfileHeader__username">
					<span class="userProfileUsername">{$view->user->username}</span>
				</h2>

				<div class="userProfileHeader__statusIcons">
					{if $view->user->banned}
						<span class="jsTooltip" role="status" title="{lang user=$view->user}wcf.user.banned{/lang}">
							{icon name='lock'}
						</span>
					{/if}

					{event name='icons'}
				</div>
			</div>

			{event name='afterUsername'}

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
			{event name='afterUserTitle'}
		</div>
		<div class="userProfileHeader__stats">
			{foreach from=$view->getStatItems() item='statItem'}
				{if $statItem->link}
					<a href="{$statItem->link}" class="userProfileHeader__statItem {$statItem->cssClassName}" {unsafe:$statItem->attributes}>
						<span class="userProfileHeader__statTitle">{unsafe:$statItem->title}</span>
						<span class="userProfileHeader__statValue">{unsafe:$statItem->value}</span>
					</a>
				{else}
					{* Buttons rely on frontend-only JavaScript and are therefore shown as plain text. *}
					<div class="userProfileHeader__statItem">
						<span class="userProfileHeader__statTitle">{unsafe:$statItem->title}</span>
						<span class="userProfileHeader__statValue">{unsafe:$statItem->value}</span>
					</div>
				{/if}
			{/foreach}
		</div>
		<div class="userProfileHeader__buttons">
			{event name='beforeButtons'}

			{hascontent}
				{content}{unsafe:$view->getInteractionContextMenu()->render()}{/content}

				<script data-relocate="true">
					{jsphrase name='wcf.acp.content.removeContent'}
					{jsphrase name='wcf.acp.worker.abort.confirmMessage'}
				</script>
			{/hascontent}

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
				{if $__wcf->session->getPermission('admin.user.canEditPassword')}
					{if !$view->user->authData|empty}
						<dt>{icon name='arrow-right-to-bracket'} {lang}wcf.user.3rdparty{/lang}</dt>
						<dd>{lang}wcf.user.3rdparty.{$view->user->getAuthProvider()}{/lang}</dd>
					{/if}
					{if $view->user->multifactorActive}
						<dt>{icon name='fingerprint'} {lang}wcf.user.security.multifactor{/lang}</dt>
						<dd>{lang}wcf.user.security.multifactor.active{/lang}</dd>
					{/if}
				{/if}

				{event name='afterMeta'}
			</dl>
		</div>
	</div>
</header>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Acp/Component/User/ProfileHeader'], ({ setup }) => {
		setup(document.querySelector('.userProfileHeader[data-object-id="{$view->user->userID}"]'));
	});
</script>
