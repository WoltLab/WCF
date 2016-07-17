{foreach from=$responseList item=response}
	<li class="commentResponse jsCommentResponse" data-response-id="{@$response->responseID}" data-object-type="com.woltlab.wcf.comment.response" data-support-likes="{if $commentManager->supportsLike()}1{else}0{/if}" data-like-liked="{if $likeData[response][$response->responseID]|isset}{@$likeData[response][$response->responseID]->liked}{/if}" data-like-likes="{if $likeData[response][$response->responseID]|isset}{@$likeData[response][$response->responseID]->likes}{else}0{/if}" data-like-dislikes="{if $likeData[response][$response->responseID]|isset}{@$likeData[response][$response->responseID]->dislikes}{else}0{/if}" data-like-users='{if $likeData[response][$response->responseID]|isset}{ {implode from=$likeData[response][$response->responseID]->getUsers() item=likeUser}"{@$likeUser->userID}": { "username": "{$likeUser->username|encodeJSON}" }{/implode} }{else}{ }{/if}' data-can-edit="{if $response->isEditable()}true{else}false{/if}" data-can-delete="{if $response->isDeletable()}true{else}false{/if}" data-user-id="{@$response->userID}">
		<div class="box32">
			{if $response->userID}
				<a href="{link controller='User' object=$response->getUserProfile()}{/link}" title="{$response->getUserProfile()->username}" class="framed">
					{@$response->getUserProfile()->getAvatar()->getImageTag(32)}
				</a>
			{else}
				<span class="framed">{@$response->getUserProfile()->getAvatar()->getImageTag(32)}</span>
			{/if}
			
			<div class="commentContent commentResponseContent">
				<div class="containerHeadline">
					<h3>
						{if $response->userID}
							<a href="{link controller='User' object=$response->getUserProfile()}{/link}" class="userLink" data-user-id="{@$response->userID}">{$response->username}</a>
						{else}
							{$response->username}
						{/if}
						
						<small> - {@$response->time|time}</small>
					</h3>
				</div>
				
				<p class="userMessage">{@$response->getFormattedMessage()}</p>
				
				<nav class="jsMobileNavigation buttonGroupNavigation">
					<ul class="commentOptions">
						{if $commentManager->supportsReport() && $__wcf->session->getPermission('user.profile.canReportContent')}
							<li class="jsReportCommentResponse jsOnly" data-object-id="{@$response->responseID}"><a href="#" title="{lang}wcf.moderation.report.reportContent{/lang}" class="jsTooltip"><span class="icon icon16 icon-warning-sign"></span> <span class="invisible">{lang}wcf.moderation.report.reportContent{/lang}</span></a></li>
						{/if}
						
						{event name='commentOptions'}
					</ul>
				</nav>
			</div>
		</div>
	</li>
{/foreach}
