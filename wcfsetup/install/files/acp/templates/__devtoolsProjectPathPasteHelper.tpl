<script data-relocate="true">
{
    const name = document.getElementById("name");
    const path = document.getElementById("path");
    path.addEventListener("paste", (event) => {
        if (name.value.trim() !== "") {
            return;
        }

        let value = event.clipboardData.getData("text/plain").trim();
        if (value.match(/.+[/\\]([^/\\]+)[/\\]?$/)) {
            value = RegExp.$1;
            if (value.includes(".")) {
                name.value = value;
            }
        }
    });
}
</script>
