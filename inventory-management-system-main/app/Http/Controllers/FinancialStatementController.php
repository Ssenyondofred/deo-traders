<?php

namespace App\Http\Controllers;

use App\Services\FinancialStatementService;
use Illuminate\Http\Request;

class FinancialStatementController extends Controller
{
    public function __construct(private FinancialStatementService $service) {}

    public function index()
    {
        return view('financial.index');
    }

    public function incomeStatement(Request $request)
    {
        $from = $request->from ?? date('Y-m-01');
        $to   = $request->to   ?? date('Y-m-d');

        $data = $this->service->incomeStatement($from, $to);

        return view('financial.income-statement', array_merge($data, compact('from', 'to')));
    }

    public function balanceSheet(Request $request)
    {
        $asOf = $request->as_of ?? date('Y-m-d');
        $data = $this->service->balanceSheet($asOf);

        return view('financial.balance-sheet', array_merge($data, compact('asOf')));
    }

    public function cashFlow(Request $request)
    {
        $from = $request->from ?? date('Y-m-01');
        $to   = $request->to   ?? date('Y-m-d');

        $data = $this->service->cashFlow($from, $to);

        return view('financial.cash-flow', array_merge($data, compact('from', 'to')));
    }
}