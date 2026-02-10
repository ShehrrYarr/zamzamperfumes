@extends('layouts.panel')

@section('title','Edit Bank')
@section('panel_name','Branch Panel')

@section('content')
<div class="grid">
    <div class="col-12 card" style="max-width:900px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="h1">Edit Bank</h1>
                <p class="muted">Update bank details for POS bank payments.</p>
            </div>
            <a class="btn btn-ghost" href="{{ route('branch.banks.index') }}">← Back</a>
        </div>

        @if ($errors->any())
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(255,0,90,0.10); border:1px solid rgba(255,0,90,0.25);">
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('branch.banks.update', $bank->id) }}"
            style="margin-top:14px; display:grid; gap:12px;">
            @csrf
            @method('PUT')

            <div class="grid">
                <div class="col-6">
                    <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">Bank Name</label>
                    <input name="name" required value="{{ old('name', $bank->name) }}"
                        style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
                </div>
                <div class="col-6">
                    <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">Account Title
                        (optional)</label>
                    <input name="account_title" value="{{ old('account_title', $bank->account_title) }}"
                        style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
                </div>
            </div>

            <div class="grid">
                <div class="col-6">
                    <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">Account Number
                        (optional)</label>
                    <input name="account_number" value="{{ old('account_number', $bank->account_number) }}"
                        style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
                </div>
                <div class="col-6">
                    <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">IBAN
                        (optional)</label>
                    <input name="iban" value="{{ old('iban', $bank->iban) }}"
                        style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
                </div>
            </div>

            <label style="display:flex; gap:10px; align-items:center; color:rgba(255,255,255,0.8);">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $bank->is_active) ? 'checked' : ''
                }}>
                Active (show in POS)
            </label>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button class="btn" type="submit">Save Changes</button>
                <a class="btn btn-ghost" href="{{ route('branch.banks.index') }}">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection