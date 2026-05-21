<?php

namespace App\Http\Middleware;

use App\Services\EmprendedorNotificacionService;
use App\Services\TuristaNotificacionService;
use App\Support\TipoCambioTurista;
use App\Support\WaynaBcpQr;
use Closure;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Aplica el idioma de sesión (es|en) antes de compartir props Inertia.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        if (! in_array($locale, ['es', 'en'], true)) {
            $locale = 'es';
        }

        app()->setLocale($locale);

        return parent::handle($request, $next);
    }

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

        'auth' => [
            'user' => $request->user(),
            'role' => fn () => $request->user()?->nombreRol(),
        ],

        'emprendedorContext' => function () use ($request) {
            $user = $request->user();

            if (! $user?->esEmprendedor()) {
                return null;
            }

            $emprendedor = $user->emprendedor;

            if (! $emprendedor) {
                return null;
            }

            return [
                'id' => $emprendedor->id,
                'perfil_publico_url' => $emprendedor->rutaPublica(),
            ];
        },

        'emprendedorNotificaciones' => function () use ($request) {
            $user = $request->user();

            if (! $user?->esEmprendedor()) {
                return null;
            }

            $emprendedor = $user->emprendedor;

            if (! $emprendedor) {
                return null;
            }

            return app(EmprendedorNotificacionService::class)->resumenParaNavbar($emprendedor);
        },

        'emprendedorOnboarding' => function () use ($request) {
            $user = $request->user();

            if (! $user?->esEmprendedor()) {
                return null;
            }

            $emprendedor = $user->emprendedor;

            if (! $emprendedor) {
                return null;
            }

            return app(\App\Services\EmprendedorOnboardingService::class)->checklist($emprendedor);
        },

        'turistaNotificaciones' => function () use ($request) {
            $user = $request->user();

            if (! $user) {
                return null;
            }

            if ($user->tieneAlgunoDeEstosRoles(['admin', 'cajero', 'emprendedor'])) {
                return null;
            }

            return app(TuristaNotificacionService::class)->resumenParaNavbar($user);
        },

        'adminSession' => fn () => $request->user()?->esAdmin()
            ? [
                'lifetimeMinutes' => (int) config('wayna.admin_session_lifetime_minutes', 30),
                'warnMinutes' => (int) config('wayna.admin_session_warn_minutes', 5),
                'lastActivity' => (int) $request->session()->get('admin_last_activity', time()),
            ]
            : null,

        'locale' => $request->session()->get('locale', 'es'),

        'availableLocales' => [
            'es' => 'Español',
            'en' => 'English',
        ],

        'tipoCambio' => fn () => TipoCambioTurista::paraFrontend(),

        'waynaBcpQr' => fn () => WaynaBcpQr::paraFrontend(),

        'flash' => [
            'success' => fn () => $request->session()->get('success'),
            'error' => fn () => $request->session()->get('error'),
            'donacion_id' => fn () => $request->session()->get('donacion_id'),
            'referencia_pago' => fn () => $request->session()->get('referencia_pago'),
            'qr_pago_url' => fn () => $request->session()->get('qr_pago_url'),
            'rag' => fn () => $request->session()->get('rag'),
        ],
    ];
    }
}
