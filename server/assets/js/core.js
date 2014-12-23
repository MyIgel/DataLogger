var url = '/api/v1';

function updateChart(chart, rangeFrom, rangeTo) {
	
	var sensorDataset = [];
	var sensors = getSensorList();
	
	for (var sensor in sensors) {
		sensor = sensors[sensor];
		var sensorData = getSensorData(sensor.sensorId, rangeFrom, rangeTo);
		if(sensorData.length == 0){
			continue;
		}
		
		var data = {
			label: sensor.name,
			data:  sensorData,
			points: { symbol: "circle", fillColor: sensor.options.color },
			color: sensor.options.color
		}
		sensorDataset.push(data);
	}
	
	var options = {
		xaxis: { mode: "time", timezone: "browser", timeformat: "%d.%m.%y, %H:%M:%S", },
		yaxis: {},
		grid: { hoverable: true, clickable: true },
		tooltip: true,
		tooltipOpts: { content: "%s am %x: %y.2°C", shifts: { x: -60, y: 25 } },
		series: {lines: {show: true, fill: true}}
	};
	console.log(sensorDataset);
	return $.plot(chart, sensorDataset, options);
}

function getSensorList(){
	var returnData = [];
	$.ajax({
		url: url+"/list",
		type: "GET",
    	async: false,
		success: function (data) {
			console.log(data);
			returnData = data;
		}
	});
	
	if(returnData.status == "ok" && returnData.sensorList){
		return returnData.sensorList;
	}
	return [];
}

function getSensorData(sensorId, rangeFrom, rangeTo){
	rangeFrom = rangeFrom || (Math.round((new Date()).getTime() / 1000)) - (1.1 * 60 * 60 * 24 * 3);
	rangeTo = rangeTo || "NOW";
	
	var returnData = [];
	
	$.ajax({
		url: url+"/show/temp/" + sensorId + "/" + rangeFrom + "/" + rangeTo,
		type: "GET",
    	async: false,
		success: function (data) {
			console.log(data);
			returnData = data;
		}
	});
	
	if(returnData.status == "ok" && returnData.data){
		return returnData.data;
	}
	return [];
}
