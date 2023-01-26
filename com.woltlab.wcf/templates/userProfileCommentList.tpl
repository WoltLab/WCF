{if $commentList|count || $commentCanAdd}
	{include file='comments' commentContainerID='userProfileCommentList' commentObjectID=$userID}
{else}
	<div class="section">
		{lang}wcf.user.profile.content.wall.noEntries{/lang}
	</div>
{/if}
