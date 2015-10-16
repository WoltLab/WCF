<section class="userDetails"
	{if $isAccessible}
		data-object-id="{@$user->userID}"
		{if $__wcf->session->getPermission('admin.user.canBanUser')}
			data-banned="{@$user->banned}"
		{/if}
		{if $__wcf->session->getPermission('admin.user.canDisableAvatar')}
			data-disable-avatar="{@$user->disableAvatar}"
		{/if}
		{if $__wcf->session->getPermission('admin.user.canDisableSignature')}
			data-disable-signature="{@$user->disableSignature}"
		{/if}
	{/if}
>
	<div class="userAvatar">
		{if $user->userID == $__wcf->user->userID}
			<a href="{link controller='AvatarEdit'}{/link}" class="framed jsTooltip" title="{lang}wcf.user.avatar.edit{/lang}">{@$user->getAvatar()->getImageTag()}</a>
		{else}
			<span class="framed">{@$user->getAvatar()->getImageTag()}</span>
		{/if}
	</div>
	
	<h1>{$user->username}{if $user->banned} <span class="icon icon16 fa-lock jsTooltip jsUserBanned" title="{lang}wcf.user.banned{/lang}"></span>{/if}</h1>
	
	{if MODULE_USER_RANK}
		<div class="userRank">
			{if $user->getUserTitle()}
				<span class="badge userTitleBadge{if $user->getRank() && $user->getRank()->cssClassName} {@$user->getRank()->cssClassName}{/if}">{$user->getUserTitle()}</span>
			{/if}
			{if $user->getRank() && $user->getRank()->rankImage}
				<span class="userRankImage">{@$user->getRank()->getImage()}</span>
			{/if}
		</div>
	{/if}
	
	<ul class="dataList">
		{if $user->isVisibleOption('gender') && $user->gender}<li>{lang}wcf.user.gender.{if $user->gender == 1}male{else}female{/if}{/lang}</li>{/if}
		{if $user->isVisibleOption('birthday') && $user->getAge()}<li>{@$user->getAge()}</li>{/if}
		{if $user->isVisibleOption('location') && $user->location}<li>{lang}wcf.user.membersList.location{/lang}</li>{/if}
		{if $user->getOldUsername()}<li>{lang}wcf.user.profile.oldUsername{/lang}</li>{/if}
		<li>{lang}wcf.user.membersList.registrationDate{/lang}</li>
		{event name='userDataRow1'}
	</ul>
	{if $user->canViewOnlineStatus() && $user->getLastActivityTime()}
		<p class="userActivity">
			<span>{lang}wcf.user.usersOnline.lastActivity{/lang}: {@$user->getLastActivityTime()|time}</span>
			{if $user->getCurrentLocation()}<span>{@$user->getCurrentLocation()}</span>{/if}
		</p>
	{/if}
	<nav class="jsMobileNavigation buttonGroupNavigation">
		<ul id="profileButtonContainer" class="buttonGroup">
			{hascontent}
				<li class="dropdown">
					<a href="#" class="button dropdownToggle jsTooltip" title="{lang}wcf.user.searchUserContent{/lang}"><span class="icon icon16 fa-search"></span> <span class="invisible">{lang}wcf.user.searchUserContent{/lang}</span></a>
					<ul class="dropdownMenu">
						{content}
							{event name='quickSearchItems'}
						{/content}
					</ul>
				</li>
			{/hascontent}
			
			{if $__wcf->session->getPermission('user.profile.canReportContent')}
				<li class="jsReportUser jsOnly" data-object-id="{@$user->userID}"><a href="#" title="{lang}wcf.user.profile.report{/lang}" class="button jsTooltip"><span class="icon icon16 fa-exclamation-triangle"></span> <span class="invisible">{lang}wcf.user.profile.report{/lang}</span></a></li>
			{/if}
			
			{if $user->userID != $__wcf->user->userID}
				{if $user->isAccessible('canViewEmailAddress') || $__wcf->session->getPermission('admin.user.canEditMailAddress')}
					<li><a class="button jsTooltip" href="mailto:{@$user->getEncodedEmail()}" title="{lang}wcf.user.button.mail{/lang}"><span class="icon icon16 fa-envelope-o"></span> <span class="invisible">{lang}wcf.user.button.mail{/lang}</span></a></li>
				{elseif $user->isAccessible('canMail') && $__wcf->session->getPermission('user.profile.canMail')}
					<li><a class="button jsTooltip" href="{link controller='Mail' object=$user}{/link}" title="{lang}wcf.user.button.mail{/lang}"><span class="icon icon16 fa-envelope-o"></span> <span class="invisible">{lang}wcf.user.button.mail{/lang}</span></a></li>
				{/if}
			{/if}
			
			{event name='buttons'}
			
			{if $isAccessible && $__wcf->user->userID != $user->userID && ($__wcf->session->getPermission('admin.user.canBanUser') || $__wcf->session->getPermission('admin.user.canDisableAvatar') || $__wcf->session->getPermission('admin.user.canDisableSignature') || ($__wcf->session->getPermission('admin.general.canUseAcp') && $__wcf->session->getPermission('admin.user.canEditUser')){event name='moderationDropdownPermissions'})}
				<li class="dropdown">
					<a href="{link controller='UserEdit' object=$user isACP=true}{/link}" class="button jsTooltip jsUserInlineEditor" title="{lang}wcf.user.moderate{/lang}"><span class="icon icon16 fa-wrench"></span> <span class="invisible">{lang}wcf.user.moderate{/lang}</span></a>
					<ul class="dropdownMenu"></ul>
				</li>
			{/if}
		</ul>
	</nav>
</section>

{hascontent}
	<section>
		<h1 class="invisible">{lang}wcf.user.stats{/lang}</h1>
		
		<dl class="plain statsDataList">
			{content}
				{event name='statistics'}
				
				{if MODULE_LIKE && $user->likesReceived}
					<dt>{if !$user->isProtected()}<a href="{link controller='User' object=$user}{/link}#likes" class="jsTooltip" title="{lang}wcf.like.showLikesReceived{/lang}">{lang}wcf.like.likesReceived{/lang}</a>{else}{lang}wcf.like.likesReceived{/lang}{/if}</dt>
					<dd>{#$user->likesReceived}</dd>
				{/if}
				
				{if $user->activityPoints}
					<dt><a href="#" class="activityPointsDisplay jsTooltip" title="{lang}wcf.user.activityPoint.showActivityPoints{/lang}" data-user-id="{@$user->userID}">{lang}wcf.user.activityPoint{/lang}</a></dt>
					<dd>{#$user->activityPoints}</dd>
				{/if}
				
				{if $user->profileHits}
					<dt>{lang}wcf.user.profileHits{/lang}</dt>
					<dd{if $user->getProfileAge() > 1} title="{lang}wcf.user.profileHits.hitsPerDay{/lang}"{/if}>{#$user->profileHits}</dd>
				{/if}
			{/content}
		</dl>
	</section>
{/hascontent}

{event name='afterStatistics'}

{if !$user->isProtected()}
	{if $followingCount}
		<section>
			<h1>{lang}wcf.user.profile.following{/lang} <span class="badge">{#$followingCount}</span></h1>
			
			<div>
				<ul class="framedIconList">
					{foreach from=$following item=followingUser}
						<li><a href="{link controller='User' object=$followingUser}{/link}" title="{$followingUser->username}" class="framed jsTooltip">{@$followingUser->getAvatar()->getImageTag(48)}</a></li>
					{/foreach}
				</ul>
				
				{if $followingCount > 8}
					<a id="followingAll" class="button small more jsOnly">{lang}wcf.global.button.showAll{/lang}</a>
				{/if}
			</div>
		</section>
	{/if}
	
	{if $followerCount}
		<section>
			<h1>{lang}wcf.user.profile.followers{/lang} <span class="badge">{#$followerCount}</span></h1>
			
			<div>
				<ul class="framedIconList">
					{foreach from=$followers item=follower}
						<li><a href="{link controller='User' object=$follower}{/link}" title="{$follower->username}" class="framed jsTooltip">{@$follower->getAvatar()->getImageTag(48)}</a></li>
					{/foreach}
				</ul>
					
				{if $followerCount > 8}
					<a id="followerAll" class="button small more jsOnly">{lang}wcf.global.button.showAll{/lang}</a>
				{/if}
			</div>
		</section>
	{/if}
	
	{if $visitorCount}
		<section>
			<h1>{lang}wcf.user.profile.visitors{/lang} <span class="badge">{#$visitorCount}</span></h1>
			
			<div>
				<ul class="framedIconList">
					{foreach from=$visitors item=visitor}
						<li><a href="{link controller='User' object=$visitor}{/link}" title="{$visitor->username} ({@$visitor->time|plainTime})" class="framed jsTooltip">{@$visitor->getAvatar()->getImageTag(48)}</a></li>
					{/foreach}
				</ul>
					
				{if $visitorCount > 8}
					<a id="visitorAll" class="button small more jsOnly">{lang}wcf.global.button.showAll{/lang}</a>
				{/if}
			</div>
		</section>
	{/if}
	
	{event name='boxes'}
{/if}
