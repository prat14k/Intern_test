<?php


// Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        // you want to allow, and if so:
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, 'http://ipinfo.io');
    $result = curl_exec($ch);
    curl_close($ch);
    $ipDetails = json_decode($result,true);
    

    $geo = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=".$ipDetails["ip"]));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, 'https://maps.googleapis.com/maps/api/place/nearbysearch/json?location='.$geo["geoplugin_latitude"].','.$geo["geoplugin_longitude"].'&radius=500&type=food&key=AIzaSyCcQzfgNXYg0DGFIY_dwQCCd-FVm9ZpKr8');
    $result = curl_exec($ch);
    curl_close($ch);

    $nearby = json_decode($result,true);
    


?>

<!DOCTYPE html>
<html>
    
<head>
		<meta charset="UTF-8">
		<title>location</title>
        <meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <!-- bootstrap framework -->
		<link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
		
        
        <!-- main stylesheet -->
		<link href="assets/css/main.min.css" rel="stylesheet" media="screen" id="mainCss">
</head>
    <body class="side_menu_active side_menu_expanded" >
        <div id="page_wrapper">

            <!-- main content -->
            <div id="main_wrapper">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-7">
                            <h3 class="heading_a"><span class="heading_text">Location and stuff</span></h3>
                            
                            <pre id="details"> <?php print_r($ipDetails); ?> </pre>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-7">
                            <div class="gmaps_wrapper">
                                <div class="gmap" id="gmap_markers"></div>
                            </div>
                        </div>
                    </div>
                    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
                </div>
            </div>
            
        </div>

        <!-- jQuery -->
        <script src="assets/js/jquery.min.js"></script>
        <!-- Bootstrap Framework -->
        <script src="assets/bootstrap/js/bootstrap.min.js"></script>
        <script src="assets/lib/jquery-match-height/jquery.matchHeight-min.js"></script>
        <!-- scrollbar -->
        <script src="assets/lib/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>

        <script type="text/javascript">

            // gmaps
            yukon_gmaps = {
                init: function() {
                    map_markers = new GMaps({
                        el: '#gmap_markers',
                        lat: <?php echo $geo["geoplugin_latitude"]; ?>,
                        lng: <?php echo $geo["geoplugin_longitude"]; ?>
                    });
                    
                    /*
                    GMaps.geocode({
                            address: $('#gmaps_address').val().trim(),
                            callback: function (results, status) {
                                if (status == 'OK') {
                                    var latlng = results[0].geometry.location;
                                    map_geocode.setCenter(latlng.lat(), latlng.lng());
                                    map_geocode.addMarker({
                                        lat: latlng.lat(),
                                        lng: latlng.lng()
                                    });
                                }
                            }
                        });
                    */
                    
                    
            <?php
                $cresults = count($nearby["results"]);
                for($i=0;$i<$cresults;$i++){
                    $r="";
                    if(isset($nearby["results"][$i]["rating"]))
                        $r="Rating- ".$nearby["results"][$i]["rating"];

                    ?>

                    map_markers.addMarker({
                        lat: <?php echo $nearby["results"][$i]["geometry"]["location"]["lat"]; ?>,
                        lng: <?php echo $nearby["results"][$i]["geometry"]["location"]["lng"]; ?>,
                        title: "<?php echo $nearby['results'][$i]['name']; ?>",
                        infoWindow: {
                            content: "<div class='infoWindow_content'><p><?php echo $nearby['results'][$i]['name']; ?><br><?php echo $nearby['results'][$i]['vicinity']; ?><br><?php echo $r; ?></p></div>"
                        }
                    });


                <?php
                }
            ?>
                map_markers.addMarker({
                        lat: <?php echo $geo["geoplugin_latitude"]; ?>,
                        lng: <?php echo $geo["geoplugin_longitude"]; ?>,
                        title: "<?php echo $geo['geoplugin_city'].','.$geo['geoplugin_regionName'].','.$geo['geoplugin_countryName'] ?>",
                        details: {
                            // You can attach additional information, which will be passed to Event object (e) in the events previously defined.
                        },
                        icon: 'assets/image/main-marker.png',
                        click: function(e){
                            alert('This is your location !!!');
                        },
                        mouseover: function(e){
                            if(console.log) console.log(e);
                        }
                    });
                    
                }
            };


        </script>
	    <!-- page specific plugins -->
            <!-- gmaps -->
            <script src="assets/lib/gmaps/gmaps.js"></script>
            <script>
                $(function() {
                    // gmaps
                    yukon_gmaps.init();
                })
            </script>
    </body>

</html>
