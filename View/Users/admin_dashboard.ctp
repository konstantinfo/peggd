<script type="text/javascript">
    window.onload = function () {
        var week = new CanvasJS.Chart("weekContainer", {
            theme: "theme2",//theme1
            title:{
                text: "Last 7 Days"              
            },
            data: [              
            {
                type: "column",
                dataPoints: [
                    { label: "Web", y: parseInt('<?php echo $week_web_user;?>') },
                    { label: "FaceBook", y: parseInt('<?php echo $week_fb_user;?>') },
                    { label: "Twitter", y: parseInt('<?php echo $week_tw_user;?>') },
                ]
            }
            ]
        });

        week.render();
        
        
        var month = new CanvasJS.Chart("monthContainer", {
            theme: "theme2",//theme1
            title:{
                text: "Last 30 Days"              
            },
            data: [              
            {
                type: "column",
                dataPoints: [
                    { label: "Web", y: parseInt('<?php echo $month_web_user;?>') },
                    { label: "FaceBook", y: parseInt('<?php echo $month_fb_user;?>') },
                    { label: "Twitter", y: parseInt("<?php echo $month_tw_user;?>") },
                ]
            }
            ]
        });

        month.render();
    }
</script>
<script type="text/javascript" src="<?php echo $this->webroot?>js/canvasjs.min.js"></script>

<div style="padding: 3% 10%;">
    <div style="float: left !important; width: 40%;" id="weekContainer" ></div>
    <div style="padding-left: 20%; float: left !important;">&nbsp;</div>
    <div style="padding-left: 40%; float: left !important;" id="monthContainer" ></div>
    <div style="clear: both;"></div>
</div>
<div style="padding: 190px;"></div>