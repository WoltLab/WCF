<ul class="sidebarBoxList">
	{foreach from=$mostLikedMembers item=likedMember}
		<li class="box24">
			<a href="{link controller='User' object=$likedMember}{/link}">{@$likedMember->getAvatar()->getImageTag(24)}</a>
			
			<div class="sidebarBoxHeadline">
				<h3><a href="{link controller='User' object=$likedMember}{/link}" class="userLink" data-user-id="{@$likedMember->userID}">{$likedMember->username}</a></h3>
				<small>{lang}wcf.dashboard.box.mostLikedMembers.likes{/lang}</small>
			</div>
		</li>
	{/foreach}
</ul>
