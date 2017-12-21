{foreach from=$responseList item=response}
	{if $response->isDisabled && !$commentCanModerate}
		<li>
			<p class="info commentModerationDisabledComment">{lang}wcf.comment.moderation.disabledComment{/lang}</p>
		</li>
	{else}
		<li class="commentResponse jsCommentResponse" data-object-id="{@$response->responseID}" data-response-id="{@$response->responseID}" data-object-type="com.woltlab.wcf.comment.response" data-like-liked="{if $likeData[response][$response->responseID]|isset}{@$likeData[response][$response->responseID]->liked}{/if}" data-like-likes="{if $likeData[response][$response->responseID]|isset}{@$likeData[response][$response->responseID]->likes}{else}0{/if}" data-like-dislikes="{if $likeData[response][$response->responseID]|isset}{@$likeData[response][$response->responseID]->dislikes}{else}0{/if}" data-like-users='{if $likeData[response][$response->responseID]|isset}{ {implode from=$likeData[response][$response->responseID]->getUsers() item=likeUser}"{@$likeUser->userID}": { "username": "{$likeUser->username|encodeJSON}" }{/implode} }{else}{ }{/if}' data-can-edit="{if $response->isEditable()}true{else}false{/if}" data-can-delete="{if $response->isDeletable()}true{else}false{/if}" data-user-id="{@$response->userID}">
			<div class="box32">
				{if $response->userID}
					<a href="{link controller='User' object=$response->getUserProfile()}{/link}" title="{$response->getUserProfile()->username}">
						{@$response->getUserProfile()->getAvatar()->getImageTag(32)}
					</a>
				{else}
					{@$response->getUserProfile()->getAvatar()->getImageTag(32)}
				{/if}
				
				<div class="commentContent commentResponseContent" itemprop="comment" itemscope itemtype="http://schema.org/Comment">
					<meta itemprop="dateCreated" content="{@$response->time|date:'c'}">
					
					<div class="containerHeadline">
						<h3 itemprop="author" itemscope itemtype="http://schema.org/Person">
							{if $response->userID}
								<a href="{link controller='User' object=$response->getUserProfile()}{/link}" class="userLink" data-user-id="{@$response->userID}" itemprop="url">
									<span itemprop="name">{$response->username}</span>
								</a>
							{else}
								<span itemprop="name">{$response->username}</span>
							{/if}
							
							<small class="separatorLeft">{@$response->time|time}</small>
							
							{if $response->isDisabled}
								<span class="badge label green jsIconDisabled">{lang}wcf.message.status.disabled{/lang}</span>
							{/if}
						</h3>
					</div>
					
					<div class="htmlContent userMessage" itemprop="text">{@$response->getFormattedMessage()}</div>
					
					<nav class="jsMobileNavigation buttonGroupNavigation">
						<ul class="buttonList iconList">
							{if $response->isDisabled && $commentCanModerate}
								<li class="jsOnly"><a href="#" class="jsEnableResponse"><span class="icon icon16 fa-check"></span> <span class="invisible">{lang}wcf.comment.approve{/lang}</span></a></li>
							{/if}
							{if $commentManager->supportsReport() && $__wcf->session->getPermission('user.profile.canReportContent')}
								<li class="jsReportCommentResponse jsOnly" data-object-id="{@$response->responseID}"><a href="#" title="{lang}wcf.moderation.report.reportContent{/lang}" class="jsTooltip"><span class="icon icon16 fa-exclamation-triangle"></span> <span class="invisible">{lang}wcf.moderation.report.reportContent{/lang}</span></a></li>
							{/if}
							
							{event name='commentOptions'}
						</ul>
					</nav>
				</div>
			</div>
		</li>
	{/if}
{/foreach}
