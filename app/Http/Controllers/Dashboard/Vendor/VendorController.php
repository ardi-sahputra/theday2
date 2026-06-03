<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Support\EffectiveUser;
use App\Support\VendorCategories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VendorController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = EffectiveUser::resolve();

        $data = $this->validateVendor($request);

        $vendor = new Vendor($this->fillableData($data));
        $vendor->user_id = $user->id;

        if ($request->hasFile('contract')) {
            $this->attachContract($request, $vendor, $user->id);
        }

        $vendor->save();

        return response()->json(['vendor' => self::vendorResource($vendor)], 201);
    }

    public function update(Request $request, Vendor $vendor): JsonResponse
    {
        $this->authorizeVendor($vendor);

        $data = $this->validateVendor($request, isUpdate: true);

        $vendor->fill($this->fillableData($data));

        if ($request->hasFile('contract')) {
            $this->deleteContract($vendor);
            $this->attachContract($request, $vendor, $vendor->user_id);
        } elseif ($request->boolean('remove_contract')) {
            $this->deleteContract($vendor);
            $vendor->contract_path = null;
            $vendor->contract_url  = null;
        }

        $vendor->save();

        return response()->json(['vendor' => self::vendorResource($vendor)]);
    }

    public function destroy(Vendor $vendor): JsonResponse
    {
        $this->authorizeVendor($vendor);

        $this->deleteContract($vendor);

        $vendor->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateVendor(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'name'        => ['required', 'string', 'max:120'],
            'category'    => ['required', 'in:'.implode(',', VendorCategories::keys())],
            'pic_name'    => ['nullable', 'string', 'max:80'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'total_cost'  => ['nullable', 'integer', 'min:0'],
            'paid_amount' => ['nullable', 'integer', 'min:0'],
            'next_action' => ['nullable', 'string', 'max:120'],
            'booked_at'   => ['nullable', 'date'],
            'rating'      => ['nullable', 'numeric', 'between:0,5'],
            'notes'       => ['nullable', 'string', 'max:1000'],
            'contract'    => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
        ];

        if ($isUpdate) {
            $rules['remove_contract'] = ['sometimes', 'boolean'];
        }

        return $request->validate($rules);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function fillableData(array $data): array
    {
        $payload = [
            'name'        => $data['name'],
            'category'    => $data['category'],
            'pic_name'    => $data['pic_name'] ?? null,
            'phone'       => $data['phone'] ?? null,
            'total_cost'  => $data['total_cost'] ?? null,
            'paid_amount' => $data['paid_amount'] ?? 0,
            'next_action' => $data['next_action'] ?? null,
            'booked_at'   => $data['booked_at'] ?? null,
            'rating'      => $data['rating'] ?? null,
            'notes'       => $data['notes'] ?? null,
        ];

        // Clamp paid_amount to total_cost when both are set.
        if (! is_null($payload['total_cost']) && (int) $payload['paid_amount'] > (int) $payload['total_cost']) {
            $payload['paid_amount'] = (int) $payload['total_cost'];
        }

        return $payload;
    }

    private function attachContract(Request $request, Vendor $vendor, string $userId): void
    {
        $disk = config('filesystems.uploads');
        $path = $request->file('contract')->store("vendor-contracts/{$userId}", $disk);

        $vendor->contract_path = $path;
        $vendor->contract_url  = Storage::disk($disk)->url($path);
    }

    private function deleteContract(Vendor $vendor): void
    {
        if (! $vendor->contract_path) {
            return;
        }

        $disk = config('filesystems.uploads');
        Storage::disk($disk)->delete($vendor->contract_path);
    }

    private function authorizeVendor(Vendor $vendor): void
    {
        $userId = EffectiveUser::resolve()?->id;

        if ($userId === null || $vendor->user_id !== $userId) {
            abort(403);
        }
    }

    /**
     * Raw + derived vendor payload shared with the page controller.
     *
     * @return array<string, mixed>
     */
    public static function vendorResource(Vendor $v): array
    {
        $total = (int) ($v->total_cost ?? 0);
        $paid  = (int) ($v->paid_amount ?? 0);

        $paidPct = $total > 0 ? (int) min(100, round($paid / $total * 100)) : 0;

        if ($total > 0 && $paid >= $total) {
            $statusKey   = 'lunas';
            $statusLabel = 'Lunas';
        } elseif ($paid > 0) {
            $statusKey   = 'dp';
            $statusLabel = "DP {$paidPct}%";
        } else {
            $statusKey   = 'booked';
            $statusLabel = 'Booked';
        }

        return [
            'id'             => $v->id,
            'name'           => $v->name,
            'category'       => $v->category,
            'category_label' => VendorCategories::label($v->category) ?? $v->category,
            'pic_name'       => $v->pic_name,
            'phone'          => $v->phone,
            'total_cost'     => $v->total_cost,
            'paid_amount'    => $paid,
            'paid_pct'       => $paidPct,
            'status_key'     => $statusKey,
            'status_label'   => $statusLabel,
            'next_action'    => $v->next_action,
            'booked_at'      => $v->booked_at?->format('Y-m-d'),
            'rating'         => $v->rating,
            'contract_url'   => $v->contract_url,
            'notes'          => $v->notes,
        ];
    }
}
