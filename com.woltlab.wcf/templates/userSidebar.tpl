{if !$user->isProtected()}
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
					<a id="followingAll" class="button small more jsOnly">{lang}wcf.global.button.showAll{/lang}</a>
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
					<a id="followerAll" class="button small more jsOnly">{lang}wcf.global.button.showAll{/lang}</a>
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
						<li><a href="{$visitor->getLink()}" title="{$visitor->username} ({@$visitor->time|plainTime})" class="jsTooltip">{@$visitor->getAvatar()->getImageTag(48)}</a></li>
					{/foreach}
				</ul>
					
				{if $visitorCount > 7}
					<a id="visitorAll" class="button small more jsOnly">{lang}wcf.global.button.showAll{/lang}</a>
				{/if}
			</div>
		</section>
	{/if}
	
	{event name='boxes'}
{/if}
