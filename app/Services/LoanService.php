<?php

namespace App\Services;

use App\Enums\ExpedientStatus;
use App\Enums\LoanStatus;
use App\Enums\MovementType;
use App\Models\Expedient;
use App\Models\LoanRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public function __construct(protected ExpedientService $expedientService) {}

    /**
     * User requests an expedient loan.
     */
    public function requestLoan(Expedient $expedient, ?string $observations = null, ?int $requesterId = null): LoanRequest
    {
        if (! $expedient->isAvailable()) {
            throw new \Exception('El expediente no está disponible para préstamo.');
        }

        return DB::transaction(function () use ($expedient, $observations, $requesterId) {
            $expedient->update(['current_status' => ExpedientStatus::Requested]);

            $loan = LoanRequest::create([
                'expedient_id' => $expedient->id,
                'requester_id' => $requesterId ?? Auth::id(),
                'status' => LoanStatus::Pending,
                'requested_at' => now(),
                'observations' => $observations,
            ]);

            $this->expedientService->recordMovement(
                $expedient,
                MovementType::StatusChanged,
                $expedient->current_location_id,
                $expedient->current_location_id,
                'Estado cambiado a Solicitado'
            );

            \App\Events\LoanRequested::dispatch($loan);

            return $loan;
        });
    }

    /**
     * Admin approves a loan.
     */
    public function approveLoan(LoanRequest $loan): void
    {
        if ($loan->status !== LoanStatus::Pending) {
            throw new \Exception('Solo se pueden aprobar solicitudes pendientes.');
        }

        DB::transaction(function () use ($loan) {
            $loan->update([
                'status' => LoanStatus::Approved,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            
            
            $loan->expedient->update(['current_status' => ExpedientStatus::Reserved]);

            \App\Events\LoanApproved::dispatch($loan);
        });
    }

    /**
     * Operator in Planta Baja extracts the folder from drawer and sends it to RH desk.
     */
    public function extractLoan(LoanRequest $loan): void
    {
        if (!in_array($loan->status, [LoanStatus::Pending, LoanStatus::Approved])) {
            throw new \Exception('La solicitud no se encuentra en estado pendiente por extraer.');
        }

        DB::transaction(function () use ($loan) {
            $loan->update([
                'status' => LoanStatus::Reserved,
            ]);

            $expedient = $loan->expedient;
            $expedient->update([
                'current_status' => ExpedientStatus::Reserved,
            ]);

            $userName = Auth::user()?->name ?? 'Operativo de Archivo';
            $this->expedientService->recordMovement(
                $expedient,
                MovementType::StatusChanged,
                $expedient->current_location_id,
                $expedient->current_location_id,
                "Extraído físicamente de gaveta por {$userName} y enviado a Jefatura de RH para entrega."
            );
        });
    }

    /**
     * Admin/Jefe RH delivers the expedient in person to the requester.
     */
    public function deliverLoan(LoanRequest $loan): void
    {
        if ($loan->status === LoanStatus::Pending) {
            $loan->update([
                'status' => LoanStatus::Approved,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            $loan->expedient->update(['current_status' => ExpedientStatus::Reserved]);
        }

        if ($loan->status !== LoanStatus::Approved && $loan->status !== LoanStatus::Reserved) {
            throw new \Exception('La solicitud no se encuentra en un estado válido para entregarse.');
        }

        DB::transaction(function () use ($loan) {
            $defaultDays = (int) config('services.loan.default_due_days', env('LOAN_DEFAULT_DUE_DAYS', 7));

            $loan->update([
                'status' => LoanStatus::Delivered,
                'delivered_at' => now(),
                'due_date' => now()->addDays($defaultDays),
            ]);

            $expedient = $loan->expedient;
            $oldLocation = $expedient->current_location_id;

            $expedient->update([
                'current_status' => ExpedientStatus::Loaned,
                'current_holder_id' => $loan->requester_id,
            ]);

            $this->expedientService->recordMovement(
                $expedient,
                MovementType::Loaned,
                $oldLocation,
                $oldLocation, // Logical location doesn't change, just holder
                'Iniciado préstamo a ' . $loan->requester->name
            );

            \App\Events\LoanDelivered::dispatch($loan);
        });
    }

    /**
     * Admin receives the returned expedient.
     */
    public function returnLoan(LoanRequest $loan, ?string $returnNotes = null): void
    {
        if ($loan->status !== LoanStatus::Delivered) {
            throw new \Exception('Solo se pueden devolver expedientes entregados.');
        }

        DB::transaction(function () use ($loan, $returnNotes) {
            $loan->update([
                'status' => LoanStatus::Returned,
                'returned_at' => now(),
                'return_notes' => $returnNotes,
            ]);

            $expedient = $loan->expedient;
            $location = $expedient->current_location_id;

            $expedient->update([
                'current_status' => ExpedientStatus::Available,
                'current_holder_id' => null,
            ]);

            $this->expedientService->recordMovement(
                $expedient,
                MovementType::Returned,
                $location,
                $location,
                'Reingresado por ' . $loan->requester->name . ($returnNotes ? " - Notas: {$returnNotes}" : '')
            );

            \App\Events\LoanReturned::dispatch($loan);
        });
    }

    /**
     * Cancel a loan request (before delivery).
     */
    public function cancelLoan(LoanRequest $loan, ?string $reason = null): void
    {
        if (in_array($loan->status, [LoanStatus::Delivered, LoanStatus::Returned])) {
            throw new \Exception('No se puede cancelar un préstamo ya entregado.');
        }

        DB::transaction(function () use ($loan, $reason) {
            $loan->update([
                'status' => LoanStatus::Cancelled,
                'return_notes' => $reason,
            ]);

            $expedient = $loan->expedient;
            $expedient->update(['current_status' => ExpedientStatus::Available]);

            $this->expedientService->recordMovement(
                $expedient,
                MovementType::StatusChanged,
                $expedient->current_location_id,
                $expedient->current_location_id,
                'Préstamo cancelado.'
            );

            \App\Events\LoanCancelled::dispatch($loan);
        });
    }
}
