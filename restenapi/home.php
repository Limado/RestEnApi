<html>
    <head>
        <title><?php print $this->service_name?></title>
        <link href="https://fonts.googleapis.com/css2?family=Comfortaa&display=swap" rel="stylesheet"> 
        <link rel="stylesheet" type="text/css" href="./restfull/home.css">
        <meta author="Emiliano Noli, noliemiliano@gmail.com">
    </head>
<body>
<h2>
    <?php print $this->service_name?>
</h2>
<div id="container">
<!--<div id="infoLabel">El par&aacute;metro default para indicar que funci&oacute;n se quiere ejecutar es "function", en todos los casos el sistema es Case Sensitive.</div>-->
<div id="infoLabel">To receive parameters from the body, header 'Content-Type : application/json' must be present.</div>
<?php
$htmlResult = "";
foreach($this->functions as $name => $function){
    $htmlResult .='<div class="functionContainer">';
    $htmlResult .='<a class="functionLink" href="javascript:Collapser(\''.$name.'\');">'.$name.'</a></div>';
    $htmlResult .="<ul class='function' style='display:none' id='$name'>";
    $method = $function->method();
    $params = "";
    if($method == "GET") {
        foreach($function->parameters() as $pname => $ptype)
        {
            $params .="/$pname";
        }
    }
    $serverProtocol =  (isset($_SERVER['HTTPS']) ? "https://" : "http://");
    $requestUri  =  $serverProtocol . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]  . "/" . str_replace("_" . strtolower($method), "", $name) . "$params";
    $htmlResult .="<li>Request URI:<i> {$requestUri} </i></li>";
    $htmlResult .="<li>Method:<i> {$method} </i></li>";
    $htmlResult .="<li>Parameters<ul>";
    // PARAMETROS OBLIGATORIOS
    foreach($function->parameters() as $pname => $ptype)
    {
        $htmlResult .="<li>$pname => $ptype</li>";
    }
    $htmlResult .="</ul></li>";
    // PARAMETROS OPCIONALES
    if  (count($function->optionalParameters())>0) {
        $htmlResult .="<li>Optional parameters:<ul>";
        foreach($function->optionalParameters() as $pname => $ptype)
        {
            $htmlResult .="<li>$pname => $ptype</li>";
        }
        $htmlResult .="</ul>";
    }
    $htmlResult .="</li>";
    $htmlResult .="<li>Return:<i> {$function->return_description}</i></li>";
    $htmlResult .="<li>Description: <i>{$function->description}</i></li>";
    $htmlResult .="</ul>";
}

print $htmlResult;
?>
</div>
<script src='./restfull/home.js'></script>
</body>
</html>