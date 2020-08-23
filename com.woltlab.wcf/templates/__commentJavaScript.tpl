<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.comment.add': '{jslang}wcf.comment.add{/jslang}',
			'wcf.comment.button.response.add': '{jslang}wcf.comment.button.response.add{/jslang}',
			'wcf.comment.delete.confirmMessage': '{jslang}wcf.comment.delete.confirmMessage{/jslang}',
			'wcf.comment.description': '{jslang}wcf.comment.description{/jslang}',
			'wcf.comment.guestDialog.title': '{jslang}wcf.comment.guestDialog.title{/jslang}',
			'wcf.comment.more': '{jslang}wcf.comment.more{/jslang}',
			'wcf.comment.response.add': '{jslang}wcf.comment.response.add{/jslang}',
			'wcf.comment.response.more': '{jslang}wcf.comment.response.more{/jslang}',
			'wcf.message.error.editorAlreadyInUse': '{jslang}wcf.message.error.editorAlreadyInUse{/jslang}',
			'wcf.moderation.report.reportContent': '{jslang}wcf.moderation.report.reportContent{/jslang}',
			'wcf.moderation.report.success': '{jslang}wcf.moderation.report.success{/jslang}'
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
