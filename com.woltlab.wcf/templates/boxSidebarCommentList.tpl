<ul class="sidebarItemList">
	{foreach from=$boxCommentList item=boxComment}
		<li>
			<div class="sidebarItemTitle">
				<h3><a href="{$boxComment->getLink()}">{$boxComment->title}</a></h3>
			</div>
			
			<p><small>{@$boxComment->getExcerpt(50)}</small></p>
			<p><small><a href="{link controller='User' object=$boxComment->getUserProfile()}{/link}" class="userLink" data-user-id="{@$boxComment->userID}">{$boxComment->username}</a> - {@$boxComment->time|time}</small></p>
		</li>
	{/foreach}
</ul>
