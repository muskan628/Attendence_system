// Dashboard Data Update Example
const total = 1200;
const present = 950;
const absent = total - present;
const percent = ((present / total) * 100).toFixed(2);

// Update text dynamically
document.getElementById("totalStudents").textContent = total;
document.getElementById("presentStudents").textContent = present;
document.getElementById("absentStudents").textContent = absent;

