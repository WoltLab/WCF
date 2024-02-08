<script data-relocate="true">
{
	const name = document.getElementById("name");
	const path = document.getElementById("path");
	path.addEventListener("paste", (event) => {
		if (name.value.trim() !== "") {
			return;
		}

		const value = event.clipboardData.getData("text/plain").trim();

		const matches = value.match(/.+[/\\]([^/\\]+)[/\\]?$/);
		if (matches !== null && matches[1].includes(".")) {
			name.value = matches[1];
		}
	});
}
</script>
