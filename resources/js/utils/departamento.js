/** Departamentos de Bolivia (T-A14, sincronizado con App\Enums\Departamento). */
export const DEPARTAMENTOS = [
    { value: 'la_paz', label: 'La Paz' },
    { value: 'santa_cruz', label: 'Santa Cruz' },
    { value: 'cochabamba', label: 'Cochabamba' },
    { value: 'oruro', label: 'Oruro' },
    { value: 'potosi', label: 'Potosí' },
    { value: 'chuquisaca', label: 'Chuquisaca' },
    { value: 'tarija', label: 'Tarija' },
    { value: 'beni', label: 'Beni' },
    { value: 'pando', label: 'Pando' },
];

export function etiquetaDepartamento(valor) {
    if (!valor) {
        return null;
    }
    return DEPARTAMENTOS.find((d) => d.value === valor)?.label ?? null;
}
