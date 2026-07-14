// BULLET CHART: initialize the chart on the #bulletchart div (see HO4.html)
const bulletChart = echarts.init(document.getElementById("bulletchart"));

// ===== EDIT HERE: your data =====
// name:    category label (e.g. group/team name)
// ranges:  [low, medium, high] qualitative background bands, ascending.
//          The last number sets the axis max (e.g. 100 = percent scale).
// measure: the actual/current value -> drawn as the thick colored bar
// target:  the goal value -> drawn as a vertical tick mark
const bulletData = [
  { name: "Group 1", ranges: [40, 70, 100], measure: 55, target: 65 },
  { name: "Group 2", ranges: [40, 70, 100], measure: 82, target: 75 },
  { name: "Group 3", ranges: [40, 70, 100], measure: 30, target: 50 },
];
// ===== END EDIT =====

const categories = bulletData.map((d) => d.name);
const axisMax = Math.max(
  ...bulletData.map((d) => d.ranges[d.ranges.length - 1]),
);

const bulletOption = {
  title: {
    text: "Submission Progress", // EDIT HERE: chart title
    left: "center",
    textStyle: { fontSize: 16, color: "#FFFF" },
  },
  tooltip: {
    trigger: "axis",
    axisPointer: { type: "shadow" },
    formatter: (params) => {
      const d = bulletData[params[0].dataIndex];
      return `${d.name}<br/>Progress: ${d.measure}<br/>Target: ${d.target}`;
    },
  },
  grid: { left: 90, right: 30, top: 60, bottom: 30 },
  xAxis: {
    max: axisMax,
    splitLine: { show: false },
  },
  yAxis: {
    type: "category",
    data: categories,
    axisTick: { show: false },
  },
  series: [
    // EDIT HERE: qualitative background ranges (widest band drawn first, so
    // narrower bands layer on top of it — same barWidth, different length)
    {
      name: "High range",
      type: "bar",
      barWidth: 22,
      data: bulletData.map((d) => d.ranges[2]),
      itemStyle: { color: "#eee" },
      barGap: "-100%",
      z: 1,
      silent: true, // no tooltip/hover on background bands
    },
    {
      name: "Medium range",
      type: "bar",
      barWidth: 22,
      data: bulletData.map((d) => d.ranges[1]),
      itemStyle: { color: "#ddd" },
      barGap: "-100%",
      z: 2,
      silent: true,
    },
    {
      name: "Low range",
      type: "bar",
      barWidth: 22,
      data: bulletData.map((d) => d.ranges[0]),
      itemStyle: { color: "#ccc" },
      barGap: "-100%",
      z: 3,
      silent: true,
    },
    // EDIT HERE: the actual measure — a slim bar overlaid on the ranges
    {
      name: "Progress",
      type: "bar",
      barWidth: 8,
      data: bulletData.map((d) => d.measure),
      itemStyle: { color: "#5B8FF9" },
      barGap: "-100%",
      z: 4,
    },
    // EDIT HERE: the target — a vertical tick mark at the goal value
    {
      name: "Target",
      type: "scatter",
      symbol: "rect",
      symbolSize: [3, 24],
      data: bulletData.map((d, i) => [d.target, i]),
      itemStyle: { color: "#333" },
      z: 5,
    },
  ],
};

bulletChart.setOption(bulletOption);
window.addEventListener("resize", () => bulletChart.resize());
