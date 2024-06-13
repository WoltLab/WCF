{if $maxUploads === 1 && $imageOnly}
	<div class="fileUpload__preview">
		{if $field->value()}
		{*<woltlab-core-file>…</woltlab-core-file>*}
			<ul class="fileUpload__preview__buttons buttonList">
				<li>
					<button class="button small" type="button">
						{lang}wcf.global.button.delete{/lang}
					</button>
				</li>
			</ul>
		{/if}
	</div>
{else}
	<ul class="fileUpload__fileList">
	</ul>
{/if}
{unsafe:$fileProcessorHtmlElement}

<script data-relocate="true">
	require(["WoltLabSuite/Core/Form/Builder/Field/Controller/FileProcessor"], ({ FileProcessor }) => {
		new FileProcessor(
			'{@$field->getPrefixedId()|encodeJS}',
		);
	});
</script>
