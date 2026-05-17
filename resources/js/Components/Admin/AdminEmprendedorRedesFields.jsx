import AdminFormField from '@/Components/Admin/AdminFormField';
import { adminFormStack, adminInputClass } from '@/Components/Admin/adminUi';

/**
 * Contacto público del emprendedor (redes y sitio web).
 */
export default function AdminEmprendedorRedesFields({ data, setData, error }) {
    return (
        <div className={`${adminFormStack} rounded-2xl border border-wayna-100 bg-wayna-50/30 p-4`}>
            <p className="text-sm font-bold text-wayna-900">Redes de contacto (turista)</p>
            <p className="text-xs text-stone-600">
                Opcional: WhatsApp, Instagram, Facebook, TikTok y sitio web. El turista las verá en
                el perfil antes de donar.
            </p>

            <AdminFormField
                id="whatsapp"
                label="WhatsApp"
                hint="Celular de Bolivia: 8 dígitos (ej. 71234567). El código +591 se guarda automáticamente."
                error={error('whatsapp')}
            >
                <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <span className="inline-flex shrink-0 items-center rounded-xl border border-wayna-200 bg-white px-3 py-2 text-sm font-semibold text-wayna-800">
                        +591
                    </span>
                    <input
                        id="whatsapp"
                        type="tel"
                        inputMode="numeric"
                        autoComplete="tel-national"
                        maxLength={8}
                        value={data.whatsapp ?? ''}
                        onChange={(e) =>
                            setData('whatsapp', e.target.value.replace(/\D/g, '').slice(0, 8))
                        }
                        className={`${adminInputClass} min-w-0 flex-1`}
                        placeholder="71234567"
                    />
                </div>
            </AdminFormField>

            <AdminFormField
                id="instagram"
                label="Instagram"
                error={error('instagram')}
            >
                <input
                    id="instagram"
                    type="url"
                    value={data.instagram ?? ''}
                    onChange={(e) => setData('instagram', e.target.value)}
                    className={adminInputClass}
                    placeholder="https://instagram.com/..."
                />
            </AdminFormField>

            <AdminFormField
                id="facebook"
                label="Facebook"
                error={error('facebook')}
            >
                <input
                    id="facebook"
                    type="url"
                    value={data.facebook ?? ''}
                    onChange={(e) => setData('facebook', e.target.value)}
                    className={adminInputClass}
                    placeholder="https://facebook.com/..."
                />
            </AdminFormField>

            <AdminFormField id="tiktok" label="TikTok" error={error('tiktok')}>
                <input
                    id="tiktok"
                    type="url"
                    value={data.tiktok ?? ''}
                    onChange={(e) => setData('tiktok', e.target.value)}
                    className={adminInputClass}
                    placeholder="https://tiktok.com/@usuario"
                />
            </AdminFormField>

            <AdminFormField
                id="sitio_web"
                label="Sitio web"
                hint="Página propia, catálogo o tienda en línea"
                error={error('sitio_web')}
            >
                <input
                    id="sitio_web"
                    type="url"
                    value={data.sitio_web ?? ''}
                    onChange={(e) => setData('sitio_web', e.target.value)}
                    className={adminInputClass}
                    placeholder="https://miemprendimiento.bo"
                />
            </AdminFormField>
        </div>
    );
}
