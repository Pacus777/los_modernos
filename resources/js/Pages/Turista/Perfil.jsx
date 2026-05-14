import GuestLayout from '@/Layouts/GuestLayout';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import DonacionForm from '@/Components/Turista/DonacionForm';

export default function Perfil({ emprendedor, campanaActiva, progreso, tipoPagos }) {
    const [montoSeleccionado, setMontoSeleccionado] = useState(10);

    const montosRapidos = [5, 10, 20, 50];

    const nombreCompleto = `${emprendedor.nombre ?? ''} ${emprendedor.apellidos ?? ''}`.trim();

    const fotoUrl = emprendedor.fotografia
        ? `/storage/${emprendedor.fotografia}`
        : null;

    const porcentaje = progreso?.porcentaje ?? 0;

    return (
        <GuestLayout variant="full">
            <Head title={`Apoya a ${nombreCompleto}`} />

            <section className="overflow-hidden rounded-3xl bg-white shadow-xl shadow-wayna-900/10">
                <div className="relative h-64 bg-gradient-to-br from-wayna-100 to-orange-100">
                    {fotoUrl ? (
                        <img
                            src={fotoUrl}
                            alt={nombreCompleto}
                            className="h-full w-full object-cover"
                        />
                    ) : (
                        <div className="flex h-full items-center justify-center px-6 text-center">
                            <span className="text-lg font-semibold text-wayna-700">
                                Foto del emprendedor
                            </span>
                        </div>
                    )}

                    <div className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-5">
                        <p className="text-sm font-medium text-orange-100">
                            Emprendedor Wayna
                        </p>

                        <h1 className="text-2xl font-bold text-white">
                            {nombreCompleto}
                        </h1>
                    </div>
                </div>

                <div className="space-y-6 p-5">
                    <div>
                        <h2 className="text-lg font-bold text-gray-900">
                            Su historia
                        </h2>

                        <p className="mt-2 text-sm leading-6 text-gray-600">
                            {emprendedor.descripcion ||
                                'Este emprendedor forma parte de Wayna y busca fortalecer su actividad mediante el apoyo directo de turistas y visitantes.'}
                        </p>
                    </div>

                    <div className="rounded-2xl border border-wayna-100 bg-wayna-50 p-4">
                        <div className="flex items-start justify-between gap-4">
                            <div>
                                <p className="text-sm font-medium text-wayna-700">
                                    Meta de apoyo
                                </p>

                                <h3 className="mt-1 text-lg font-bold text-gray-900">
                                    {campanaActiva?.titulo ??
                                        'Campaña de apoyo activa'}
                                </h3>
                            </div>

                            <span className="rounded-full bg-white px-3 py-1 text-sm font-bold text-wayna-700 shadow-sm">
                                {porcentaje}%
                            </span>
                        </div>

                        <div className="mt-4 h-3 overflow-hidden rounded-full bg-white">
                            <div
                                className="h-full rounded-full bg-wayna-600 transition-all duration-700"
                                style={{ width: `${porcentaje}%` }}
                            />
                        </div>

                        <div className="mt-3 grid grid-cols-2 gap-3 text-sm">
                            <div className="rounded-xl bg-white p-3">
                                <p className="text-gray-500">Recaudado</p>
                                <p className="font-bold text-gray-900">
                                    Bs {Number(progreso?.monto_recaudado ?? 0).toFixed(2)}
                                </p>
                            </div>

                            <div className="rounded-xl bg-white p-3">
                                <p className="text-gray-500">Meta</p>
                                <p className="font-bold text-gray-900">
                                    Bs {Number(progreso?.meta ?? 0).toFixed(2)}
                                </p>
                            </div>
                        </div>
                    </div>

                    <form
                        className="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm"
                        onSubmit={(e) => e.preventDefault()}
                    >
                        <h2 className="text-lg font-bold text-gray-900">
                            Elige tu aporte
                        </h2>

                        <p className="mt-1 text-sm text-gray-500">
                            Selecciona un monto en bolivianos para apoyar esta campaña.
                        </p>

                        <div className="mt-4 grid grid-cols-4 gap-2">
                            {montosRapidos.map((monto) => (
                                <button
                                    key={monto}
                                    type="button"
                                    onClick={() => setMontoSeleccionado(monto)}
                                    className={`rounded-xl border px-3 py-3 text-sm font-bold transition ${
                                        montoSeleccionado === monto
                                            ? 'border-wayna-600 bg-wayna-600 text-white'
                                            : 'border-gray-200 bg-white text-gray-700 hover:bg-wayna-50'
                                    }`}
                                >
                                    Bs {monto}
                                </button>
                            ))}
                        </div>

                        <label className="mt-4 block text-sm font-medium text-gray-700">
                            Otro monto
                        </label>

                        <input
                            type="number"
                            min="1"
                            value={montoSeleccionado}
                            onChange={(e) => setMontoSeleccionado(e.target.value)}
                            className="mt-1 block w-full rounded-xl border-gray-300 focus:border-wayna-500 focus:ring-wayna-500"
                            placeholder="Ej. 30"
                        />

                        <button
                            type="submit"
                            className="mt-5 w-full rounded-xl bg-wayna-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-wayna-700"
                        >
                            Continuar con Bs {montoSeleccionado}
                        </button>

                        <p className="mt-3 text-center text-xs text-gray-400">
                            El registro real de la donación se conectará en la siguiente tarea.
                        </p>
                    </form>
                </div>
            </section>
        </GuestLayout>
    );
}