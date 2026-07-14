const chart = echarts.init(document.getElementById("chart"));

const docuSymbol =
  "path://M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M4.5 9a.5.5 0 0 1 0-1h7a.5.5 0 0 1 0 1zM4 10.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 1 0-1h4a.5.5 0 0 1 0 1z";

const data = [
  { name: "CCRESME", value: 80 },
  { name: "THSCC02", value: 53 },
  { name: "THSCC03", value: 33 },
];

const option = {
  title: {
    text: "Handled Courses",
    left: "center",
    textStyle: { fontSize: 16, color: "#FFFF" },
    itemGap: 6,
  },
  grid: { left: 80, right: 30, top: 60, bottom: 40 },
  tooltip: { formatter: (p) => `${p.name}: ${p.value}%` },
  xAxis: {
    max: 100,
    splitLine: { show: false },
    axisLabel: { show: false },
    axisTick: { show: false },
    axisLine: { show: false },
  },
  yAxis: {
    data: data.map((d) => d.name),
    axisTick: { show: false },
    axisLine: { show: false },
  },
  series: [
    {
      name: "value",
      type: "pictorialBar",
      symbol: docuSymbol,
      symbolRepeat: "fixed",
      symbolMargin: "10%",
      symbolClip: true,
      symbolSize: ["70%", "90%"],
      symbolBoundingData: 100,
      data: data.map((d) => d.value),
      z: 10,
      itemStyle: { color: "#5B8FF9" },
    },
    {
      name: "background",
      type: "pictorialBar",
      symbol: docuSymbol,
      symbolRepeat: "fixed",
      symbolMargin: "10%",
      symbolSize: ["70%", "90%"],
      symbolBoundingData: 100,
      animationDuration: 0,
      data: data.map(() => 100),
      itemStyle: { color: "#eee" },
      z: 5,
    },
  ],
};

chart.setOption(option);
window.addEventListener("resize", () => chart.resize());
