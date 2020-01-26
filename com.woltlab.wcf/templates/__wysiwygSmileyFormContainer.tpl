{include file='__tabTabMenuFormContainer'}

<script data-relocate="true">
	$(function() {
		{if $container->children()|count > 1}
			new WCF.Message.SmileyCategories(
				'{@$container->getPrefixedWysiwygId()}',
				'{@$container->getPrefixedId()}Container',
				true
			);
			
			$('#{@$container->getPrefixedId()}Container').messageTabMenu();
		{/if}
		
		require(['WoltLabSuite/Core/Ui/Smiley/Insert'], function(UiSmileyInsert) {
			new UiSmileyInsert('{@$container->getPrefixedWysiwygId()}');
		});
	});
</script>
