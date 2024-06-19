{assign var="files" value=$field->getFiles()}
{if $field->isSingleFileUpload() === 1 && $imageOnly}
	<div class="fileUpload__preview">
		{if $field->getValue()}
			{assign var="file" value=$files|reset}
			{unsafe:$file->toHtmlElement()}
		{/if}
	</div>
{else}
	<ul class="fileUpload__fileList">
		{foreach from=$files item=file}
			<li>
				{unsafe:$file->toHtmlElement()}
			</li>
		{/foreach}
	</ul>
{/if}

{unsafe:$fileProcessorHtmlElement}

<script data-relocate="true">
	require(["WoltLabSuite/Core/Form/Builder/Field/Controller/FileProcessor"], ({ FileProcessor }) => {
		new FileProcessor(
			'{@$field->getPrefixedId()|encodeJS}',
			{if $field->isSingleFileUpload()}true{else}false{/if},
			{if $imageOnly}true{else}false{/if}
		);
	});
</script>
