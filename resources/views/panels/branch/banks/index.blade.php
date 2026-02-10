@extends('layouts.panel')
@section('title','Banks')
@section('panel_name','Branch Panel')

@section('content')
<div class="grid">
  <div class="col-12 card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
      <div>
        <h1 class="h1">Banks</h1>
        <p class="muted">These banks will appear in Branch POS when payment method = Bank.</p>
      </div>
      <a class="btn" href="{{ route('branch.banks.create') }}">+ Add Bank</a>
    </div>

    @if(session('success'))
      <div style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(0,255,170,0.10); border:1px solid rgba(0,255,170,0.20);">
        {{ session('success') }}
      </div>
    @endif

    <div style="margin-top:14px; overflow:auto;">
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="text-align:left; color:rgba(255,255,255,0.7);">
            <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Name</th>
            <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">A/C Title</th>
            <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">A/C #</th>
            <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">IBAN</th>
            <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Active</th>
            <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($banks as $b)
            <tr>
              <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06); font-weight:700;">{{ $b->name }}</td>
              <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->account_title ?? '—' }}</td>
              <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->account_number ?? '—' }}</td>
              <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->iban ?? '—' }}</td>
              <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->is_active ? 'Yes' : 'No' }}</td>
              <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                <a class="btn btn-ghost" href="{{ route('branch.banks.edit', $b->id) }}">Edit</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" style="padding:12px; color:rgba(255,255,255,0.65);">No banks yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
@extends('layouts.panel')
@section('title','Banks')
@section('panel_name','Branch Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="h1">Banks</h1>
                <p class="muted">These banks will appear in Branch POS when payment method = Bank.</p>
            </div>
            <a class="btn" href="{{ route('branch.banks.create') }}">+ Add Bank</a>
        </div>

        @if(session('success'))
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(0,255,170,0.10); border:1px solid rgba(0,255,170,0.20);">
            {{ session('success') }}
        </div>
        @endif

        <div style="margin-top:14px; overflow:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left; color:rgba(255,255,255,0.7);">
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Name</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">A/C Title</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">A/C #</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">IBAN</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Active</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banks as $b)
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06); font-weight:700;">{{
                            $b->name }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->account_title
                            ?? '—' }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->account_number
                            ?? '—' }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->iban ?? '—' }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->is_active ?
                            'Yes' : 'No' }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            <a class="btn btn-ghost" href="{{ route('branch.banks.edit', $b->id) }}">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding:12px; color:rgba(255,255,255,0.65);">No banks yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection