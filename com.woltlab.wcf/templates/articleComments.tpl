{if $commentList|count || $commentCanAdd}
	<section id="comments" class="section sectionContainerList">
		<h2 class="sectionTitle">{lang}wcf.global.comments{/lang}{if $articleContent->comments} <span class="badge">{#$articleContent->comments}</span>{/if}</h2>
		
		{include file='comments' commentContainerID='articleCommentList' commentObjectID=$articleContentID}
	</section>
{/if}
