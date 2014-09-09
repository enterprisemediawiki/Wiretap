$(document).ready(function(){

	function getData () {

		var rawData = JSON.parse( $('#wiretap-data').text() );
		var weekly = [];
		var monthly = [];

		for( var i = 0; i < rawData[0].values.length; i++ ) {
			if ( i % 7 === 0 ) {
				weekly.push( rawData[0].values[i].x );
			}
			if ( i % 30 === 0 ) {
				monthly.push( rawData[0].values[i].x );
			}
		}

		return {
			dailyHits : rawData,
			weeklyLabels : weekly,
			monthlyLabels : monthly
		};

		// return [
		// 	{
		// 		key: "Data 1",
		// 		values: [	
		// 			{ x: 1410282000000, y: 1 },
		// 			{ x: 1410368400000, y: .8 },
		// 			{ x: 1410454800000, y: .9 },
		// 			{ x: 1410541200000, y: .5 },
		//			...
		// 		]
		// 	}
		// ];
	}


	nv.addGraph(function() {

		var hitsData = getData();
		console.log( hitsData );
		window.chart = nv.models.lineWithFocusChart();

		chart.xAxis
			// .tickFormat(d3.format(',f'));
			.tickValues( hitsData.weeklyLabels )
			.tickFormat(function(d) {
				return d3.time.format('%x')(new Date(d))
				});

		chart.x2Axis
			.tickValues( hitsData.monthlyLabels )
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