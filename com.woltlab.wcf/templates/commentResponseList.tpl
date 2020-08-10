{foreach from=$responseList item=response}
	{if $response->isDisabled && !$commentCanModerate}
		<li>
			<p class="info commentModerationDisabledComment">{lang}wcf.comment.moderation.disabledComment{/lang}</p>
		</li>
	{else}
		<li class="commentResponse jsCommentResponse" data-response-id="{@$response->responseID}" {@$__wcf->getReactionHandler()->getDataAttributes('com.woltlab.wcf.comment.response', $response->responseID)} data-can-edit="{if $response->isEditable()}true{else}false{/if}" data-can-delete="{if $response->isDeletable()}true{else}false{/if}" data-user-id="{@$response->userID}">
			<div class="box32{if $__wcf->getUserProfileHandler()->isIgnoredUser($response->userID)} ignoredUserContent{/if}">
				{if $response->userID}
					{user object=$response->getUserProfile() type='avatar48' title=$response->getUserProfile()->username}
				{else}
					{@$response->getUserProfile()->getAvatar()->getImageTag(32)}
				{/if}
				
				<div class="commentContent commentResponseContent" itemprop="comment" itemscope itemtype="http://schema.org/Comment">
					<meta itemprop="dateCreated" content="{@$response->time|date:'c'}">
					
					<div class="containerHeadline">
						<h3 itemprop="author" itemscope itemtype="http://schema.org/Person">
							{if $response->userID}
								<a href="{$response->getUserProfile()->getLink()}" class="userLink" data-object-id="{@$response->userID}" itemprop="url">
									<span itemprop="name">{@$response->getUserProfile()->getFormattedUsername()}</span>
								</a>
							{else}
								<span itemprop="name">{$response->username}</span>
							{/if}
							
							<small class="separatorLeft">{@$response->time|time}</small>
							
							{if $response->isDisabled}
								<span class="badge label green jsIconDisabled">{lang}wcf.message.status.disabled{/lang}</span>
							{/if}
							
							{if $commentManager->isContentAuthor($response)}
								<span class="badge label">{lang}wcf.comment.objectAuthor{/lang}</span>
							{/if}
						</h3>
					</div>
					
					<div class="htmlContent userMessage" itemprop="text">{@$response->getFormattedMessage()}</div>

					{if MODULE_LIKE && $likeData|isset}{include file="reactionSummaryList" isTiny=true reactionData=$likeData[response] objectType="com.woltlab.wcf.comment.response" objectID=$response->responseID}{else}<a href="#" class="reactionSummaryList reactionSummaryListTiny jsOnly" data-object-type="com.woltlab.wcf.comment.response" data-object-id="{$response->responseID}" title="{lang}wcf.reactions.summary.listReactions{/lang}" style="display: none;"></a>{/if}
					
					<nav class="jsMobileNavigation buttonGroupNavigation">
						<ul class="buttonList iconList">
							{if $response->isDisabled && $commentCanModerate}
								<li class="jsOnly"><a href="#" class="jsEnableResponse"><span class="icon icon16 fa-check"></span> <span class="invisible">{lang}wcf.comment.approve{/lang}</span></a></li>
							{/if}
							{if $commentManager->supportsReport() && $__wcf->session->getPermission('user.profile.canReportContent')}
								<li class="jsReportCommentResponse jsOnly" data-object-id="{@$response->responseID}"><a href="#" title="{lang}wcf.moderation.report.reportContent{/lang}" class="jsTooltip"><span class="icon icon16 fa-exclamation-triangle"></span> <span class="invisible">{lang}wcf.moderation.report.reportContent{/lang}</span></a></li>
							{/if}
							
							{if MODULE_LIKE && $__wcf->session->getPermission('user.like.canLike') && $response->userID != $__wcf->user->userID}<li class="jsOnly"><a href="#" class="reactButton jsTooltip {if $likeData[response][$response->responseID]|isset && $likeData[response][$response->responseID]->reactionTypeID} active{/if}" title="{lang}wcf.reactions.react{/lang}" data-reaction-type-id="{if $likeData[response][$response->responseID]|isset && $likeData[response][$response->responseID]->reactionTypeID}{$likeData[response][$response->responseID]->reactionTypeID}{else}0{/if}"><span class="icon icon16 fa-smile-o"></span> <span class="invisible">{lang}wcf.reactions.react{/lang}</span></a></li>{/if}
							
							{event name='commentOptions'}
						</ul>
					</nav>
				</div>
			</div>
		</li>
	{/if}
{/foreach}
