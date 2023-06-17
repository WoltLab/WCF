{include file='header' pageTitle='wcf.acp.license'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.license{/lang}</span></h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

<section class="section">
    <h1 class="sectionTitle">TODO: License</h1>

    <dl>
        <dt>TODO: License Number</dt>
        {* TODO: The WoltLab Cloud does not really follows the concept of
           license numbers therefore this needs to be hidden for these. *}
        <dd>{$licenseNumber}</dd>
    </dl>
    <dl>
        <dt>TODO: License Type</dt>
        {* TODO: Do we want to display additional information depending on the
           license type? This could be useful for WoltLab Cloud installations
           to show the subscription name. But this could also show a reminder
           for developer licenses that they only qualify for demo/testing
           purpose and not for production sites. *}
        <dd>{$licenseData[license][type]}</dd>
    </dl>
</section>

<section class="section">
    <h1 class="sectionTitle">TODO: WoltLab Products</h1>

    <div class="section tabularBox">
        <table class="table">
            <thead>
                <tr>
                    <th>TODO: Name</th>
                    <th>TODO: Major Version</th>
                    <th>TODO: Action</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$licenseData[woltlab] key=package item=majorVersion}
                    <tr>
                        {* TODO: Install button? *}

                        <td>{$package}</td>
                        <td>{$majorVersion}</td>

                        {* TODO: We might want to suggest upgrading the license
                           if we can determine that this is an outdated version.
                           Maybe we can deeplink the LicenseExtend page here? *}

                        <td>
                            {if $package|in_array:$installedPackages}TODO: Installed.{/if}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</section>

<section class="section">
    <h1 class="sectionTitle">TODO: WoltLab Plugin-Store</h1>

    <div class="section tabularBox">
        <table class="table">
            <thead>
                <tr>
                    <th>TODO: Name</th>
                    <th>TODO: Major Version</th>
                    <th>TODO: Action</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$licenseData[pluginstore] key=package item=majorVersion}
                    <tr>
                        {* TODO: Install button? *}

                        <td>{$package}</td>
                        <td>{$majorVersion}</td>

                        {* TODO: Are we able to suggest if there is a newer
                           version available? And if yes, how do we display this
                           without breaking the version filter? Or does it only
                           affect the availibility of the install button and not
                           the row itself? *}
                        
                        {* TODO: Do we want to display the time of purchase here? *}

                        <td>
                            {if $package|in_array:$installedPackages}TODO: Installed.{/if}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</section>

{include file='footer'}
