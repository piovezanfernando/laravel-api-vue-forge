@php
    echo "<?php".PHP_EOL;
@endphp

{{'namespace App\Swagger;'}}

/**
 * @OA\Tag(
 *     name="SWOP - Sistema de gerenciamento para prefeituras",
 *     description="SWOP"
 * )
 * @OA\Info(
 *     version="1.0",
 *     title="SWOP - Sistema d egerenciamento para prefeituras",
 * )
 * @OA\Server(
 *     url="https://api-dev.camarasmunicipais.com.br:8443",
 *     description="API server"
 * )
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT",
 *      description="Insira o token JWT no formato 'Bearer {token}'"
 *  )
 */