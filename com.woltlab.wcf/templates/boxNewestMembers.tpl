<ul class="sidebarBoxList">
	{foreach from=$newestMembers item=newMember}
		<li class="box24">
			<a href="{link controller='User' object=$newMember}{/link}">{@$newMember->getAvatar()->getImageTag(24)}</a>
			
			<div class="sidebarBoxHeadline">
				<h3><a href="{link controller='User' object=$newMember}{/link}" class="userLink" data-user-id="{@$newMember->userID}">{$newMember->username}</a></h3>
				<small>{@$newMember->registrationDate|time}</small>
			</div>
		</li>
	{/foreach}
</ul>
