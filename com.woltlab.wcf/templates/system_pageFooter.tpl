{if !$errorField|empty || !$errorType|empty}
	<!--
		DEBUG: FORM_VALIDATION_FAILED
		
		errorField: {if $errorField|empty}(empty){else}{$errorField|print_r:true}{/if}
		
		errorType: {if $errorType|empty}(empty){else}{$errorType|print_r:true}{/if}
	
	-->
	{if ENABLE_DEBUG_MODE}
		<script>
			console.debug('FORM_VALIDATION_FAILED');
			console.debug('errorField:', '{if $errorField|empty}(empty){else}{$errorField|json}{/if}');
			console.debug('errorType:', '{if $errorType|empty}(empty){else}{$errorType|json}{/if}');
		</script>
	{/if}
{/if}

{capture assign='__pageFooterBoxes'}
	{if !$boxesFooter|empty}
		{unsafe:$boxesFooter}
	{/if}

	{foreach from=$__wcf->getBoxHandler()->getBoxes('footer') item=box}
		{unsafe:$box->render()}
	{/foreach}
{/capture}
{assign var='__pageFooterBoxes' value=$__pageFooterBoxes|trim}

{capture assign='__pageFooterCopyright'}
	{event name='footerContents'}

	{if ENABLE_BENCHMARK}
		{include file='shared_benchmark'}
	{/if}

	{include file='pageFooterCopyright'}
{/capture}
{assign var='__pageFooterCopyright' value=$__pageFooterCopyright|trim}

{capture assign='__pageFooterPreferences'}
	{if $__wcf->getStyleHandler()->showColorSchemeSelector()}
		{* The browser keeps the choice of a guest, `ColorSchemeSwitch` checks it. *}
		{if $__wcf->user->isGuest()}
			{assign var='__pageFooterColorScheme' value=''}
		{else}
			{assign var='__pageFooterColorScheme' value=$__wcf->getStyleHandler()->getColorScheme()}
		{/if}
		<fieldset class="pageFooterScheme jsOnly">
			<legend class="visuallyHidden">{lang}wcf.style.setColorScheme{/lang}</legend>
			<label class="pageFooterScheme__option" title="{lang}wcf.style.setColorScheme.system{/lang}">
				<input type="radio" class="pageFooterScheme__input" name="pageFooterColorScheme" value="system"{if $__pageFooterColorScheme === 'system'} checked{/if}>
				{icon name='circle-half-stroke'}
				<span class="visuallyHidden">{lang}wcf.style.setColorScheme.system{/lang}</span>
			</label>
			<label class="pageFooterScheme__option" title="{lang}wcf.style.setColorScheme.light{/lang}">
				<input type="radio" class="pageFooterScheme__input" name="pageFooterColorScheme" value="light"{if $__pageFooterColorScheme === 'light'} checked{/if}>
				{icon name='sun'}
				<span class="visuallyHidden">{lang}wcf.style.setColorScheme.light{/lang}</span>
			</label>
			<label class="pageFooterScheme__option" title="{lang}wcf.style.setColorScheme.dark{/lang}">
				<input type="radio" class="pageFooterScheme__input" name="pageFooterColorScheme" value="dark"{if $__pageFooterColorScheme === 'dark'} checked{/if}>
				{icon name='moon'}
				<span class="visuallyHidden">{lang}wcf.style.setColorScheme.dark{/lang}</span>
			</label>
		</fieldset>
	{/if}

	{if $__wcf->getStyleHandler()->showStyleChanger()}
		<button type="button" class="pageFooterPreferences__button jsButtonStyleChanger jsOnly">
			{icon name='palette'}
			<span>{lang}wcf.style.changeStyle{/lang}</span>
		</button>
	{/if}
{/capture}
{assign var='__pageFooterPreferences' value=$__pageFooterPreferences|trim}

{capture assign='__pageFooterAd'}
	{if MODULE_WCF_AD && $__disableAds|empty}
		{unsafe:$__wcf->getAdHandler()->getAds('com.woltlab.wcf.footer.bottom')}
	{/if}
{/capture}
{assign var='__pageFooterAd' value=$__pageFooterAd|trim}

{if $__pageFooterBoxes || $__pageFooterCopyright || $__pageFooterPreferences || $__pageFooterAd}
	<footer id="pageFooter" class="pageFooter pageFooter--compact">
		{if $__pageFooterBoxes}
			<div class="pageFooter__part pageFooter__part--main">
				<div class="layoutBoundary">
					<div class="boxContainer">
						{unsafe:$__pageFooterBoxes}
					</div>
				</div>
			</div>
		{/if}

		{if $__pageFooterCopyright || $__pageFooterPreferences || $__pageFooterAd}
			<div class="pageFooter__part pageFooter__part--bottom{if !$__pageFooterCopyright && !$__pageFooterAd} jsOnly{/if}">
				<div class="layoutBoundary">
					{if $__pageFooterCopyright || $__pageFooterPreferences}
						<div class="pageFooter__bottom">
							{if $__pageFooterCopyright}
								<div id="pageFooterCopyright" class="pageFooter__copyright">
									{unsafe:$__pageFooterCopyright}
								</div>
							{/if}

							{if $__pageFooterPreferences}
								<div class="pageFooterPreferences jsOnly">
									{unsafe:$__pageFooterPreferences}
								</div>
							{/if}
						</div>
					{/if}

					{if $__pageFooterAd}
						<div class="pageFooter__ad">
							{unsafe:$__pageFooterAd}
						</div>
					{/if}
				</div>
			</div>
		{/if}
	</footer>
{/if}
