<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentAudit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $action = $request->string('action')->toString();

        $query = ContentAudit::query()->latest('created_at');

        if (in_array($action, ['created', 'updated', 'trashed', 'restored', 'permanently_deleted'], true)) {
            $query->where('action', $action);
        }

        return view('admin.audit.index', [
            'audits' => $query->paginate(40)->withQueryString(),
            'action' => $action,
        ]);
    }
}