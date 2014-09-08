
var canvas = $("#wiretapChart");
canvas.attr( "width", canvas.parent().width() - 100 );
canvas.attr( "height", parseInt(canvas.width() * .75) );

var rawData = JSON.parse( $('#wiretap-data').text() );

var labels = [];
var hits = [];
var count = 0;
for ( var date in rawData ) {
    if ( count % 7 === 0 ) {
        labels[ labels.length ] = date;
    }
    else {
        labels[ labels.length ] = '';
    }
    count++;
    hits[ hits.length ] = parseInt( rawData[ date ] );
}

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
        curSum = curSum + dataArray[ i ]; // add in the new value to the moving sum
        
        if ( i - maLength >= 0 ) {
            // subtract oldest value still "part of" moving sum
            // if reached length of moving sum (if have 7 days in
            // 7-day moving sum) 
            curSum = curSum - dataArray[ i - maLength ];
        }

        denominator = Math.min( i + 1, maLength );

        avgArray[ i ] = curSum / denominator;
    }

    return avgArray;

};



var ctx = canvas.get(0).getContext( "2d" );

var data = {
    labels: labels, //["January", "February", "March", "April", "May", "June", "July"],
    datasets: [
        {
            label: "My First dataset",
            fillColor: "rgba(220,220,220,0.2)",
            strokeColor: "rgba(220,220,220,1)",
            pointColor: "rgba(220,220,220,1)",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(220,220,220,1)",
            data: hits //[65, 59, 80, 81, 56, 55, 40]
        }
        ,{
            label: "My Second dataset",
            fillColor: "rgba(151,187,205,0.2)",
            strokeColor: "rgba(151,187,205,1)",
            pointColor: "rgba(151,187,205,1)",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(151,187,205,1)",
            data: getMovingAverage( hits, 7 )
        }
        ,{
            label: "My third dataset",
            fillColor: "rgba(255,92,92,0.2)",
            strokeColor: "rgba(255,92,92,1)",
            pointColor: "rgba(255,92,92,1)",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(151,187,205,1)",
            data: getMovingAverage( hits, 28 )
        }
    ]
};

var options = {

    ///Boolean - Whether grid lines are shown across the chart
    scaleShowGridLines : false,

    //String - Colour of the grid lines
    scaleGridLineColor : "rgba(0,0,0,.05)",

    //Number - Width of the grid lines
    scaleGridLineWidth : 1,

    //Boolean - Whether the line is curved between points
    bezierCurve : true,

    //Number - Tension of the bezier curve between points
    bezierCurveTension : 0.4,

    //Boolean - Whether to show a dot for each point
    pointDot : true,

    //Number - Radius of each point dot in pixels
    pointDotRadius : 4,

    //Number - Pixel width of point dot stroke
    pointDotStrokeWidth : 1,

    //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
    pointHitDetectionRadius : 0,

    //Boolean - Whether to show a stroke for datasets
    datasetStroke : false,

    //Number - Pixel width of dataset stroke
    datasetStrokeWidth : 1,

    //Boolean - Whether to fill the dataset with a colour
    datasetFill : true,

    //String - A legend template
    legendTemplate : "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>"

};

var myLineChart = new Chart( ctx ).Line( data, options );

