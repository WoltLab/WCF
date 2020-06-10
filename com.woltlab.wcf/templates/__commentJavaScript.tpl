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
		{if MODULE_LIKE && $commentList->getCommentManager()->supportsLike() && $__wcf->getSession()->getPermission('user.like.canViewLike')}
			require(['WoltLabSuite/Core/Ui/Like/Handler'], function(UiLikeHandler) {
				var canDislike = {if LIKE_ENABLE_DISLIKE}true{else}false{/if};
				var canLike = {if $__wcf->getUser()->userID && $__wcf->getSession()->getPermission('user.like.canLike')}true{else}false{/if};
				var canLikeOwnContent = {if LIKE_ALLOW_FOR_OWN_CONTENT}true{else}false{/if};
				
				new UiLikeHandler('com.woltlab.wcf.comment', {
					// settings
					badgeClassNames: 'separatorLeft',
					markListItemAsActive: true,
					renderAsButton: false,
					
					// permissions
					canDislike: canDislike,
					canLike: canLike,
					canLikeOwnContent: canLikeOwnContent,
					canViewSummary: false,
					
					// selectors
					badgeContainerSelector: '.commentContent:not(.commentResponseContent) > .containerHeadline > h3',
					buttonAppendToSelector: '.commentContent .buttonList',
					containerSelector: '#{@$commentContainerID} .comment',
					summarySelector: ''
				});
				
				new UiLikeHandler('com.woltlab.wcf.comment.response', {
					// settings
					badgeClassNames: 'separatorLeft',
					markListItemAsActive: true,
					renderAsButton: false,
					
					// permissions
					canDislike: canDislike,
					canLike: canLike,
					canLikeOwnContent: canLikeOwnContent,
					canViewSummary: false,
					
					// selectors
					badgeContainerSelector: '.commentResponseContent > .containerHeadline > h3',
					buttonAppendToSelector: '.commentContent .buttonList',
					containerSelector: '#{@$commentContainerID} .commentResponse',
					summarySelector: ''
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
