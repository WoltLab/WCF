{assign var="files" value=$field->getFiles()}
{if $maxUploads === 1 && $imageOnly}
	<div class="fileUpload__preview">
		{if $field->getValue()}
			{assign var="file" value=$files|reset}
			{unsafe:$file->toHtmlElement()}
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
