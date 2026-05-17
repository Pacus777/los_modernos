import { useEffect, useState } from 'react';
import AdminNotificationToast from './AdminNotificationToast';

export default function AdminRealtimeNotifications() {
    const [notifications, setNotifications] = useState([]);

    useEffect(() => {
        if (!window.Echo) return;

        const channel = window.Echo.private('admin-notifications');

        channel.listen('DonacionCreada', (event) => {
            const localId = `${event.id}-${Date.now()}`;
            setNotifications((prev) => [...prev, { ...event, localId }]);
        });

        return () => channel.stopListening('DonacionCreada');
    }, []);

    const removeNotification = (localId) => {
        setNotifications((prev) => prev.filter((n) => n.localId !== localId));
    };

    return (
        <>
            {notifications.map((n) => (
                <AdminNotificationToast key={n.localId} notification={n} onClose={removeNotification} />
            ))}
        </>
    );
}