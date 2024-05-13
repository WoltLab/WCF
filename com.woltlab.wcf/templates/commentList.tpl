{if !$commentManager|isset}{assign var='commentManager' value=$commentList->getCommentManager()}{/if}
{if !$commentCanAdd|isset}{assign var=commentCanAdd value=$commentManager->canAdd($commentList->objectID)}{/if}
{if !$commentCanModerate|isset}{assign var=commentCanModerate value=$commentManager->canModerate($commentList->objectTypeID, $commentList->objectID)}{/if}
{foreach from=$commentList item=comment}
	{if $comment->isDisabled && !$commentCanModerate}
		<div class="commentList__item">
			<p class="info commentModerationDisabledComment">{lang}wcf.comment.moderation.disabledComment{/lang}</p>
		</div>
	{else}
		<div class="commentList__item jsComment{if $__wcf->getUserProfileHandler()->isIgnoredUser($comment->userID, 2)} ignoredUserContent{/if}"
			data-comment-id="{@$comment->commentID}"
			{@$__wcf->getReactionHandler()->getDataAttributes('com.woltlab.wcf.comment', $comment->commentID)}
			data-can-edit="{if $comment->isEditable()}true{else}false{/if}" data-can-delete="{if $comment->isDeletable()}true{else}false{/if}"
			data-responses="{@$comment->responses}" data-last-response-time="{if $ignoreLastResponseTime|empty}{@$comment->getLastResponseTime()}{else}1{/if}" data-is-disabled="{@$comment->isDisabled}"
			data-last-response-id="{if $ignoreLastResponseTime|empty}{@$comment->getLastResponseID()}{else}0{/if}"
		>
			<woltlab-core-comment class="comment" comment-id="{@$comment->commentID}" itemprop="comment" itemscope itemtype="http://schema.org/Comment">
				<div class="comment__header">
					<div class="comment__avatar">
						{user object=$comment->getUserProfile() type='avatar32' ariaHidden='true' tabindex='-1'}
					</div>
					<div class="comment__author" itemprop="author" itemscope itemtype="http://schema.org/Person">
						{if $comment->userID}
							<a href="{$comment->getUserProfile()->getLink()}" class="comment__author__link userLink" data-object-id="{@$comment->userID}" itemprop="url">
								<span itemprop="name">{@$comment->getUserProfile()->getFormattedUsername()}</span>
							</a>
						{else}
							<span itemprop="name">{$comment->username}</span>
						{/if}
					</div>
					<div class="comment__date">
						<meta itemprop="datePublished" content="{@$comment->time|date:'c'}">
						<a href="{$comment->getLink()}" class="comment__permalink">{@$comment->time|time}</a>
					</div>
					<div class="comment__status">
						{if $comment->isDisabled}
							<span class="badge label green comment__status--disabled">{lang}wcf.message.status.disabled{/lang}</span>
						{/if}
						
						{if $commentManager->isContentAuthor($comment)}
							<span class="badge label">{lang}wcf.comment.objectAuthor{/lang}</span>
						{/if}

						{event name='commentStatus'}
					</div>
					{hascontent}
						<div class="comment__menu dropdown" id="commentOptions{@$comment->commentID}">
							<button type="button" class="dropdownToggle" aria-label="{lang}wcf.global.button.more{/lang}">{icon name='ellipsis-vertical'}</button>

							<ul class="dropdownMenu">
								{content}
									{if $comment->isDisabled && $commentCanModerate}
										<li>
											<a href="#" class="comment__option comment__option--enable">
												{lang}wcf.comment.approve{/lang}
											</a>
										</li>
									{/if}
									{if $commentManager->supportsReport() && $__wcf->session->getPermission('user.profile.canReportContent')}
										<li>
											<a
												href="#"
												data-report-content="com.woltlab.wcf.comment.comment"
												data-object-id="{$comment->commentID}"
												class="comment__option comment__option--report"
											>
												{lang}wcf.moderation.report.reportContent{/lang}
											</a>
										</li>
									{/if}
									{if $comment->isEditable()}
										<li>
											<a href="#" class="comment__option comment__option--edit">
												{lang}wcf.global.button.edit{/lang}
											</a>
										</li>
									{/if}
									{if $comment->isDeletable()}
										<li>
											<a href="#" class="comment__option comment__option--delete">
												{lang}wcf.global.button.delete{/lang}
											</a>
										</li>
									{/if}

									{event name='commentMenuOptions'}
								{/content}
							</ul>
						</div>
					{/hascontent}

					{event name='commentHeader'}
				</div>

				{event name='commentBeforeMessage'}

				<div class="comment__message">
					<div class="htmlContent userMessage" itemprop="text">{@$comment->getFormattedMessage()}</div>
				</div>

				{event name='commentAfterMessage'}
				
				<div class="comment__footer">
					<div class="comment__reactions">
						{if MODULE_LIKE && $commentManager->supportsLike() && $likeData|isset}
							{include file="reactionSummaryList" isTiny=true reactionData=$likeData[comment] objectType="com.woltlab.wcf.comment" objectID=$comment->commentID}
						{else}
							<a href="#" class="reactionSummaryList reactionSummaryListTiny" data-object-type="com.woltlab.wcf.comment" data-object-id="{$comment->commentID}" title="{lang}wcf.reactions.summary.listReactions{/lang}" style="display: none;"></a>
						{/if}
					</div>

					<div class="comment__buttons">
						{if $commentCanAdd}
							<button
								type="button"
								class="comment__button comment__button--reply button small"
							>
								<span>{lang}wcf.comment.button.response.add{/lang}</span>
							</button>
						{/if}
						
						{if MODULE_LIKE && $commentManager->supportsLike() && $__wcf->session->getPermission('user.like.canLike') && $comment->userID != $__wcf->user->userID}
							<button
								type="button"
								class="comment__button comment__button--react jsTooltip button small {if $likeData[comment][$comment->commentID]|isset && $likeData[comment][$comment->commentID]->reactionTypeID} active{/if}"
								title="{lang}wcf.reactions.react{/lang}"
								data-reaction-type-id="{if $likeData[comment][$comment->commentID]|isset && $likeData[comment][$comment->commentID]->reactionTypeID}{$likeData[comment][$comment->commentID]->reactionTypeID}{else}0{/if}"
							>
								{icon name='face-smile'}
								<span class="invisible">{lang}wcf.reactions.react{/lang}</span>
							</button>
						{/if}

						{event name='commentButtons'}
					</div>

					{event name='commentFooter'}
				</div>
			</woltlab-core-comment>

			{if !$ignoreLastResponseTime|empty || $comment|count}
				<div class="comment__responses">
					<div class="commentResponseList" data-responses="{if $commentCanModerate}{@$comment->unfilteredResponses}{else}{@$comment->responses}{/if}">
						{if $ignoreLastResponseTime|empty}{include file='commentResponseList' responseList=$comment}{/if}
					</div>
				</div>
			{/if}
		</div>
	{/if}
{/foreach}
