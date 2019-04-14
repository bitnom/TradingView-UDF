function returnError($errorMsg){
    $responseAr = [];
    $responseAr['success'] = true;
    $responseAr['error'] = true;
    $responseAr['errorMsg'] = $errorMsg;
    die(json_encode($responseAr));
}

function requestCheck($expectedAr)
{
    if(isset($_GET) && isset($_POST))
    {
        $requestAr = array_replace_recursive($_GET, $_POST);
    }elseif(isset($_GET)){
        $requestAr = $_GET;
    }elseif(isset($_POST)){
        $requestAr = $_POST;
    }else{
        $requestAr = array();
    }
    $diffAr = array_diff_key(array_flip($expectedAr),$requestAr);
    if(count($diffAr) > 0)
    {
        returnError("Missing variables: ".implode(',',array_flip($diffAr)).".");
    }else {
        return $requestAr;
    }
}
