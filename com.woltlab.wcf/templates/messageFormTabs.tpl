<div class="messageTabMenu">
	<nav class="messageTabMenuNavigation jsOnly">
		<ul>
			{if MODULE_SMILEY && $__wcf->getSession()->getPermission($permissionCanUseSmilies) && $smileyCategories|count}<li data-name="smilies"><a>{lang}wcf.message.smilies{/lang}</a></li>{/if}
			{if MODULE_ATTACHMENT && $attachmentHandler !== null && $attachmentHandler->canUpload()}<li data-name="attachments"><a>{lang}wcf.attachment.attachments{/lang}</a></li>{/if}
			<li data-name="settings"><a>{lang}wcf.message.settings{/lang}</a></li>
			{if $__showPoll|isset && $__showPoll}<li data-name="poll"><a>{lang}wcf.poll.management{/lang}</a></li>{/if}
			{event name='tabMenuTabs'}
		</ul>
	</nav>
	
	{if MODULE_SMILEY && $__wcf->getSession()->getPermission($permissionCanUseSmilies) && $smileyCategories|count}{include file='messageFormSmilies'}{/if}
	{if MODULE_ATTACHMENT && $attachmentHandler !== null && $attachmentHandler->canUpload()}{include file='messageFormAttachments'}{/if}
	
	{include file='messageFormSettings'}
	{include file='__messageFormPoll'}
	
	{event name='tabMenuContents'}
</div>

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		if (!$.browser.redactor) $('#smiliesTab, #smilies').remove();
		
		$('.redactorMessageOptions > nav > ul > li > a').removeAttr('title');
		
		$('.messageTabMenu').messageTabMenu();
		
		/*$('.redactorMessageOptions > nav > ul > li > a').click(function() {
			var $a = $(this);
			var $p = $a.parent();
			var $h = $p.hasClass('active');
			
			$('.redactorMessageOptions > nav > ul > li').removeClass('active');
			$('.redactorMessageOptions > div, .redactorMessageOptions > fieldset').hide();
			
			if (!$h) {
				$p.addClass('active');
				$('#' + $a.prop('href').replace(/[^#]+#/, '')).show();
			}
			
			return false;
		});
		
		$('.redactorMessageOptions > nav > ul > li > a:eq(0)').trigger('click');
		
		$('.redactorMessageOptions > div:eq(0) > nav > ul > li:eq(0)').addClass('active');*/
	});
	//]]>
</script>