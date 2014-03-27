<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.Comment{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@$__wcfVersion}"></script>
<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.Moderation{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@$__wcfVersion}"></script>
{if !$__wcf->user->userID}
	<script type="text/javascript" src="http{if $__wcf->secureConnection()}s{/if}://www.google.com/recaptcha/api/js/recaptcha_ajax.js"></script>
{/if}
<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.comment.add': '{lang}wcf.comment.add{/lang}',
			'wcf.comment.button.response.add': '{lang}wcf.comment.button.response.add{/lang}',
			'wcf.comment.delete.confirmMessage': '{lang}wcf.comment.delete.confirmMessage{/lang}',
			'wcf.comment.description': '{lang}wcf.comment.description{/lang}',
			'wcf.comment.more': '{lang}wcf.comment.more{/lang}',
			'wcf.comment.response.add': '{lang}wcf.comment.response.add{/lang}',
			'wcf.comment.response.more': '{lang}wcf.comment.response.more{/lang}',
			'wcf.moderation.report.reportContent': '{lang}wcf.moderation.report.reportContent{/lang}',
			'wcf.moderation.report.success': '{lang}wcf.moderation.report.success{/lang}'
		});
		
		new {if $commentHandlerClass|isset}{@$commentHandlerClass}{else}WCF.Comment.Handler{/if}('{$commentContainerID}', '{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32)}');
		{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike')}
			new WCF.Comment.Like({if $__wcf->getUser()->userID && $__wcf->getSession()->getPermission('user.like.canLike')}1{else}0{/if}, {@LIKE_ENABLE_DISLIKE}, false, {@LIKE_ALLOW_FOR_OWN_CONTENT});
			new WCF.Comment.Response.Like({if $__wcf->getUser()->userID && $__wcf->getSession()->getPermission('user.like.canLike')}1{else}0{/if}, {@LIKE_ENABLE_DISLIKE}, false, {@LIKE_ALLOW_FOR_OWN_CONTENT});
		{/if}
		
		new WCF.Moderation.Report.Content('com.woltlab.wcf.comment.comment', '.jsReportCommentComment');
		new WCF.Moderation.Report.Content('com.woltlab.wcf.comment.response', '.jsReportCommentResponse');
	});
	//]]>
</script>

{event name='javascriptInclude'}
