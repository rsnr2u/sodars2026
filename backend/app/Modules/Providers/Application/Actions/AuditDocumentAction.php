<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Actions;

use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Entities\ProviderDocument;
use App\Modules\Providers\Domain\Enums\DocumentStatus;
use App\Modules\Providers\Domain\Repositories\ProviderReadRepositoryInterface;
use App\Modules\Providers\Domain\Repositories\ProviderWriteRepositoryInterface;
use App\Modules\Providers\Application\Services\ProviderLifecycleService;
use Illuminate\Support\Facades\Auth;

class AuditDocumentAction
{
    public function __construct(
        protected ProviderReadRepositoryInterface $providerReadRepo,
        protected ProviderWriteRepositoryInterface $providerWriteRepo,
        protected ProviderLifecycleService $lifecycleService
    ) {}

    /**
     * Audit a compliance document.
     */
    public function execute(string $providerId, string $docId, string $status, ?string $comment = null): ProviderDocument
    {
        /** @var Provider $provider */
        $provider = $this->providerReadRepo->findOrFail($providerId);

        /** @var ProviderDocument $doc */
        $doc = ProviderDocument::findOrFail($docId);

        if ($doc->provider_id !== $providerId) {
            throw new \InvalidArgumentException('Document does not belong to this provider.');
        }

        $doc->update([
            'status' => $status,
            'remarks' => $comment,
            'verified_by' => Auth::id() ? (string) Auth::id() : null,
            'verified_at' => now(),
        ]);

        // Provider lifecycle transitions
        if ($status === DocumentStatus::Rejected->value) {
            $this->lifecycleService->transitionTo($provider, 'draft');
        } elseif ($status === DocumentStatus::Approved->value) {
            $hasUnapproved = ProviderDocument::where('provider_id', $providerId)
                ->where('is_current', true)
                ->where('status', '!=', DocumentStatus::Approved->value)
                ->exists();

            if (!$hasUnapproved) {
                $this->lifecycleService->transitionTo($provider, 'verified');
            }
        }

        return $doc;
    }
}
