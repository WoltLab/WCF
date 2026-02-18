<div class="tableOfContents__wrapper">
	<nav class="tableOfContents__container" aria-labelledby="tocTitle-{$idPrefix}">
		<div class="tableOfContents__header">
			<h2 class="tableOfContents__title" id="tocTitle-{$idPrefix}">{lang}wcf.message.toc{/lang}</h2>
			
			<button type="button" 
				aria-expanded="true" 
				aria-controls="toc-{$idPrefix}" 
				class="tableOfContents__toggle button small"
				id="tocToggle-{$idPrefix}"
			>
				{lang}wcf.message.toc.hide{/lang}
			</button>
		</div>
		<ul class="tableOfContents tableOfContents--level1" id="toc-{$idPrefix}">
			{foreach from=$items item=item}
				<li>
					<a class="tableOfContents__item" href="#{$item->getID()}">{$item->getTitle()}</a>
					
					{if $item->hasChildren()}<ul class="tableOfContents tableOfContents--level{$item->getDepth() + 1}">{else}</li>{/if}
					
					{if !$item->hasChildren() && $item->isLastSibling()}
						{unsafe:"</ul></li>"|str_repeat:$item->getOpenParentNodes()}
					{/if}
			{/foreach}
		</ul>
	</nav>
	<script>
		{
			const button = document.getElementById('tocToggle-{$idPrefix}');
			button.addEventListener('click', () => {
				toggle();
			});
			
			function toggle() {
				const hidden = button.getAttribute('aria-expanded') === 'true';
				button.setAttribute('aria-expanded', hidden ? 'false' : 'true');
				button.textContent = hidden ? '{jslang}wcf.message.toc.show{/jslang}' : '{jslang}wcf.message.toc.hide{/jslang}';
				document.getElementById(button.getAttribute('aria-controls')).hidden = hidden;
			}
			
			if (window.matchMedia('(max-width: 768px)').matches) {
				toggle();
			}
		}
	</script>
</div>
