{if !$commentManager|isset}{assign var='commentManager' value=$commentList->getCommentManager()}{/if}
{if !$commentCanModerate|isset}{assign var=commentCanModerate value=$commentManager->canModerate($commentList->objectTypeID, $commentList->objectID)}{/if}
{foreach from=$commentList item=comment}
	{if $comment->isDisabled && !$commentCanModerate}
		<li>
			<p class="info commentModerationDisabledComment">{lang}wcf.comment.moderation.disabledComment{/lang}</p>
		</li>
	{else}
		<li class="comment jsComment"
		    data-object-id="{@$comment->commentID}" data-comment-id="{@$comment->commentID}" data-object-type="com.woltlab.wcf.comment"
		    data-like-liked="{if $likeData[comment][$comment->commentID]|isset}{@$likeData[comment][$comment->commentID]->liked}{/if}" data-like-likes="{if $likeData[comment][$comment->commentID]|isset}{@$likeData[comment][$comment->commentID]->likes}{else}0{/if}" data-like-dislikes="{if $likeData[comment][$comment->commentID]|isset}{@$likeData[comment][$comment->commentID]->dislikes}{else}0{/if}" data-like-users='{if $likeData[comment][$comment->commentID]|isset}{ {implode from=$likeData[comment][$comment->commentID]->getUsers() item=likeUser}"{@$likeUser->userID}": { "username": "{$likeUser->username|encodeJSON}" }{/implode} }{else}{ }{/if}'
		    data-can-edit="{if $comment->isEditable()}true{else}false{/if}" data-can-delete="{if $comment->isDeletable()}true{else}false{/if}"
		    data-responses="{@$comment->responses}" data-last-response-time="{if $commentLastResponseTime|empty}{@$comment->getLastResponseTime()}{else}{@$commentLastResponseTime}{/if}" data-user-id="{@$comment->userID}" data-is-disabled="{@$comment->isDisabled}">
			<div class="box48">
				{if $comment->userID}
					<a href="{link controller='User' object=$comment->getUserProfile()}{/link}" title="{$comment->getUserProfile()->username}">
						{@$comment->getUserProfile()->getAvatar()->getImageTag(48)}
					</a>
				{else}
					{@$comment->getUserProfile()->getAvatar()->getImageTag(48)}
				{/if}
				
				<div class="commentContentContainer" itemprop="comment" itemscope itemtype="http://schema.org/Comment">
					<div class="commentContent">
						<meta itemprop="dateCreated" content="{@$comment->time|date:'c'}">
						
						<div class="containerHeadline">
							<h3 itemprop="author" itemscope itemtype="http://schema.org/Person">
								{if $comment->userID}
									<a href="{link controller='User' object=$comment->getUserProfile()}{/link}" class="userLink" data-user-id="{@$comment->userID}" itemprop="url">
										<span itemprop="name">{$comment->username}</span>
									</a>
								{else}
									<span itemprop="name">{$comment->username}</span>
								{/if}
								
								<small class="separatorLeft">{@$comment->time|time}</small>
								
								{if $comment->isDisabled}
									<span class="badge label green jsIconDisabled">{lang}wcf.message.status.disabled{/lang}</span>
								{/if}
							</h3>
						</div>
						
						<div class="htmlContent userMessage" itemprop="text">{@$comment->getFormattedMessage()}</div>
						
						<nav class="jsMobileNavigation buttonGroupNavigation">
							<ul class="buttonList iconList">
								{if $comment->isDisabled && $commentCanModerate}
									<li class="jsOnly"><a href="#" class="jsEnableComment"><span class="icon icon16 fa-check"></span> <span class="invisible">{lang}wcf.comment.approve{/lang}</span></a></li>
								{/if}
								{if $commentManager->supportsReport() && $__wcf->session->getPermission('user.profile.canReportContent')}
									<li class="jsReportCommentComment jsOnly" data-object-id="{@$comment->commentID}"><a href="#" title="{lang}wcf.moderation.report.reportContent{/lang}" class="jsTooltip"><span class="icon icon16 fa-exclamation-triangle"></span> <span class="invisible">{lang}wcf.moderation.report.reportContent{/lang}</span></a></li>
								{/if}
								
								{event name='commentOptions'}
							</ul>
						</nav>
					</div>
					
					{if !$commentLastResponseTime|empty || $comment|count}
						<ul data-responses="{if $commentCanModerate}{@$comment->unfilteredResponses}{else}{@$comment->responses}{/if}" class="containerList commentResponseList">
							{if $commentLastResponseTime|empty}{include file='commentResponseList' responseList=$comment}{/if}
						</ul>
					{/if}
				</div>
			</div>
		</li>
	{/if}
{/foreach}
