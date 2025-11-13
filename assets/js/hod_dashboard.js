document.addEventListener("DOMContentLoaded", () => {
    console.log("HOD Dashboard Loaded Successfully!");

    document.querySelectorAll(".view-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            alert("View Details Clicked!");
        });
    });
});
