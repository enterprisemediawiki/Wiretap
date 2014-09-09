window.getMovingAverage = function ( dataArray, maLength, weekdaysOnly ) {

	weekdaysOnly = weekdaysOnly || false; 
    var denominator;

    // initialize early datapoints
    var avgArray = [];

    // // initialize current sum
    var curSum = 0;
	var curDays = [];
	var curAvg = 0;

    for ( var i = 0; i < dataArray.length; i++ ) {
		dayOfWeek = new Date( dataArray[ i ].x ).getDay();
	
		if ( weekdaysOnly && (dayOfWeek === 0 || dayOfWeek === 6) ) {
			avgArray[ i ] = {
				x : dataArray[ i ].x,
				y : curAvg
			};
		}
		else {
			curDays.push( dataArray[ i ].y );
			if ( curDays.length > maLength ) {
				curDays.shift(); // shift first element off
			}
			
			curSum = curDays.reduce(function(p,c) { return p + c; });			
			denominator = curDays.length;
			curAvg = curSum / denominator;
			
			avgArray[ i ] = {
				x : dataArray[ i ].x,
				y : curAvg
			};
		}
    }

    return avgArray;

};

$(document).ready(function(){

	/**
		{
			dailyHits : [
				{
					key : "Series 1",
					values : [
						{ x: timestamp, y: value },
						{ x: timestamp, y: value },
						{ x: timestamp, y: value },
						{ x: timestamp, y: value },
						....
					]
				},
				{ key : "Series 2", ... }
			],
			weeklyLabels : [unixtimestamp-milliseconds, ts, ts, ...],
			monthlyLabels : [unixtimestamp-milliseconds, ts, ts, ...]
		}
	 **/
	function getData () {

		var rawData = JSON.parse( $('#wiretap-data').text() );

		rawData[0].color = "#4B70E7";
		
		rawData.push( {
			key: "7-Day Moving Average",
			values: getMovingAverage( rawData[0].values, 7 ),
			color: "#FFBB44"
		} );
		
		rawData.push( {
			key: "28-Day Moving Average",
			values: getMovingAverage( rawData[0].values, 28 ),
			color: "#FF0000"
		} );

		rawData.push( {
			key: "20-Weekday Moving Average (no weekends)",
			values: getMovingAverage( rawData[0].values, 20, true ),
			color: "#00FF00"
		} );

		return { dailyHits : rawData };

	}


	nv.addGraph(function() {

		window.hitsData = getData();
		console.log(hitsData);
		window.chart = nv.models.lineWithFocusChart();

		chart.xAxis
			.tickFormat(function(d) {
				return d3.time.format('%x')(new Date(d))
			});

		chart.x2Axis
			.tickFormat(function(d) {
				return d3.time.format('%x')(new Date(d))
			});

		chart.yAxis
			.tickFormat(d3.format(',.0f'));

		chart.y2Axis
			.tickFormat(d3.format(',.0f'));

		d3.select('#wiretap-chart svg')
			.datum( hitsData.dailyHits )
			.attr( "height" , $(window).height() - 100 )
			.transition().duration(500)
			.call(chart);

		// $("#wiretap-chart svg").height( $(window).height() - 100 );

		nv.utils.windowResize(chart.update);

		return chart;
	});
});