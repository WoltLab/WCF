<div id="pageHeaderLogo" class="pageHeaderLogo">
	{if MODULE_WCF_AD && $__disableAds|empty}{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.logo')}{/if}
	
	<a href="{if PAGE_LOGO_LINK_TO_APP_DEFAULT}{link application=$__wcf->getActiveApplication()->getAbbreviation()}{/link}{else}{link}{/link}{/if}">
		<img src="{$__wcf->getStyleHandler()->getStyle()->getPageLogo()}" alt="" class="pageHeaderLogoLarge"{*
			*}{if $__wcf->getStyleHandler()->getStyle()->getVariable('pageLogoHeight')} height="{@$__wcf->getStyleHandler()->getStyle()->getVariable('pageLogoHeight')}"{/if}{*
			*}{if $__wcf->getStyleHandler()->getStyle()->getVariable('pageLogoWidth')} width="{@$__wcf->getStyleHandler()->getStyle()->getVariable('pageLogoWidth')}"{/if}{*
			*} loading="eager">
		<img src="{$__wcf->getStyleHandler()->getStyle()->getPageLogoMobile()}" alt="" class="pageHeaderLogoSmall" loading="eager">
		
		{event name='headerLogo'}
	</a>
</div>