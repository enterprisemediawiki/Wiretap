var getMovingAverage = function ( dataArray, maLength ) {

    var denominator;

    // initialize early datapoints
    var avgArray = [];
    // for( var i = 0; i < maLength; i++ ) {
    //     avgArray.push( 0 );
    // }

    // // initialize current sum
    var curSum = 0;
    // for ( var i = 0; i < maLength; i++ ) {
    //     curSum += dataArray[ i ];
    // }



    // set first "real" datapoint
    // avgArray[ maLength - 1 ] = curSum / maLength;

    for ( var i = 0; i < dataArray.length; i++ ) {
        curSum = curSum + dataArray[ i ].y; // add in the new value to the moving sum
        
        if ( i - maLength >= 0 ) {
            // subtract oldest value still "part of" moving sum
            // if reached length of moving sum (if have 7 days in
            // 7-day moving sum) 
            curSum = curSum - dataArray[ i - maLength ].y;
        }

        denominator = Math.min( i + 1, maLength );

        avgArray[ i ] = {
        	x : dataArray[ i ].x,
        	y : curSum / denominator
        };

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

		rawData.push( {
			key : "7-Day Moving Average",
			values : getMovingAverage( rawData[0], 7 )
		} );
		rawData.push( {
			key: "30-Day Moving Average",
			values: getMovingAverage( rawData[0], 30 )
		} );

		return {
			dailyHits : rawData,
			weeklyLabels : weekly,
			monthlyLabels : monthly
		};

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