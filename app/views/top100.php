<?php
include("includes/header.php");
?>

<body id="page-top" class="index">

<section id="top100">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h2 class="section-heading">Top 100 de usuarios</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <table class="table top100">
                    <thead>
                    <tr>
                        <th></th>
                        <th>Quien</th>
                        <th>Ciudades</th>
                        <th>Viajes</th>
                        <th>Kilómetros</th>
                        <th>Tiempo</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                        $count = 0;
                        foreach($output as $dato):
                            $count++;
                    ?>
                    <tr>
                        <td>#<?php echo $count; ?></td>
                        <td class="top100-profile"><img class="img-circle profile" width="25" src="<?php echo $dato["user"]["picture"]; ?>" alt="Arturo"> <a href="/stats/<?php echo $dato["user"]["uuid"]; ?>"><?php echo strtoupper($dato["user"]["first_name"]); ?></a></td>
                        <td><?php echo count($dato["cities"]); ?></td>
                        <td><?php echo $dato["totalTrips"]; ?></td>
                        <td><?php echo number_format($dato["totalKm"],2); ?></td>
                        <td><?php echo seconds2human($dato["totalTime"],2); ?></td>
                    </tr>
                    <?php endforeach;?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php
include("includes/footer.php");
?>

</body>

</html>