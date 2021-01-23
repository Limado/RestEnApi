<?php

namespace Limado\RestEnApi;

class Tools
{
/**
 * En caso que el servidor no sea apache o no reconozca la funcion
 */
    public static function getallheaders()
    {
        $contenTypes = ["content-type", "content_type", "contenttype"];
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
            if (in_array(strtolower($name), $contenTypes)) {
                $headers["Content-Type"] = $value;
            }
            if (strtolower($name) == "authentication") {
                $headers["Authentication"] = $value;
            }
        }

        return $headers;
    }
}
