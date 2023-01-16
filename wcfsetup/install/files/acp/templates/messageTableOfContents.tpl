<div class="tableOfContentsWrapper">
	<div class="tableOfContentsContainer open mobileForceHide" id="tocContainer-{$idPrefix}">
		<div class="tableOfContentsHeader">
			<span class="tableOfContentsTitle">{lang}wcf.message.toc{/lang}</span>
			<span class="jsOnly">[<a href="#" class="jsTableOfContentsHide">{lang}wcf.message.toc.hide{/lang}</a><a href="#" class="jsTableOfContentsShow">{lang}wcf.message.toc.show{/lang}</a>]</span>
		</div>
		<ol class="tableOfContents tocLevel1">
			{foreach from=$items item=item}
				<li>
					<span class="tocItemTitle"><a href="#{$item->getID()}">{$item->getTitle()}</a></span>
					
					{if $item->hasChildren()}<ol class="tableOfContents tocLevel{@$item->getDepth() + 1}">{else}</li>{/if}
					
					{if !$item->hasChildren() && $item->isLastSibling()}
						{@"</ol></li>"|str_repeat:$item->getOpenParentNodes()}
					{/if}
			{/foreach}
		</ol>
	</div>
</div>
<script data-relocate="true">
	require(['Ui/Screen'], function(UiScreen) {
		var container = elById('tocContainer-{$idPrefix}');
		elBySelAll('.jsTableOfContentsHide, .jsTableOfContentsShow', container, function(button) {
			button.addEventListener('click', function(event) {
				event.preventDefault();
				
				container.classList.toggle('open');
			});
		});
		
		if (UiScreen.is('screen-sm-down')) {
			container.classList.remove('open');
		}
		
		container.classList.remove('mobileForceHide');
	});
</script>
