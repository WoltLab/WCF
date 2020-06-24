<ul class="sidebarItemList">
	{foreach from=$boxCommentList item=boxComment}
		<li>
			<div class="sidebarItemTitle">
				<h3><a href="{$boxComment->getLink()}">{$boxComment->title}</a></h3>
			</div>
			
			<p><small>{@$boxComment->getExcerpt(50)}</small></p>
			<p><small>{if $boxComment->userID}{user object=$boxComment->getUserProfile()}{else}{$boxComment->username}{/if} <span class="separatorLeft">{@$boxComment->time|time}</span></small></p>
		</li>
	{/foreach}
</ul>
