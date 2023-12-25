<div class="messageTabMenuContent" id="attachments_{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}">
	<button type="button">
		<woltlab-core-file-upload
			data-endpoint="{link controller='FileUpload'}{/link}"
		></woltlab-core-file-upload>
	</button>
	<dl class="wide">
		<dt></dt>
		<dd>
			<div data-max-size="{@$attachmentHandler->getMaxSize()}"></div>
			<small>{lang}wcf.attachment.upload.limits{/lang}</small>
		</dd>
	</dl>
	
	{event name='fields'}
</div>
