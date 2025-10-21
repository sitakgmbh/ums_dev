<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     title="REST API",
 *     version="1.0.0",
 *     description="Diese REST-API stellt Funktionen zur Systemverwaltung bereit.",
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description=""
 * )
 *
  * @OA\SecurityScheme(
 *     securityScheme="basicAuth",
 *     type="http",
 *     scheme="basic",
 * )
 */
class SwaggerController extends Controller
{
    // Dieser Controller enthält nur Swagger-Metadaten.
    // Hier sind keine Endpunkte implementiert.
}
