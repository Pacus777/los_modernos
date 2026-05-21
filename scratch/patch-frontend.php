<?php

$file = __DIR__ . '/../resources/js/Pages/Admin/Donaciones/Index.jsx';
if (!file_exists($file)) {
    echo "Error: No se encuentra el archivo frontend.\n";
    exit(1);
}

$content = file_get_contents($file);

// 1. Reemplazar el botón Validar
$patternValidar = '/onClick=\{\(\)\s*=>\s*patchAccion\(\s*\'admin\.donaciones\.validar\'\s*,\s*row\.id,?\s*\)\s*\}/s';
if (preg_match($patternValidar, $content)) {
    $content = preg_replace($patternValidar, 'onClick={() => confirmarDonacion(row)}', $content);
    echo "Botón Validar modificado exitosamente.\n";
} else {
    echo "Advertencia: No se encontró el botón Validar.\n";
}

// 2. Reemplazar el botón Rechazar
$patternRechazar = '/onClick=\{\(\)\s*=>\s*patchAccion\(\s*\'admin\.donaciones\.rechazar\'\s*,\s*row\.id,?\s*\)\s*\}/s';
if (preg_match($patternRechazar, $content)) {
    $content = preg_replace($patternRechazar, 'onClick={() => iniciarRechazo(row)}', $content);
    echo "Botón Rechazar modificado exitosamente.\n";
} else {
    echo "Advertencia: No se encontró el botón Rechazar.\n";
}

file_put_contents($file, $content);
echo "Archivo guardado.\n";
