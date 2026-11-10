<?php

namespace App\Http\Controllers;

use App\Models\QrGroup;
use App\Models\QrGroupItem;
use Illuminate\Contracts\View\View;

class QrGroupPrintController extends Controller
{
    /**
     * Printable sheet containing the group QR code and every selected item QR code.
     */
    public function print(string $token): View
    {
        $user = auth()->user();

        abort_unless(
            $user?->hasPermission('documents_view')
            || $user?->hasPermission('clients_view')
            || $user?->hasPermission('invoices_view'),
            403,
        );

        $group = QrGroup::query()
            ->where('public_token', $token)
            ->with(['items', 'user'])
            ->firstOrFail();

        return view('qr-groups.print', [
            'group' => $group,
            'documents' => $group->items->where('item_type', QrGroupItem::TYPE_DOCUMENT)->values(),
            'clients' => $group->items->where('item_type', QrGroupItem::TYPE_CLIENT)->values(),
            'groupUrl' => route('qr-groups.show', $group->public_token),
        ]);
    }
}
