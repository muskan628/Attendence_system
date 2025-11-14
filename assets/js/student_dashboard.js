// OVERALL ATTENDANCE CIRCLE
document.addEventListener("DOMContentLoaded", () => {

    const overallChart = new Chart(document.getElementById("overallChart"), {
        type: 'doughnut',
        data: {
            labels: ["Attendance"],
            datasets: [{
                data: [78, 22],
                backgroundColor: ["#ffffff", "rgba(255,255,255,0.2)"],
                borderWidth: 4
            }]
        },
        options: {
            cutout: "70%",
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        }
    });

    // Text in center
    Chart.defaults.font.size = 22;
    Chart.defaults.color = "white";

    // COURSE-WISE SMALL CHARTS
    document.querySelectorAll(".courseChart").forEach((canvas) => {

        const percent = canvas.getAttribute("data-percent");

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [percent, 100 - percent],
                    backgroundColor: ["#008cff", "#e0e6ef"],
                    borderWidth: 4
                }]
            },
            options: {
                cutout: "70%",
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
    });

});
