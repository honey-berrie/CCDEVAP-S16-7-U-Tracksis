// POLAR CHART: initialize the chart on the #polarchart div
const polarChart = echarts.init(document.getElementById("polarchart"));

async function loadPolarData() {
  try {
    const res = await fetch("../../api/coordinator/polargrid_data.php", {
      credentials: "same-origin",
    });
    const text = await res.text();
    let json = null;
    try {
      json = text ? JSON.parse(text) : null;
    } catch (e) {
      json = null;
    }
    if (!res.ok || !json || !json.success) {
      console.warn("Failed to load polar data", text || res.statusText);
      polarChart.setOption({
        title: { text: "Adviser Workload Breakdown (no data)", left: "center" },
      });
      return;
    }

    const advisers = json.advisers || [];
    const thesisData = json.thesisData || [];

    const option = {
      title: {
        text: "Adviser Thesis Load",
        left: "center",
        textStyle: { fontSize: 16, color: "#FFFF" },
      },
      tooltip: { trigger: "axis", axisPointer: { type: "shadow" } },
      legend: {
        data: ["Thesis"],
        bottom: 0,
        icon: "circle",
        textStyle: { fontSize: 12, color: "#FFFF" },
      },
      polar: { center: ["50%", "50%"], radius: "80%" },
      angleAxis: {
        type: "category",
        data: advisers,
        startAngle: 90,
        axisLabel: { color: "#FFFF" },
      },
      radiusAxis: { min: 0, splitLine: { lineStyle: { color: "#eee" } } },
      series: [
        {
          type: "bar",
          name: "Thesis",
          data: thesisData,
          coordinateSystem: "polar",
          itemStyle: { color: "#5B8FF9" },
        },
      ],
    };

    polarChart.setOption(option);
  } catch (err) {
    console.error("Error loading polar chart data", err);
    polarChart.setOption({
      title: { text: "Adviser Thesis Load (error)", left: "center" },
    });
  }
}

loadPolarData();
window.addEventListener("resize", () => polarChart.resize());
