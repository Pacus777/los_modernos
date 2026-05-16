/** Opciones T-A13 (sincronizadas con App\Enums\TipoEmprendimiento). */
export const TIPOS_EMPRENDIMIENTO = [
    { value: 'gastronomia', label: 'Gastronomía' },
    { value: 'artesania', label: 'Artesanía' },
    { value: 'turismo', label: 'Turismo' },
    { value: 'textil', label: 'Textil' },
    { value: 'agricultura', label: 'Agricultura' },
    { value: 'servicios', label: 'Servicios' },
    { value: 'otro', label: 'Otro' },
];

export function etiquetaTipoEmprendimiento(valor) {
    if (!valor) {
        return null;
    }
    return TIPOS_EMPRENDIMIENTO.find((t) => t.value === valor)?.label ?? null;
}
