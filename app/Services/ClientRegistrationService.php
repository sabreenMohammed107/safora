<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ClientStatus;
use App\Enums\ClientUserRole;
use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\ClientRole;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * Creates a pending client from the public registration form with its main branch and
 * owner (company manager) login, stores its documents on the private disk and records
 * the registration in the client's activity log.
 */
class ClientRegistrationService
{
    public const string DOCUMENT_DISK = 'local';

    /**
     * @param  array{company: string, sector_category_id: int|string, contact_name: string, phone: string, email: string, cr_number: string}  $data
     * @param  list<UploadedFile>  $documents
     */
    public function register(array $data, string $password, array $documents = []): Client
    {
        $storedPaths = [];

        try {
            return DB::transaction(function () use ($data, $password, $documents, &$storedPaths): Client {
                $client = Client::create([
                    ...$data,
                    'status' => ClientStatus::Pending,
                    'registered_at' => now(),
                ]);

                $client->branches()->create([
                    'code' => $client->nextBranchCode(),
                    'name' => __('portal.main_branch'),
                    'manager' => $client->contact_name,
                    'phone' => $client->phone,
                    'active' => true,
                ]);

                ClientRole::createDefaultsFor($client);

                $client->users()->create([
                    'name' => $client->contact_name,
                    'email' => $client->email,
                    'role' => ClientUserRole::Owner,
                    'password' => $password,
                    'locale' => app()->getLocale(),
                    'active' => true,
                ]);

                foreach ($documents as $file) {
                    $storedPaths[] = $path = $this->storeDocument($client, $file);

                    $client->documents()->create([
                        'type' => ClientDocument::TYPE_REGISTRATION,
                        'original_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                        'size' => $file->getSize(),
                    ]);
                }

                $client->logActivity('registered', actorName: $client->contact_name);

                return $client;
            });
        } catch (Throwable $exception) {
            Storage::disk(self::DOCUMENT_DISK)->delete($storedPaths);

            throw $exception;
        }
    }

    private function storeDocument(Client $client, UploadedFile $file): string
    {
        $path = $file->store("clients/{$client->getKey()}/documents", self::DOCUMENT_DISK);

        if ($path === false) {
            throw new RuntimeException("Unable to store document [{$file->getClientOriginalName()}].");
        }

        return $path;
    }
}
