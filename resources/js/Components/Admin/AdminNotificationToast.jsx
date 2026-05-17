import { useEffect } from 'react';

export default function AdminNotificationToast({ notification, onClose }) {
    useEffect(() => {
        const timer = setTimeout(() => onClose(notification.localId), 4000);
        return () => clearTimeout(timer);
    }, [notification, onClose]);

    return (
        <div className="fixed top-4 right-4 max-w-sm p-3 rounded-lg bg-wayna-500 text-white shadow-lg animate-slide-in">
            <p className="font-bold">{notification.campana}</p>
            <p>Bs {Number(notification.monto || 0).toFixed(2)}</p>
            <p className="text-sm">{notification.usuario}</p>
        </div>
    );
}