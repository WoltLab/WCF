{if $commentsView->isVisible()}
	{if $commentsView->showSection}
		<section id="comments" class="section sectionContainerList">
			<h2 class="sectionTitle">{lang}wcf.global.comments{/lang}{if $commentsView->totalComments} <span class="badge">{#$commentsView->totalComments}</span>{/if}</h2>
	{/if}

	{assign var='commentContainerID' value=$commentsView->commentContainerID}
	{assign var='commentObjectID' value=$commentsView->objectID}
	{assign var='commentCanAdd' value=$commentsView->canAddComments}
	{assign var='commentList' value=$commentsView->getCommentList()}
	{assign var='commentObjectTypeID' value=$commentsView->getObjectTypeID()}
	{assign var='lastCommentTime' value=$commentsView->getLastCommentTime()}
	{assign var='likeData' value=$commentsView->getLikeData()}
	{include file='comments'}

	{if $commentsView->showSection}
		</section>
	{/if}
{/if}
