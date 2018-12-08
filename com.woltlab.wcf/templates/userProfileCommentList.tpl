{include file='__commentJavaScript' commentContainerID='userProfileCommentList'}

{if $commentCanAdd}
	<ul id="userProfileCommentList" class="commentList containerList" data-can-add="true" data-object-id="{@$userID}" data-object-type-id="{@$commentObjectTypeID}" data-comments="{@$commentList->countObjects()}" data-last-comment-time="{@$lastCommentTime}">
		{include file='commentListAddComment' wysiwygSelector='userProfileCommentListAddComment'}
		{include file='commentList'}
	</ul>
{else}
	{hascontent}
		<ul id="userProfileCommentList" class="commentList containerList" data-can-add="false" data-object-id="{@$userID}" data-object-type-id="{@$commentObjectTypeID}" data-comments="{@$commentList->countObjects()}" data-last-comment-time="{@$lastCommentTime}">
			{content}
				{include file='commentList'}
			{/content}
		</ul>
	{hascontentelse}
		<div class="section">
			{lang}wcf.user.profile.content.wall.noEntries{/lang}
		</div>
	{/hascontent}
{/if}