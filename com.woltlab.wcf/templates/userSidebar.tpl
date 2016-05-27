{if !$user->isProtected()}
	{if $followingCount}
		<section class="box">
			<h2 class="boxTitle">{lang}wcf.user.profile.following{/lang} <span class="badge">{#$followingCount}</span></h2>
			
			<div class="boxContent">
				<ul class="userAvatarList">
					{foreach from=$following item=followingUser}
						<li><a href="{link controller='User' object=$followingUser}{/link}" title="{$followingUser->username}" class="jsTooltip">{@$followingUser->getAvatar()->getImageTag(48)}</a></li>
					{/foreach}
				</ul>
				
				{if $followingCount > 7}
					<a id="followingAll" class="button small more jsOnly">{lang}wcf.global.button.showAll{/lang}</a>
				{/if}
			</div>
		</section>
	{/if}
	
	{if $followerCount}
		<section class="box">
			<h2 class="boxTitle">{lang}wcf.user.profile.followers{/lang} <span class="badge">{#$followerCount}</span></h2>
			
			<div class="boxContent">
				<ul class="userAvatarList">
					{foreach from=$followers item=follower}
						<li><a href="{link controller='User' object=$follower}{/link}" title="{$follower->username}" class="jsTooltip">{@$follower->getAvatar()->getImageTag(48)}</a></li>
					{/foreach}
				</ul>
					
				{if $followerCount > 7}
					<a id="followerAll" class="button small more jsOnly">{lang}wcf.global.button.showAll{/lang}</a>
				{/if}
			</div>
		</section>
	{/if}
	
	{if $visitorCount}
		<section class="box">
			<h2 class="boxTitle">{lang}wcf.user.profile.visitors{/lang} <span class="badge">{#$visitorCount}</span></h2>
			
			<div class="boxContent">
				<ul class="userAvatarList">
					{foreach from=$visitors item=visitor}
						<li><a href="{link controller='User' object=$visitor}{/link}" title="{$visitor->username} ({@$visitor->time|plainTime})" class="jsTooltip">{@$visitor->getAvatar()->getImageTag(48)}</a></li>
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
