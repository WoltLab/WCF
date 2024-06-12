{unsafe:$fileProcessorHtmlElement}

<script data-relocate="true">
	require(["WoltLabSuite/Core/Form/Builder/Field/Controller/FileProcessor"], ({ FileProcessor }) => {
		new FileProcessor(
			'{@$field->getPrefixedId()|encodeJS}',
			[],
		);
	});
</script>
