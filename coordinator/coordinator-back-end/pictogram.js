const chartDom = document.getElementById("chart");

const docuSymbol =
  "path://M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M4.5 9a.5.5 0 0 1 0-1h7a.5.5 0 0 1 0 1zM4 10.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 1 0-1h4a.5.5 0 0 1 0 1z";

// Use server-provided course data (set by coordinator-overview.php). No more
// hardcoded fallback courses — if the coordinator has none, show an empty state.
const data =
  window.COORDINATOR_COURSE_CHART && window.COORDINATOR_COURSE_CHART.length
    ? window.COORDINATOR_COURSE_CHART
    : [];

// Read the theme-aware text color from CSS (defined in coordinator.css as
// --chart-text-color, overridden under [data-theme="dark"]). This keeps the
// color choice in one place instead of duplicating hex values in JS, and
// guarantees the chart matches whatever the rest of the UI is using.
function getChartTextColor() {
  const value = getComputedStyle(document.documentElement)
    .getPropertyValue("--chart-text-color")
    .trim();
  return value || "#212529";
}

function buildOption() {
  const textColor = getChartTextColor();

  return {
    title: {
      text: "Handled Courses",
      left: "center",
      textStyle: { fontSize: 16, color: textColor },
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
      axisLabel: { color: textColor },
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
}

if (!data.length) {
  // No courses assigned — mirror the empty state used by the Handled Courses card
  chartDom.innerHTML =
    '<p class="text-center text-muted mt-5">No courses assigned</p>';
} else {
  const chart = echarts.init(chartDom);
  chart.setOption(buildOption());

  window.addEventListener("resize", () => chart.resize());

  // Primary mechanism: theme.js dispatches this on every toggle (and once on
  // load), so the chart recolors deterministically the moment the theme changes.
  window.addEventListener("themechange", () => {
    chart.setOption(buildOption());
  });

  // Fallback in case data-theme is changed by something other than theme.js.
  const themeObserver = new MutationObserver(() => {
    chart.setOption(buildOption());
  });
  themeObserver.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ["data-theme"],
  });
}
