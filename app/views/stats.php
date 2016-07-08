<?php
include("includes/header.php");
?>

<body id="page-top" class="index">
<section id="stats">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <img class="img-circle profile" width="100" src="<?php echo $output["user"]["picture"]; ?>" alt="<?php echo $output["user"]["first_name"]; ?>">
                <h2 class="section-heading">Estadísticas de <?php echo $output["user"]["first_name"]; ?></h2>
                <div class="row">
                    <a href="/top100"><strong>Ver top 100</strong></a>
                </div>
                <div class="col-md-3 text-center text-muted">
                    <h3><?php echo $output["totalTrips"]; ?><br/>VIAJES</h3>
                    <span>¡Nada mal!</span>
                </div>
                <div class="col-md-3 text-center text-muted">
                    <h3><?php echo number_format($output["totalKm"],2); ?><br/>KILÓMETROS</h3>
                    <span><?php echo $output["totalKm"] > 0 ? number_format($output["totalKm"]/$output["totalTrips"],2) : "0"; ?> km en promedio</span>
                </div>
                <div class="col-md-3 text-center text-muted">
                    <h3 class="stats-data"><?php echo seconds2human($output["totalTime"]); ?><br />TIEMPO EN UBER</h3>
                    <span><?php echo $output["totalTime"] > 0 ? seconds2human($output["totalTime"]/$output["totalTrips"]) : "0m"; ?> en promedio</span>
                </div>
                <div class="col-md-3 text-center text-muted">
                    <h3 class="stats-data"><?php echo seconds2human($output["totalWaitTime"]); ?><br />ESPERANDO UBER</h3>
                    <span><?php echo $output["totalTrips"] > 0 ? seconds2human($output["totalWaitTime"]/$output["totalTrips"]) : "0m"; ?> en promedio</span>
                </div>
            </div>
        </div>
        <hr />
        <div class="row share">
            <?php
            if($isCurrentUser){
            ?>
            <span>Comparte tus estadísticas</span><br/>
            <div class="buttons">
                <div class="fb-share-button" data-href="https://ridetotals.com<?php echo $_SERVER['REQUEST_URI']; ?>" data-layout="button" data-mobile-iframe="false"></div>
                <a href="https://twitter.com/share" class="twitter-share-button" data-url="https://ridetotals.com<?php echo $_SERVER['REQUEST_URI']; ?>" data-text="He usado @Uber <?php echo $output["totalTrips"];?> veces (<?php echo number_format($output["totalKm"],2); ?>km) en un tiempo total de <?php echo seconds2human($output["totalTime"]); ?>" data-lang="es">Twittear</a>
                <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
                </div>
            <? } else {?>
                <a href="/login">¡Haz click aquí para conocer tus estadísticas!</a>
            <? } ?>
            </div>
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="col-md-12 text-center text-muted">
                    <?php
                    $cuenta_ciudades = count($output["cities"]);
                    if($cuenta_ciudades == 1){
                        echo "<h3>¡Has usado Uber en una ciudad!</h3>";
                    }else{
                        $ciudades = join($output["cities"]," - ");
                        echo "<h3>¡Has usado Uber en {$cuenta_ciudades} ciudades!</h3><span>{$ciudades}</span>";
                    }
                    ?>
                </div>
            </div>
        </div>
        <br /><br />
        <div class="row">
            <div class="col-lg-12 text-center">
                    <?php
                    //ToDo: dynamic number of columns
                    foreach($output["products"] as $llave=>$valor){
                        if($llave == "Other"){
                            $valor["image"] = "http://d1a3f4spazzrp4.cloudfront.net/car-types/mono/mono-uberx.png";
                            $llave = "Otro (Desconocido)";
                        }
                        $valor['image'] = str_replace("http://","https://",$valor['image']);
                        $plural = $valor['count'] <= 1 ? 'viaje' : 'viajes';
                        echo "<div class=\"col-md-4 text-center text-muted product\">\n";
                        echo "<div class='product-img'>\n<img class='product' src='{$valor['image']}' alt='{$llave}' />\n</div>\n";
                        echo "<h4>{$llave}</h4>\n";
                        echo "<span>{$valor['count']} {$plural}</span>\n";
                        echo "</div>";
                    }
                    ?>

                </div>
            </div>
        </div>
    </div>
</section>

<?php
include("includes/footer.php");
?>

</body>

</html>