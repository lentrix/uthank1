<?php

namespace App\Http\Controllers;

use App\Models\Debtor;
use Illuminate\View\View;

class DebtorController extends Controller
{
    public function index(): View
    {
        $debtors = Debtor::query()
            ->orderBy('lname')
            ->orderBy('fname')
            ->orderBy('id')
            ->paginate(25);

        return view('debtors.index', ['debtors' => $debtors]);
    }

    public function show(Debtor $debtor): View
    {
        return view('debtors.show');
    }
}
