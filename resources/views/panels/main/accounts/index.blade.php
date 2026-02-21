@extends('layouts.panel')

@section('title','Accounts')
@section('panel_name','Main Shop Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <h1 class="h1">Accounts</h1>
        <p class="muted">Accounts created by Admin for your shop.</p>

        <div class="table-responsive mt-3">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $a)
                    <tr>
                        <td><b>{{ $a->name }}</b></td>
                        <td>{{ $a->code ?? '—' }}</td>
                        <td class="text-right">
                            <a class="btn btn-sm btn-primary" href="{{ route('main.accounts.show', $a->id) }}">Open</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-4">No accounts created yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection