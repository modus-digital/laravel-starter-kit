import { Head } from "@inertiajs/react";
import { ApiReferenceReact } from "@scalar/api-reference-react";
import '@scalar/api-reference-react/style.css'
import { json as apiJson } from '@/routes/api/openapi';

export default function ApiDocs() {
    return (
        <>
            <Head title="API Documentation" />
            <div className="h-full overflow-x-hidden overflow-y-auto" style={{ height: '100dvh' }}>
                <ApiReferenceReact configuration={{
                    url: apiJson().url,
                    showDeveloperTools: "never",
                    hideDownloadButton: true,
                }} />
            </div>
        </>
    );
}