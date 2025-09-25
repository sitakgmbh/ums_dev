document.addEventListener("DOMContentLoaded", function () {
    // Body Element
    const body = document.body;

    // Lies die Config vom data-layout-config Attribut
    const configRaw = body.getAttribute("data-layout-config");
    let config = {};
    try {
        config = JSON.parse(configRaw);
    } catch (e) {
        console.error("Konnte data-layout-config nicht parsen:", e);
    }

    // Darkmode anwenden
    if (config.darkMode) {
        body.setAttribute("data-layout-mode", "dark");
        document.documentElement.setAttribute("data-bs-theme", "dark");
    } else {
        body.setAttribute("data-layout-mode", "light");
        document.documentElement.setAttribute("data-bs-theme", "light");
    }
});
