document.addEventListener("DOMContentLoaded", () => {
    console.log("Admin Dashboard Ready!");

    // Example JS action
    document.querySelectorAll("table tr").forEach(row => {
        row.addEventListener("mouseover", () => {
            row.style.background = "#f1faff";
        });

        row.addEventListener("mouseout", () => {
            row.style.background = "white";
        });
    });
});
