<div id="pageHeaderLogo" class="pageHeaderLogo">
	{if MODULE_WCF_AD && $__disableAds|empty}{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.logo')}{/if}
	
	<a href="{link}{/link}">
		<img src="{$__wcf->getStyleHandler()->getStyle()->getPageLogo()}" alt="" class="pageHeaderLogoLarge" style="{if $__wcf->getStyleHandler()->getStyle()->getVariable('pageLogoWidth')}width: {@$__wcf->getStyleHandler()->getStyle()->getVariable('pageLogoWidth')}px;{/if}{if $__wcf->getStyleHandler()->getStyle()->getVariable('pageLogoHeight')}height: {@$__wcf->getStyleHandler()->getStyle()->getVariable('pageLogoHeight')}px{/if}">
		<img src="{$__wcf->getStyleHandler()->getStyle()->getPageLogoMobile()}" alt="" class="pageHeaderLogoSmall">
		
		{event name='headerLogo'}
	</a>
</div>