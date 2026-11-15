@extends('layouts.app')
@section('title', 'Buat Jurnal Manual')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Create a Manual Journal</h5>
    <a href="{{ route('journals.index') }}" class="btn btn-sm btn-secondary">← Kembali</a>
</div>

<form action="{{ route('journals.store') }}" method="POST" id="journalForm">
    @csrf
    <div class="card shadow-sm mb-3">
        <div class="card-header fw-bold">Journal Header</div>
        <div class="card-body row g-3">
            <div class="col-md-3">
                <label class="form-label small">Date <span class="text-danger">*</span></label>
                <input type="date" name="entry_date" class="form-control form-control-sm"
                    value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small">Description <span class="text-danger">*</span></label>
                <input type="text" name="description" class="form-control form-control-sm"
                    placeholder="Example: Adjustment journal for inventory" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Notes</label>
                <input type="text" name="notes" class="form-control form-control-sm" placeholder="Optional">
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-bold">Journal Lines</span>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addLine">+ Add Line</button>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0" id="linesTable">
                <thead class="table-light">
                    <tr>
                        <th width="35%">Account</th>
                        <th width="25%">Description</th>
                        <th width="17%">Debit (Rp)</th>
                        <th width="17%">Credit (Rp)</th>
                        <th width="6%"></th>
                    </tr>
                </thead>
                <tbody id="linesBody">
                    @for($i = 0; $i < 2; $i++)
                    <tr class="line-row">
                        <td>
                            <select name="lines[{{ $i }}][account_id]" class="form-select form-select-sm account-select" required>
                                <option value="">-- Select Account --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="text" name="lines[{{ $i }}][description]"
                                class="form-control form-control-sm" placeholder="Optional">
                        </td>
                        <td>
                            <input type="number" name="lines[{{ $i }}][debit]"
                                class="form-control form-control-sm debit-input" min="0" step="1" value="0">
                        </td>
                        <td>
                            <input type="number" name="lines[{{ $i }}][credit]"
                                class="form-control form-control-sm credit-input" min="0" step="1" value="0">
                        </td>
                        <td>
                            @if($i >= 2)
                            <button type="button" class="btn btn-sm btn-outline-danger remove-line">×</button>
                            @endif
                        </td>
                    </tr>
                    @endfor
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="2" class="text-end">Total</td>
                        <td><span id="totalDebit">0</span></td>
                        <td><span id="totalCredit">0</span></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div id="balanceAlert" class="alert alert-warning d-none">
        ⚠ Journal is not balanced. Debit must equal Credit.
    </div>

    <button type="submit" class="btn btn-primary">Save Journal (Draft)</button>
</form>

@push('scripts')
<script>
let lineIndex = 2;
const accountOptions = `{!! $accounts->map(fn($a) => "<option value=\"{$a->id}\">{$a->code} — {$a->name}</option>")->implode('') !!}`;

document.getElementById('addLine').addEventListener('click', function () {
    const tbody = document.getElementById('linesBody');
    const tr = document.createElement('tr');
    tr.className = 'line-row';
    tr.innerHTML = `
        <td><select name="lines[${lineIndex}][account_id]" class="form-select form-select-sm account-select" required>
            <option value="">-- Select Account --</option>${accountOptions}</select></td>
        <td><input type="text" name="lines[${lineIndex}][description]" class="form-control form-control-sm" placeholder="Optional"></td>
        <td><input type="number" name="lines[${lineIndex}][debit]" class="form-control form-control-sm debit-input" min="0" step="1" value="0"></td>
        <td><input type="number" name="lines[${lineIndex}][credit]" class="form-control form-control-sm credit-input" min="0" step="1" value="0"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-line">×</button></td>`;
    tbody.appendChild(tr);
    lineIndex++;
    bindEvents();
    updateTotals();
});

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-line')) {
        e.target.closest('tr').remove();
        updateTotals();
    }
});

function updateTotals() {
    let debit = 0, credit = 0;
    document.querySelectorAll('.debit-input').forEach(i => debit += parseFloat(i.value) || 0);
    document.querySelectorAll('.credit-input').forEach(i => credit += parseFloat(i.value) || 0);
    document.getElementById('totalDebit').textContent = debit.toLocaleString('id-ID');
    document.getElementById('totalCredit').textContent = credit.toLocaleString('id-ID');
    const alert = document.getElementById('balanceAlert');
    if (Math.abs(debit - credit) > 0.01 && (debit > 0 || credit > 0)) {
        alert.classList.remove('d-none');
    } else {
        alert.classList.add('d-none');
    }
}

function bindEvents() {
    document.querySelectorAll('.debit-input, .credit-input').forEach(input => {
        input.removeEventListener('input', updateTotals);
        input.addEventListener('input', updateTotals);
    });
}
bindEvents();
</script>
@endpush
@endsection