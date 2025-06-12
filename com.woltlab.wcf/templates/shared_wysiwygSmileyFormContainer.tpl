{include file='shared_tabTabMenuFormContainer'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/Smiley/Insert'], function(UiSmileyInsert) {
		new UiSmileyInsert('{unsafe:$container->getPrefixedWysiwygId()|encodeJS}');
	});
</script>
