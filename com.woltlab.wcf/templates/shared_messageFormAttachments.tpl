<div class="messageTabMenuContent" id="attachments_{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}">
	{@$attachmentHandler->getHtmlElement()}
	<dl class="wide">
		<dt></dt>
		<dd>
			<div data-max-size="{@$attachmentHandler->getMaxSize()}"></div>
			<small>{lang}wcf.attachment.upload.limits{/lang}</small>
		</dd>
	</dl>

	<script data-relocate="true">
		require(["WoltLabSuite/Core/Component/Attachment/List"], ({ setup }) => {
			setup("{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}");
		});
	</script>
	
	{event name='fields'}
</div>
