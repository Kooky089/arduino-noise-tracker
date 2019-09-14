<html>
<head>

<?php
    exec('python3 heating_monitor_parser.py',$output);
?>
    <style>
    </style>
  	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
    google.charts.load("current", {packages:["calendar"]});
    google.charts.load('current', {packages:['timeline']});
    google.charts.load('current', {packages:['gauge']});
    
    google.charts.setOnLoadCallback(drawChart1);
    google.charts.setOnLoadCallback(drawChart2);
    google.charts.setOnLoadCallback(drawChart3);

    $(window).resize(function(){drawChart1();});
    $(window).resize(function(){drawChart2();});
    $(window).resize(function(){drawChart3();});
    
    function drawChart1() {
        var chart = new google.visualization.Calendar(document.getElementById('calendar_basic'));
        var dataTable = new google.visualization.DataTable();
        
        dataTable.addColumn({ type: 'date', id: 'Date' });
        dataTable.addColumn({ type: 'number', id: 'Consumption in l' });
        dataTable.addRows([
        <?php
            echo "$output[0]\n";
        ?>
        ]);

        var options = {
            title: "Oil Consumption",
            colorAxis: {minValue: 0,  colors: ['#0000FF', '#00FFFF', '#00FF00', '#FFFF00', '#FF0000']}
        };
        google.visualization.events.addListener(chart, 'ready', function () {
          $($('#calendar_basic text')[0]).text('0');
          $($('#calendar_basic text')[1]).text('');
          $($('#calendar_basic text')[2]).text('');
          $($('#calendar_basic text')[3]).text('');
          $($('#calendar_basic text')[4]).text('Max');
        });
        chart.draw(dataTable, options);
    }


    function drawChart2() {
        var chart = new google.visualization.Timeline(document.getElementById('timeline'));
        var dataTable = new google.visualization.DataTable();

        dataTable.addColumn({ type: 'string', id: 'President' });
        dataTable.addColumn({ type: 'date', id: 'Start' });
        dataTable.addColumn({ type: 'date', id: 'End' });
        dataTable.addRows([
        <?php
            echo "$output[1]\n";
        ?>
        ]);
        
        var paddingHeight = 0;
        var rowHeight = dataTable.getNumberOfRows() * 8;
        var chartHeight = rowHeight + paddingHeight;
        var options = {
            hAxis: {
                minValue: new Date(0,0,0,0,0,0),
                maxValue: new Date(0,0,0,24,0,0)
            },
            height: chartHeight
        };


        chart.draw(dataTable, options);
    }
    
    function drawChart3() {
        var data = google.visualization.arrayToDataTable([
            ['Label', 'Value'],
            ['Tank',
                <?php
                    echo "$output[2]\n";
                ?>
            ],
        ]);
        
        var formatter = new google.visualization.NumberFormat({ suffix: ' l',decimalSymbol: '.', groupingSymbol: '', fractionDigits: 0 });
        formatter.format(data, 1);

        var options = {
            width: 300, height: 300,
            min: 0,
            max: 10000,
            redFrom: 0, redTo: 1000,
            yellowFrom:1000, yellowTo: 2000,
            majorTicks: ["0", "1k", "2k", "3k", "4k", "5k", "6k", "7k", "8k", "9k", "10k"],
            minorTicks: 10
        };

        var chart = new google.visualization.Gauge(document.getElementById('gauge'));
        
        google.visualization.events.addListener(chart, 'ready', function () {
        $('#gauge circle:nth-child(1)').attr('fill',
            <?php
                echo "'$output[3]'";
            ?>
        );

        });

        chart.draw(data, options);
    }

    </script>
</head>
<body>
    <?php
        echo '<div id="gauge"></div>';
        echo '<div id="calendar_basic"></div>';
        echo '<div id="timeline"></div>';
    ?>    
</body>
</html>
