{foreach from=$responseList item=response}
	{if $response->isDisabled && !$commentCanModerate}
		<li>
			<p class="info commentModerationDisabledComment">{lang}wcf.comment.moderation.disabledComment{/lang}</p>
		</li>
	{else}
		<li class="commentResponse jsCommentResponse{if $__wcf->getUserProfileHandler()->isIgnoredUser($response->userID, 2)} ignoredUserContent{/if}"
			data-response-id="{@$response->responseID}"
			{@$__wcf->getReactionHandler()->getDataAttributes('com.woltlab.wcf.comment.response', $response->responseID)}
			data-can-edit="{if $response->isEditable()}true{else}false{/if}"
			data-can-delete="{if $response->isDeletable()}true{else}false{/if}"
			data-user-id="{@$response->userID}"
		>
			<woltlab-core-comment-response response-id="{@$response->responseID}" itemprop="comment" itemscope itemtype="http://schema.org/Comment">
				<div class="commentResponse__header">
					<div class="commentResponse__avatar">
						{user object=$response->getUserProfile() type='avatar32' ariaHidden='true' tabindex='-1'}
					</div>
					<div class="commentResponse__author" itemprop="author" itemscope itemtype="http://schema.org/Person">
						{if $response->userID}
							<a href="{$response->getUserProfile()->getLink()}" class="userLink" data-object-id="{@$response->userID}" itemprop="url">
								<span itemprop="name">{@$response->getUserProfile()->getFormattedUsername()}</span>
							</a>
						{else}
							<span itemprop="name">{$response->username}</span>
						{/if}
					</div>
					<div class="commentResponse__date">
						<meta itemprop="dateCreated" content="{@$response->time|date:'c'}">
						{@$response->time|time}
					</div>
					<div class="commentResponse__status">
						{if $response->isDisabled}
							<span class="badge label green commentResponse__status--disabled">{lang}wcf.message.status.disabled{/lang}</span>
						{/if}
						
						{if $commentManager->isContentAuthor($response)}
							<span class="badge label">{lang}wcf.comment.objectAuthor{/lang}</span>
						{/if}
					</div>

					<div class="comment__menu dropdown" id="commentResponseOptions{@$response->responseID}">
		                <button type="button" class="button small dropdownToggle" aria-label="{lang}wcf.global.button.more{/lang}">{icon name='ellipsis-vertical'}</button>

		                <ul class="dropdownMenu">
		                    {if $response->isDisabled && $commentCanModerate}
								<li>
									<a href="#" class="commentResponse__option commentResponse__option--enable">
										{lang}wcf.comment.approve{/lang}
									</a>
								</li>
							{/if}
							{if $commentManager->supportsReport() && $__wcf->session->getPermission('user.profile.canReportContent')}
								<li>
									<a
										href="#"
										data-report-content="com.woltlab.wcf.comment.response"
										data-object-id="{$response->responseID}"
										class="commentResponse__option commentResponse__option--report"
									>
										{lang}wcf.moderation.report.reportContent{/lang}
									</a>
								</li>
							{/if}
							{if $response->isEditable()}
								<li>
									<a href="#" class="commentResponse__option commentResponse__option--edit">
										{lang}wcf.global.button.edit{/lang}
									</a>
								</li>
							{/if}
							{if $response->isDeletable()}
								<li>
									<a href="#" class="commentResponse__option commentResponse__option--delete">
										{lang}wcf.global.button.delete{/lang}
									</a>
								</li>
							{/if}

							{event name='commentResponseMenuOptions'}
		                </ul>
		            </div>

					{event name='commentResponseHeader'}
				</div>

				{event name='commentBeforeMessage'}

				<div class="commentResponse__message">
					<div class="htmlContent userMessage" itemprop="text">{@$response->getFormattedMessage()}</div>
				</div>

				{event name='commentAfterMessage'}
				
				<div class="commentResponse__footer">
					<div class="commentResponse__reactions">
						{if MODULE_LIKE && $commentManager->supportsLike() && $likeData|isset}
							{include file="reactionSummaryList" isTiny=true reactionData=$likeData[response] objectType="com.woltlab.wcf.comment.response" objectID=$response->responseID}
						{else}
							<a href="#" class="reactionSummaryList reactionSummaryListTiny jsOnly" data-object-type="com.woltlab.wcf.comment.response" data-object-id="{$response->responseID}" title="{lang}wcf.reactions.summary.listReactions{/lang}" style="display: none;"></a>
						{/if}
					</div>

					<div class="commentResponse__buttons">
						{if MODULE_LIKE && $commentManager->supportsLike() && $__wcf->session->getPermission('user.like.canLike') && $response->userID != $__wcf->user->userID}
							<button
								type="button"
								class="commentResponse__button commentResponse__button--react jsTooltip {if $likeData[response][$response->responseID]|isset && $likeData[response][$response->responseID]->reactionTypeID} active{/if}"
								title="{lang}wcf.reactions.react{/lang}"
								data-reaction-type-id="{if $likeData[response][$response->responseID]|isset && $likeData[response][$response->responseID]->reactionTypeID}{$likeData[response][$response->responseID]->reactionTypeID}{else}0{/if}"
							>
								{icon name='face-smile'}
								<span class="invisible">{lang}wcf.reactions.react{/lang}</span>
							</button>		
						{/if}
						
						{event name='commentResponseButtons'}
					</div>

					{event name='commentResponseFooter'}
				</div>
			</woltlab-core-comment-response>
		</li>
	{/if}
{/foreach}
