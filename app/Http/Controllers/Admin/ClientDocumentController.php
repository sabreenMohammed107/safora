<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientDocument;
use App\Services\ClientRegistrationService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientDocumentController extends Controller
{
    public function show(Client $client, ClientDocument $document): StreamedResponse
    {
        return Storage::disk(ClientRegistrationService::DOCUMENT_DISK)
            ->download($document->file_path, $document->original_name);
    }
}
