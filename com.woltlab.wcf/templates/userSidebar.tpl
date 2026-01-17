{if !$user->isProtected()}
	{if MODULE_TROPHY && $__wcf->session->getPermission('user.profile.trophy.canSeeTrophies') && ($user->isAccessible('canViewTrophies') || $user->userID == $__wcf->session->userID) && $specialTrophyCount}
		<section class="box" data-static-box-identifier="com.woltlab.wcf.UserTrophies">
			<h2 class="boxTitle">{lang}wcf.user.trophy.trophyPoints{/lang} <span class="badge">{#$user->trophyPoints}</span></h2>
			
			<div class="boxContent">
				<ol class="sidebarList">
					{foreach from=$user->getSpecialTrophies() item=trophy}
						<li class="sidebarListItem">
							<div class="sidebarListItem__image">
								{unsafe:$trophy->renderTrophy(24)}
							</div>

							<div class="sidebarListItem__content">
								<h3 class="sidebarListItem__title">
									<a href="{$trophy->getLink()}" class="sidebarListItem__link">{$trophy}</a>
								</h3>
							</div>
						</li>
					{/foreach}
				</ol>
				
				{if $user->trophyPoints > $specialTrophyCount}
					<button type="button" class="button small more userTrophyOverlayList" data-user-id="{$user->userID}">{lang}wcf.global.button.showAll{/lang}</button>
				{/if}
			</div>
		</section>
	{/if}
	
	{if $followingCount}
		<section class="box" data-static-box-identifier="com.woltlab.wcf.UserProfileFollowing">
			<h2 class="boxTitle">{lang}wcf.user.profile.following{/lang} <span class="badge">{#$followingCount}</span></h2>
			
			<div class="boxContent">
				<ul class="userAvatarList">
					{foreach from=$following item=followingUser}
						<li>{user object=$followingUser type='avatar48' title=$followingUser->username class='jsTooltip'}</li>
					{/foreach}
				</ul>
				
				{if $followingCount > 7}
					<button type="button" id="followingAll" class="button small more jsOnly" data-dialog-title="{lang}wcf.user.profile.following{/lang}">{lang}wcf.global.button.showAll{/lang}</button>
				{/if}
			</div>
		</section>
	{/if}
	
	{if $followerCount}
		<section class="box" data-static-box-identifier="com.woltlab.wcf.UserProfileFollowers">
			<h2 class="boxTitle">{lang}wcf.user.profile.followers{/lang} <span class="badge">{#$followerCount}</span></h2>
			
			<div class="boxContent">
				<ul class="userAvatarList">
					{foreach from=$followers item=follower}
						<li>{user object=$follower type='avatar48' title=$follower->username class='jsTooltip'}</li>
					{/foreach}
				</ul>
					
				{if $followerCount > 7}
					<button type="button" id="followerAll" class="button small more jsOnly" data-dialog-title="{lang}wcf.user.profile.followers{/lang}">{lang}wcf.global.button.showAll{/lang}</button>
				{/if}
			</div>
		</section>
	{/if}
	
	{if $visitorCount}
		<section class="box" data-static-box-identifier="com.woltlab.wcf.UserProfileVisitors">
			<h2 class="boxTitle">{lang}wcf.user.profile.visitors{/lang} <span class="badge">{#$visitorCount}</span></h2>
			
			<div class="boxContent">
				<ul class="userAvatarList">
					{foreach from=$visitors item=visitor}
						<li><a href="{$visitor->getLink()}" title="{$visitor->username} ({time time=$visitor->time type='plainTime'})" class="jsTooltip">{unsafe:$visitor->getAvatar()->getImageTag(48)}</a></li>
					{/foreach}
				</ul>
					
				{if $visitorCount > 7}
					<button type="button" id="visitorAll" class="button small more jsOnly" data-dialog-title="{lang}wcf.user.profile.visitors{/lang}">{lang}wcf.global.button.showAll{/lang}</button>
				{/if}
			</div>
		</section>
	{/if}
	
	{event name='boxes'}
{/if}
