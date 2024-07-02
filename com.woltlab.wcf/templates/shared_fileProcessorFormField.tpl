{unsafe:$fileProcessorHtmlElement}

{assign var="files" value=$field->getFiles()}
{if $field->isSingleFileUpload() && $imageOnly}
	<div class="fileUpload__preview">
		{if $field->getValue()}
			{assign var="file" value=$files|reset}
			{unsafe:$file->toHtmlElement()}
		{/if}
	</div>
{else}
	<ul class="fileList">
		{foreach from=$files item=file}
			<li class="fileList__item">
				{unsafe:$file->toHtmlElement()}
			</li>
		{/foreach}
	</ul>
{/if}

<script data-relocate="true">
	require(["WoltLabSuite/Core/Form/Builder/Field/Controller/FileProcessor"], ({ FileProcessor }) => {
		new FileProcessor(
			'{unsafe:$field->getPrefixedId()|encodeJS}',
			{if $field->isSingleFileUpload()}true{else}false{/if},
			{if $imageOnly}true{else}false{/if},
			[{implode from=$actionButtons item=actionButton}{
				title: '{unsafe:$actionButton['title']|encodeJS}',
				icon: {if $actionButton['icon'] === null}undefined{else}'{unsafe:$actionButton['icon']->toHtml()|encodeJS}'{/if},
				actionName: '{unsafe:$actionButton['actionName']|encodeJS}',
			}{/implode} ],
		);
	});

	{foreach from=$actionButtons item=actionButton}
		{include application=$actionButton['application'] file=$actionButton['template']}
	{/foreach}
</script>
