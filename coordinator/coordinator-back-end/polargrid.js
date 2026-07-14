// POLAR CHART: initialize the chart on the #polarchart div
const polarChart = echarts.init(document.getElementById("polarchart"));

// ===== EDIT HERE: your data =====
// Categories of workload assigned to each adviser
const advisers = ["Dr. Joel I.", "Mr. Jonathan C.", "Mr. Clement O."];
const thesisData = [5, 3, 6]; // Number of Thesis groups
const lecturesData = [8, 6, 4]; // Number of Lecture sessions
const researchData = [4, 7, 5]; // Number of Research projects
// ===== END EDIT =====

const polarOption = {
  title: {
    text: "Adviser Workload Breakdown",
    left: "center",
    textStyle: { fontSize: 16, color: "#FFFF" },
  },
  tooltip: {
    trigger: "axis",
    axisPointer: { type: "shadow" },
  },
  legend: {
    data: ["Thesis", "Lectures", "Research"],
    bottom: 0,
    icon: "circle",
    textStyle: { fontSize: 12, color: "#FFFF" },
  },
  // Define the polar coordinate grid geometry
  polar: {
    center: ["50%", "50%"],
    radius: "80%",
  },
  // Angle axis maps to the categorical values (the advisers)
  angleAxis: {
    type: "category",
    data: advisers,
    startAngle: 90, // Positions the start at the top (12 o'clock)
    axisLabel: {
      color: "#FFFF",
    },
  },
  // Radius axis handles the numerical workload values
  radiusAxis: {
    min: 0,
    splitLine: {
      lineStyle: { color: "#eee" },
    },
  },
  series: [
    {
      type: "bar",
      name: "Thesis Groups",
      data: thesisData,
      coordinateSystem: "polar",
      stack: "workload", // Stacking series makes it a compact ring group
      itemStyle: { color: "#5B8FF9" }, // Matches your primary brand blue
    },
    {
      type: "bar",
      name: "Lectures",
      data: lecturesData,
      coordinateSystem: "polar",
      stack: "workload",
      itemStyle: { color: "#61D4A7" },
    },
    {
      type: "bar",
      name: "Research",
      data: researchData,
      coordinateSystem: "polar",
      stack: "workload",
      itemStyle: { color: "#FFB800" },
    },
  ],
};

polarChart.setOption(polarOption);
window.addEventListener("resize", () => polarChart.resize());
