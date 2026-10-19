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

	{* Members only switch the language through the developer tools. *}
	{if $__wcf->getLanguage()->getLanguages()|count > 1 && ($__wcf->user->isGuest() || (ENABLE_DEBUG_MODE && ENABLE_DEVELOPER_TOOLS))}
		<div class="pageFooterLanguage jsOnly">
			<button
				type="button"
				class="pageFooterPreferences__button pageFooterLanguage__toggle"
				aria-haspopup="dialog"
				aria-expanded="false"
				aria-controls="pageFooterLanguageList"
			>
				<img src="{$__wcf->getLanguage()->getIconPath()}" alt="" class="iconFlag">
				<span><span class="visuallyHidden">{lang}wcf.user.language{/lang} </span>{$__wcf->getLanguage()}</span>
				{icon name='chevron-up'}
			</button>
			<div class="pageFooterLanguage__backdrop" hidden></div>
			<div id="pageFooterLanguageList" class="pageFooterLanguage__popover" role="dialog" aria-label="{lang}wcf.user.language{/lang}" tabindex="-1" hidden>
				<div class="pageFooterLanguage__head">
					<span class="pageFooterLanguage__title">{lang}wcf.user.language{/lang}</span>
					<button type="button" class="pageFooterLanguage__close" title="{lang}wcf.global.button.close{/lang}" aria-label="{lang}wcf.global.button.close{/lang}">
						{icon name='xmark'}
					</button>
				</div>
				<ul class="pageFooterLanguage__list">
					{foreach from=$__wcf->getLanguage()->getLanguages() item=_language}
						<li>
							<button
								type="button"
								class="pageFooterLanguage__item"
								data-switch-language="{$_language->languageID}"
								data-language-code="{$_language->languageCode}"
								lang="{$_language->languageCode}"
								{if $_language->languageID === $__wcf->getLanguage()->languageID} aria-current="true"{/if}
							>
								<img src="{$_language->getIconPath()}" alt="" class="iconFlag">
								<span>{$_language}</span>
								<span class="pageFooterLanguage__check">{icon name='check'}</span>
							</button>
						</li>
					{/foreach}
				</ul>
			</div>
		</div>
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
