<ul class="sidebarBoxList">
	{foreach from=$mostActiveMembers item=activeMember}
		<li class="box24">
			<a href="{link controller='User' object=$activeMember}{/link}">{@$activeMember->getAvatar()->getImageTag(24)}</a>
			
			<div class="sidebarBoxHeadline">
				<h3><a href="{link controller='User' object=$activeMember}{/link}" class="userLink" data-user-id="{@$activeMember->userID}">{$activeMember->username}</a></h3>
				<small>{lang}wcf.dashboard.box.mostActiveMembers.points{/lang}</small>
			</div>
		</li>
	{/foreach}
</ul>
