<?php

// COMPTE LE NOMBRE D'IMAGES RECU
$total_files = count($_FILES['input_upload_image']['name']);

// EST-CE QU'ON AFFICHE LA NUMEROTATION
$display_numbers = $_POST["display_numbers"];

// EST-CE QU'ON AFFICHE LES NOMS
$display_names = $_POST["display_names"];

// QUALITE DE COMPRESSION
$jpg_quality = $_POST["quality"];

// LARGEUR DE L'IMAGE FINALE
$final_width=$_POST["desired_width"];

// TAILLE BARRE NOIRE SUR LAQUELLE ON VA ECRIRE NUMEROS ET NOM D'IMAGE
$display_numbers_height = 24;

// TAILLE MAX DES IMAGES IMPORTEES
$max_size_upload = 2000*2000;

// SI AUCUNE IMAGE N'EST CREE, CA RESTE A FALSE
$download_file = false;

// EXTENSIONS AUTORISEES
$allowed_ext = array("png", "jpg", "jpeg");

// NOM FINALE DE L'IMAGE
$final_image_name = md5(rand()) . '.jpg';

// CHEMIN VERS L'IMAGE FINALE
$path = '/home/makemevikw/oretmonnaies/uploads/' . $final_image_name;

//CHEMIN VERS L'IMAGE EN HTTP
$pathHTTP = 'https://oretmonnaies.hepta.studio/uploads/' . $final_image_name;

// VARIABLE QUI MÉMORISE LA POSITION VERICALE DE NOTRE PROCHAINE IMAGE.
// LA PROCHAINE IMAGE A AFFICHER EST à 0 PIXEL SUR L'AXE Y
$next_Y=0;

// INIT SOME VARS
$final_height=0;






// LIMITE LE NOMBRE D'IMAGES A TRAITER A 30 IMAGES
if ($total_files > 30) {
    $total_files = 30;
}


// ON RECUP LES IMAGES ET ON CREE DES VARIABLES
for ($i=0; $i < $total_files; $i++) {

    ${"name$i"} = $_FILES['input_upload_image']["name"][$i];
    ${"size$i"} = $_FILES['input_upload_image']["size"][$i];
    ${"ext$i"} = end(explode(".", ${"name$i"} ));

}



// ON CALCULE LA TAILLE DE L'IMAGE FINALE
for ($y=0; $y < $total_files; $y++) {


    if(in_array(${"ext$y"}, $allowed_ext) && ${"size$y"} < $max_size_upload) {

        list($width, $height) = getimagesize($_FILES["input_upload_image"]["tmp_name"][$y]);
        
        if(${"ext$y"} == 'png' || ${"ext$y"} == 'jpg' || ${"ext$y"} == 'jpeg') {  

            $final_height += round( ($height/$width)*$final_width , 0,  PHP_ROUND_HALF_DOWN);

            if ($display_numbers == "on" || $display_names == "on") {
                $final_height += $display_numbers_height;
            }
        }
    }
}


// SI LE CALCUL DE NOTRE TAILLE FINALE EST SUP A 0, ALORS ON CREE L'IMAGE FINALE
// SINON C'EST QU'AUCUNE IMAGE IMPORTEE N'EST VALIDE
if($final_height > 0) {

    // INIT DE NOTRE IMAGE FINALE
    // POUR LE MOMENT ELLE EST VIDE
    $tmp_image = imagecreatetruecolor($final_width, $final_height);

    //ON LOOP NOS IMAGES IMPORTEES
    for ($y=0; $y < $total_files; $y++) { 

        // SI L'IMAGE A UNE EXTENSION ACCEPTEE
        if(in_array(${"ext$y"}, $allowed_ext)) {

            // SI L'IMAGE A UNE TAILLE ACCEPTEE
            if(${"size$y"} < $max_size_upload)  {
                
                // ON INDIQUE QU'IL Y AURA UNE IMAGE A TÉLÉCHARGE
                $download_file = true;


                // ON FOUT NOTRE IMAGE EN VARIABLE
                // SI L'IMAGE EST UN .PNG
                if(${"ext$y"} == 'png') {
                    $new_image = imagecreatefrompng($_FILES["input_upload_image"]["tmp_name"][$y]);
                }
                // SI L'IMAGE EST UN .JPG
                if(${"ext$y"} == 'jpg' || ${"ext$y"} == 'jpeg') {  
                    $new_image = imagecreatefromjpeg($_FILES["input_upload_image"]["tmp_name"][$y]);  
                }
                

                // CALCUL DE LA TAILLE DE NOTRE IMAGE A INTÉGRER DANS NOTRE BANDE
                // ON CONNAIT DÉJA LA WIDTH
                // ON CALCUL LA HEIGHT POUR GARGER DE BONNES PROPORTIONS
                list($width, $height) = getimagesize($_FILES["input_upload_image"]["tmp_name"][$y]);
                $new_height =  round( ($height/$width)*$final_width , 0,  PHP_ROUND_HALF_DOWN );



                // $offset_Y EST LA POSITION VERTICALE, EN PIXEL, DE NOTRE IMAGE DANS LA BANDE D'IMAGE
                // SI ON DOIT AFFICHER LA BANDE NOIRE, ON DÉCALE $offset_Y DE $display_numbers_height PIXELS
                if ( $display_numbers == "on" || $display_names == "on" ) {
                    $offset_Y = $display_numbers_height;
                } else {
                    $offset_Y = 0;
                }

                // ON DESSINE L'IMAGE DANS NOTRE BANDE D'IMAGES
                imagecopyresampled($tmp_image, $new_image, 0, $next_Y+$offset_Y, 0, 0, $final_width, $new_height, $width, $height);


                // CODE QUI ÉCRIT DANS LA BANDE NOIRE
                if ($display_numbers == "on" || $display_names == "on") {

                    // ON DEFINI LA COULEUR BLANCHE
                    $white = imagecolorallocate($new_image, 255, 255, 255);

                    // NUMERO DE L'IMAGE.
                    // ON AJOUTE 1 POUR PAS COMMENCER LA NUMEROTATION A 0
                    $image_num = $y+1;

                    // INIT DU TXT
                    $txt = "";

                    // SI ON VEUT ECRIRE LA NUMEROTATION
                    if ($display_numbers == "on") {
                        $txt .= '#'. $image_num . " ";
                    }

                    // SI ON VEUT ECRIRE LE NOM DE L'IMAGE
                    if ($display_names == "on") {
                        $txt .= current(explode(".", ${"name$y"} ));
                    }

                    // ON DEFINI LA POLICE
                    $font = $_POST["upload_folder"] . "RobotoCondensed-Regular.ttf"; 


                    // ON ECRIT NOTRE TEXTE
                    imagettftext($tmp_image, 14, 0, 7, $next_Y+$display_numbers_height-5, $white, $font, $txt);
                    
                }

                // CALCUL DE LA POSITION Y DE LA PROCHAINE IMAGE A AJOUTER SUR LA BANDE
                $next_Y += $new_height+$offset_Y;

            }  else  {
                echo '<br>' . ${"name$y"} . 'est trop grande';
            }
        } else {
            echo '<br>' . ${"name$y"} . ' : Type de fichier incorrect';
        }


    }



    // ON CREE L'IMAGE FINALE
    imagejpeg($tmp_image, $path , $jpg_quality);

    // DESTROY DES VARIABLES TEMPORAIRES
    // Adieu les filles <3
    imagedestroy($new_image);
    imagedestroy($tmp_image);



    // SI UN FICHIER A ETE CREE
    // ON AFFICHE CE HTML
    if ($download_file) {

        ?>
        <div id="infos_file">

            <a class="btn" href="<?php echo $pathHTTP; ?>" download>Télécharger</a>

            <br>

            <p>
                <strong>Fichier :</strong> créé<br>
                <strong>Taille :</strong> <?php echo round( filesize($path)/1000000 , 2); ?> Mo<br>
                <strong>Vous avez 5 minutes pour télécharger le fichier.</strong>
            </p>

        </div>



        <?php

    }


} else {
    echo "Fichier non créé.";
}

?>
