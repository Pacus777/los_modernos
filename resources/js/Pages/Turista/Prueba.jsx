import GuestLayout from '@/Layouts/GuestLayout';

// Pantalla de prueba para verificar que el selector de idioma aparece antes de cualquier acción del turista.
export default function Prueba() {
    return (
        <GuestLayout variant="full">
            <section className="rounded-2xl bg-white p-6 shadow">
                <h1 className="text-2xl font-bold text-gray-800">
                    Pantalla de prueba del turista
                </h1>

                <p className="mt-2 text-gray-600">
                    Esta pantalla sirve para verificar que el selector de idioma
                    aparece antes de cualquier acción del turista.
                </p>
            </section>
        </GuestLayout>
    );
}