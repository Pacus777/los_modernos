import AppLayout from '@/Layouts/AppLayout';

export default function ComingSoon({ moduleTitle, moduleDescription }) {
    return (
        <AppLayout
            title={moduleTitle}
            metaDescription={moduleDescription}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {moduleTitle}
                </h2>
            }
        >
            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-700">
                            <p className="text-sm leading-relaxed">
                                {moduleDescription}
                            </p>
                            <p className="mt-4 text-sm text-gray-500">
                                Este módulo se completará en las siguientes
                                tareas del sprint.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
