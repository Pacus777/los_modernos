import AdminFormField from '@/Components/Admin/AdminFormField';
import { adminFormStack, adminInputClass } from '@/Components/Admin/adminUi';

/**
 * Contacto público del emprendedor (WhatsApp, Instagram, Facebook).
 */
export default function AdminEmprendedorRedesFields({ data, setData, error }) {
    return (
        <div className={`${adminFormStack} rounded-2xl border border-wayna-100 bg-wayna-50/30 p-4`}>
            <p className="text-sm font-bold text-wayna-900">Redes de contacto (turista)</p>
            <p className="text-xs text-stone-600">
                Opcional: WhatsApp, Instagram, Facebook y TikTok. El turista las verá en el perfil
                antes de donar.
            </p>

            <AdminFormField
                id="whatsapp"
                label="WhatsApp"
                hint="Número con código de país o enlace wa.me"
                error={error('whatsapp')}
            >
                <input
                    id="whatsapp"
                    type="text"
                    value={data.whatsapp ?? ''}
                    onChange={(e) => setData('whatsapp', e.target.value)}
                    className={adminInputClass}
                    placeholder="59170000000"
                />
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
        </div>
    );
}
