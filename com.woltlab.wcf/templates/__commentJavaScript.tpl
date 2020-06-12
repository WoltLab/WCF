<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.comment.add': '{lang}wcf.comment.add{/lang}',
			'wcf.comment.button.response.add': '{lang}wcf.comment.button.response.add{/lang}',
			'wcf.comment.delete.confirmMessage': '{lang}wcf.comment.delete.confirmMessage{/lang}',
			'wcf.comment.description': '{lang}wcf.comment.description{/lang}',
			'wcf.comment.guestDialog.title': '{lang}wcf.comment.guestDialog.title{/lang}',
			'wcf.comment.more': '{lang}wcf.comment.more{/lang}',
			'wcf.comment.response.add': '{lang}wcf.comment.response.add{/lang}',
			'wcf.comment.response.more': '{lang}wcf.comment.response.more{/lang}',
			'wcf.message.error.editorAlreadyInUse': '{lang}wcf.message.error.editorAlreadyInUse{/lang}',
			'wcf.moderation.report.reportContent': '{lang}wcf.moderation.report.reportContent{/lang}',
			'wcf.moderation.report.success': '{lang}wcf.moderation.report.success{/lang}'
		});
		
		new {if $commentHandlerClass|isset}{@$commentHandlerClass}{else}WCF.Comment.Handler{/if}('{@$commentContainerID}');
		{if MODULE_LIKE && $commentList->getCommentManager()->supportsLike() && $__wcf->getSession()->getPermission('user.like.canViewLike') || $__wcf->getSession()->getPermission('user.like.canLike')}
			require(['WoltLabSuite/Core/Ui/Reaction/Handler'], function(UiReactionHandler) {
				new UiReactionHandler('com.woltlab.wcf.comment', {
					// selectors
					containerSelector: '#{@$commentContainerID} li.comment',
					summaryListSelector: '.reactionSummaryList',
					isButtonGroupNavigation: true
				});
				
				new UiReactionHandler('com.woltlab.wcf.comment.response', {
					// selectors
					containerSelector: '#{@$commentContainerID} .commentResponse',
					summaryListSelector: '.reactionSummaryList',
					isButtonGroupNavigation: true
				});
			});
		{/if}
		
		{if $commentList->getCommentManager()->supportsReport() && $__wcf->session->getPermission('user.profile.canReportContent')}
			new WCF.Moderation.Report.Content('com.woltlab.wcf.comment.comment', '#{@$commentContainerID} .jsReportCommentComment');
			new WCF.Moderation.Report.Content('com.woltlab.wcf.comment.response', '#{@$commentContainerID} .jsReportCommentResponse');
		{/if}
	});
</script>

{event name='javascriptInclude'}
