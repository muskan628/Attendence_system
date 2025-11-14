document.addEventListener("DOMContentLoaded", function () {

    // CHART (Demo Bar Graph)
    const chartCanvas = document.getElementById("chart").getContext("2d");

    new Chart(chartCanvas, {
        type: "bar",
        data: {
            labels: ["W1", "W2", "W3", "W4"],
            datasets: [{
                label: "Attendance Count",
                data: [180, 150, 170, 160],
                backgroundColor: "rgba(0, 122, 255, 0.7)"
            }]
        },
        options: {
            responsive: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

});
