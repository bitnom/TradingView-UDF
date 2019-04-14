<?php

if((@include 'api.php') === false){
    $responseAr = ['success' => true, 'apiUpdating' => true];
    $responseAr['layoutActive'] = true;
    echo json_encode($responseAr);
}
