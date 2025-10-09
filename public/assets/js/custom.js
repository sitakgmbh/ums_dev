document.addEventListener("livewire:init", () => {
    const body = document.body;

    function applyTheme(dark) {
        if (dark) {
            body.setAttribute("data-layout-mode", "dark");
            document.documentElement.setAttribute("data-bs-theme", "dark");
            document.documentElement.classList.add("dark-mode");
        } else {
            body.setAttribute("data-layout-mode", "light");
            document.documentElement.setAttribute("data-bs-theme", "light");
            body.classList.remove("dark-mode");
        }
    }

    Livewire.on("theme-changed", (event) => {
        applyTheme(event.dark);
    });
});
