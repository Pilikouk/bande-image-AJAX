<!DOCTYPE html>

<html>

    <head>
        <meta name="viewport" content="width=device-width" />

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@300;400;700;900&family=Roboto:wght@300;400;700;900&display=swap" rel="stylesheet">

        <link rel="stylesheet" href="styles.css">

    </head>

    <body >

        <div id="wrapper" class="hfeed">

            <div id="container">

                <?php

    //
    // THAT PART REMOVES FILES OLDER THAN 300s OF LIFE
    // TOO LAZY TO CREATE SOMETHING LIKE A CRON.
    //

                $folderName = "/uploads/";

                if (file_exists($folderName)) {
                    foreach (new DirectoryIterator($folderName) as $fileInfo) {
                        if ($fileInfo->isDot()) {
                            continue;
                        }


    // Nombres de secondes en 1 journée
    // 60secondes*60minutes*24h


                        if ($fileInfo->isFile() && time() - $fileInfo->getCTime() >= 300) {
                            unlink($fileInfo->getRealPath());
                        }

                    }
                }
                ?>



                <h1>Images</h1>
                <p>Créateur de bande d'images.<br>30 images max pour le moment. Dimension max d'image 2000×2000px</p>
                <br />

                <form method="post" id="upload_image" enctype="multipart/form-data">

                    <input type="file" multiple="multiple" name="input_upload_image[]" class="input_upload_image" id="input_upload_image" /><br />

                    <br />

                    <label>
                        Largeur (max 1200px)
                        <input type="number" name="desired_width" id="desired_width" value="600" min="10" max="1200">px
                    </label>

                    <br />

                    <label>
                        Numérotation
                        <input type="checkbox" name="display_numbers" id="display_numbers" >
                    </label>

                    <br />

                    <label>
                        Nom des images
                        <input type="checkbox" name="display_names" id="display_names" >
                    </label>

                    <br />

                    <label>
                        Qualité (0: Moche, 100:Beau)
                        <input type="number" name="quality" id="quality" value="75" min="0" max="100">
                    </label>

                    <br />
                    <input type="submit" name="upload" id="upload" class="btn btn-info" value="Créer" />

                </form>


                <div id="message"></div>

                <div id="result"></div>



                <script type="text/javascript">


                    jQuery(document).ready(function(){

                        jQuery('#upload_image').on('submit', function(event){

                            event.preventDefault();

                            jQuery("#upload_image").remove();
                            jQuery("#message").html("Chargement...");

                            jQuery.ajax({

                                url: "/process.php",
                                method:"POST",
                                data:new FormData(this),
                                contentType:false,
                                cache:false,
                                processData:false,

                                success:function(data){

                                    jQuery('#message').html("");
                                    jQuery("#upload").remove();
                                    jQuery('#result').html(data);
                                },
                                error:function(){
                                    jQuery("#message").html("Erreur... Rechargez la page");
                                }
                            })
                        });

                    });

                </script>


            </div>
            <footer id="footer" role="contentinfo">

            </footer>
        </div>
    </body>
</html>
