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
        <dd>{$licenseNumber}</dd>
    </dl>
    <dl>
        <dt>TODO: License Type</dt>
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
                </tr>
            </thead>
            <tbody>
                {foreach from=$licenseData[woltlab] key=package item=majorVersion}
                    <tr>
                        <td>{$package}</td>
                        <td>{$majorVersion}</td>
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
                </tr>
            </thead>
            <tbody>
                {foreach from=$licenseData[pluginstore] key=package item=majorVersion}
                    <tr>
                        <td>{$package}</td>
                        <td>{$majorVersion}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</section>

{include file='footer'}
