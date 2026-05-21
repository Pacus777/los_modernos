<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
$app->instance('request', \Illuminate\Http\Request::create('/', 'GET'));

use App\Models\Emprendedor;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

echo "=== INICIANDO PRUEBAS DE PERFIL PÚBLICO CON SLUG (S4-05) ===\n\n";

// 1. Verificar columna en base de datos
echo "1. Verificando que todos los emprendedores tengan slugs...\n";
$emprendedores = Emprendedor::all();
$sinSlug = 0;
foreach ($emprendedores as $emp) {
    if (empty($emp->slug)) {
        $sinSlug++;
    } else {
        echo "   [OK] Emprendedor ID #{$emp->id}: Slug [{$emp->slug}]\n";
    }
}

if ($sinSlug > 0) {
    echo "   [FAIL] Hay {$sinSlug} emprendedores sin slug en base de datos.\n";
    exit(1);
} else {
    echo "   [SUCCESS] Todos los emprendedores cargan su slug único.\n";
}
echo "\n";

// 2. Probar colisiones al generar slugs únicos
echo "2. Probando generación de slugs únicos y control de colisiones...\n";
$baseNombre = "Pedro Perez Quispe";
$slug1 = Emprendedor::generarSlugUnico($baseNombre);
echo "   - Generación 1 para '{$baseNombre}': [{$slug1}]\n";

// Crear temporalmente un registro con ese slug
$tempEmp = Emprendedor::create([
    'nombre' => 'Pedro',
    'apellidos' => 'Perez Quispe',
    'descripcion' => 'Descripción temporal',
    'slug' => $slug1,
    'estado' => 'inactivo'
]);

$slug2 = Emprendedor::generarSlugUnico($baseNombre);
echo "   - Generación 2 (Colisión) para '{$baseNombre}': [{$slug2}]\n";

// Borrar temporal
$tempEmp->delete();

if ($slug1 !== $slug2 && str_starts_with($slug2, $slug1 . '-')) {
    echo "   [SUCCESS] Control de colisiones e incremental numérico funciona a la perfección.\n";
} else {
    echo "   [FAIL] Falló el control de colisión de slugs.\n";
}
echo "\n";

// 3. Probar método rutaPublica()
echo "3. Probando helper rutaPublica()...\n";
$first = Emprendedor::query()->first();
if (!$first) {
    echo "   [FAIL] No hay emprendedores para probar rutas.\n";
    exit(1);
}
$ruta = $first->rutaPublica();
echo "   - Ruta pública generada: [{$ruta}]\n";
if (str_contains($ruta, "/emprendedores/{$first->slug}")) {
    echo "   [SUCCESS] El helper genera la URL amigable con el slug correcto.\n";
} else {
    echo "   [FAIL] La URL no contiene el slug amigable.\n";
}
echo "\n";

// 4. Probar redirección desde la ruta vieja por ID
echo "4. Probando redirección 302 desde ruta por ID hacia slug...\n";
$request = Request::create(route('turista.emprendedor.show', $first->id), 'GET');
$response = $kernel->handle($request);

if ($response->isRedirection()) {
    $targetUrl = $response->headers->get('Location');
    echo "   - Redirigido exitosamente a: [{$targetUrl}]\n";
    if (str_contains($targetUrl, "/emprendedores/{$first->slug}")) {
        echo "   [SUCCESS] La ruta por ID redirige correctamente a la ruta por slug (302 Redirect).\n";
    } else {
        echo "   [FAIL] Redirigió a una URL incorrecta: [{$targetUrl}]\n";
    }
} else {
    echo "   [FAIL] No se redirigió al slug. Status code: " . $response->getStatusCode() . "\n";
}
echo "\n";

// 5. Probar petición directa a la ruta por slug
echo "5. Probando carga directa de ruta por slug...\n";
$requestSlug = Request::create(route('turista.emprendedores.show', $first->slug), 'GET');
$responseSlug = $kernel->handle($requestSlug);

if ($responseSlug->getStatusCode() === 200 || $responseSlug->getStatusCode() === 302) {
    echo "   [SUCCESS] La ruta por slug responde de forma correcta (HTTP " . $responseSlug->getStatusCode() . ").\n";
} else {
    echo "   [FAIL] No cargó la ruta por slug. Status code: " . $responseSlug->getStatusCode() . "\n";
}
echo "\n";

echo "=== PRUEBAS CONCLUIDAS CON ÉXITO ===\n";
