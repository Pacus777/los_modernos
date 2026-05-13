import { useForm } from '@inertiajs/react'

export default function Login() {

    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
    })

    const submit = (e) => {
        e.preventDefault()
        post('/login')
    }

    return (
        <div className="min-h-screen flex items-center justify-center">
            <form onSubmit={submit} className="w-full max-w-md p-6 bg-white rounded shadow">

                <h1 className="text-2xl font-bold mb-4">
                    Iniciar Sesión
                </h1>

                <input
                    type="email"
                    placeholder="Correo"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    className="w-full border p-2 mb-2"
                />

                {errors.email && (
                    <div className="text-red-500 text-sm">
                        {errors.email}
                    </div>
                )}

                <input
                    type="password"
                    placeholder="Contraseña"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    className="w-full border p-2 mb-2"
                />

                {errors.password && (
                    <div className="text-red-500 text-sm">
                        {errors.password}
                    </div>
                )}

                <button
                    type="submit"
                    disabled={processing}
                    className="w-full bg-black text-white p-2 rounded"
                >
                    {processing ? 'Ingresando...' : 'Ingresar'}
                </button>

            </form>
        </div>
    )
}