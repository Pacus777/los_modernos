import { useEffect } from 'react';

export default function NotificationToast({ notification, onClose }) {
    useEffect(() => {
        const timer = setTimeout(() => onClose(notification.id), 4000);
        return () => clearTimeout(timer);
    }, [notification, onClose]);

    return (
        <div className="fixed top-4 right-4 max-w-sm p-3 rounded-lg bg-wayna-500 text-white shadow-lg animate-slide-in">
            <p className="font-bold">{notification.campana}</p>
            <p>Bs {notification.monto}</p>
            <p className="text-sm">{notification.usuario}</p>
        </div>
    );
}