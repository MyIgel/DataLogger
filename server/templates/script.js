/**
 * Parse and update data
 */

var flotTemp = $('#flotTemp');
var plot = $.plot(flotTemp,
    [],
    {
        xaxis: {mode: "time", timezone: "browser", timeformat: "%d.%m.%y, %H:%M:%S"},
        yaxis: {},
        grid: {hoverable: true, clickable: true},
        tooltip: true, tooltipOpts: {content: "%s am %x: %y.2°C", shifts: {x: -60, y: 25}},
        series: {lines: {show: true, fill: true}}
    }
);

updateChart(flotTemp, parseInt('/*FROM*/'));

setInterval(
    function () {
        updateChart(flotTemp, parseInt('/*FROM*/'))
    },
    120 * 1000
);
