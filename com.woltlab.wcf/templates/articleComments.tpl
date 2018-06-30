{if $commentList|count || $commentCanAdd}
	<section id="comments" class="section sectionContainerList">
		<h2 class="sectionTitle">{lang}wcf.global.comments{/lang}{if $article->comments} <span class="badge">{#$article->comments}</span>{/if}</h2>
		
		{include file='__commentJavaScript' commentContainerID='articleCommentList'}
		
		<ul id="articleCommentList" class="commentList containerList" data-can-add="{if $commentCanAdd}true{else}false{/if}" data-object-id="{@$articleContentID}" data-object-type-id="{@$commentObjectTypeID}" data-comments="{@$commentList->countObjects()}" data-last-comment-time="{@$lastCommentTime}">
			{if $commentCanAdd}{include file='commentListAddComment' wysiwygSelector='articleCommentListAddComment'}{/if}
			{include file='commentList'}
		</ul>
	</section>
{/if}
