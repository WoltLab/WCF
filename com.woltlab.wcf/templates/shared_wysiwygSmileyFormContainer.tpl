{include file='shared_tabTabMenuFormContainer'}

<script data-relocate="true">
	$(function() {
		{if $container->children()|count > 1}
			new WCF.Message.SmileyCategories(
				'{@$container->getPrefixedWysiwygId()|encodeJS}',
				'{@$container->getPrefixedId()|encodeJS}Container',
				true
			);
			
			$('#{@$container->getPrefixedId()|encodeJS}Container').messageTabMenu();
		{/if}
		
		require(['WoltLabSuite/Core/Ui/Smiley/Insert'], function(UiSmileyInsert) {
			new UiSmileyInsert('{@$container->getPrefixedWysiwygId()|encodeJS}');
		});
	});
</script>
