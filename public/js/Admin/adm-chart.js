const colors = {
    onTime: "#1a6b45",
    late: "#d4a017",
    completed: "#1a6b45",
    onTrack: "#5aa17e",
    needsAttention: "#d4a017",
    overdue: "#b3382c",
    workloadBar: "#12224a",
    gridLine: "#e9e9e9"
};

fetch("../../Controllers/Admin/adm-dashboard-data.php")
    .then(response => {
        if (!response.ok) {
            throw new Error("Failed to fetch dashboard data.");
        }
        return response.json();
    })
    .then(data => {

        createSubmissionChart(data.submissionData);
        createProgressChart(data.progressData);
        createAdviserChart(data.adviserData);

    })
    .catch(error => {
        console.error("Dashboard Error:", error);
    });


function createSubmissionChart(submissionData) {

    const ctx = document
        .getElementById("submissionStatsChart")
        .getContext("2d");

    new Chart(ctx, {
        type: "bar",
        data: {
            labels: submissionData.labels,
            datasets: [
                {
                    label: "On-time",
                    data: submissionData.onTime,
                    backgroundColor: colors.onTime,
                    borderRadius: 3,
                    maxBarThickness: 28
                },
                {
                    label: "Late",
                    data: submissionData.late,
                    backgroundColor: colors.late,
                    borderRadius: 3,
                    maxBarThickness: 28
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: "bottom",
                    align: "start",
                    labels: {
                        boxWidth: 14
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    suggestedMax: 30,
                    grid: {
                        color: colors.gridLine
                    }
                }
            }
        }
    });

}


function createProgressChart(progressData) {

    const ctx = document
        .getElementById("progressStatsChart")
        .getContext("2d");

    new Chart(ctx, {
        type: "doughnut",
        data: {
            labels: progressData.labels,
            datasets: [
                {
                    data: progressData.values,
                    backgroundColor: [
                        colors.completed,
                        colors.onTrack,
                        colors.needsAttention,
                        colors.overdue
                    ],
                    borderColor: "#fff",
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: "60%",
            plugins: {
                legend: {
                    position: "bottom",
                    labels: {
                        boxWidth: 14
                    }
                }
            }
        }
    });

}

function createAdviserChart(adviserData) {

    const ctx = document
        .getElementById("adviserWorkloadChart")
        .getContext("2d");

    new Chart(ctx, {
        type: "bar",
        data: {
            labels: adviserData.labels,
            datasets: [
                {
                    label: "Assigned Groups",
                    data: adviserData.groups,
                    backgroundColor: colors.workloadBar,
                    borderRadius: 6,
                    barThickness: 14
                }
            ]
        },
        options: {
            indexAxis: "y",
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    suggestedMax: 3,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: colors.gridLine
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

}