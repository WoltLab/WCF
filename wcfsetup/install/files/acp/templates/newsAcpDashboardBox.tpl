<div class="woltlabNewsfeed woltlabNewsfeed--loading">
	<woltlab-core-loading-indicator size="48"></woltlab-core-loading-indicator>
	<iframe
		class="woltlabNewsfeed__iframe"
		referrerpolicy="no-referrer"
		sandbox="allow-popups allow-popups-to-escape-sandbox"
	></iframe>
</div>

<script data-eager="true">
{
	const languageCode = "{if $__wcf->language->languageCode === 'de'}de{else}en{/if}";
	let colorScheme = document.documentElement.dataset.colorScheme;
	const container = document.querySelector(".woltlabNewsfeed");
	const iframe = container.querySelector(".woltlabNewsfeed__iframe");

	const updateColorScheme = () => {
		container.classList.add("woltlabNewsfeed--loading");
		iframe.addEventListener(
			"load",
			() => container.classList.remove("woltlabNewsfeed--loading"),
			{ once: true }
		);
		iframe.src = `https://newsfeed.woltlab.com/${ languageCode }_${ colorScheme }.html`;
	};

	const observer = new MutationObserver(() => {
		const newScheme = document.documentElement.dataset.colorScheme;
		if (newScheme === "light" || newScheme === "dark") {
			colorScheme = newScheme;
			updateColorScheme();
		}
	});
	observer.observe(document.documentElement, {
		attributeFilter: ["data-color-scheme"]
	});

	updateColorScheme();
}
</script>
