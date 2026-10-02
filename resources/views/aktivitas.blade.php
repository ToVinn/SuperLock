@extends('layouts.app')

@section('title', 'Log Aktivitas')

@push('head')
<style>
.tbl-log { width: 100%; border-collapse: collapse; font-size: 13px; }
.tbl-log th { text-align: left; padding: 10px; border-bottom: 2px solid var(--garis); color: var(--abu); }
.tbl-log td { padding: 10px; border-bottom: 1px solid var(--garis); vertical-align: top; }
.tbl-log tr:hover td { background: #f8fafc; }
.log-waktu { font-family: Consolas, monospace; font-size: 11.5px; color: var(--abu); white-space: nowrap; }
.log-pelaku b { display: block; font-size: 13px; color: var(--teks); }
.log-pelaku span { font-size: 11px; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 4px; }
.role-admin { background: #fee2e2; color: #b91c1c; }
.role-guru { background: #dcfce7; color: #15803d; }
.role-km { background: #e0e7ff; color: #4338ca; }
.aksi-badge { font-weight: 600; padding: 3px 8px; border-radius: 6px; font-size: 11.5px; background: #f1f5f9; color: #475569; display: inline-block; white-space: nowrap; }
.log-desc { line-height: 1.4; }
.pagination { display: flex; justify-content: space-between; align-items: center; margin-top: 16px; font-size: 13px; }
.pagination a { color: var(--biru); text-decoration: none; padding: 6px 12px; border: 1px solid var(--garis); border-radius: 6px; }
.pagination a:hover { background: #f8fafc; border-color: var(--biru); }
</style>
@endpush

@section('content')
<div class="kartu-head" style="margin-bottom:16px">
    <div>
        <h1 style="margin:0 0 4px">Log Aktivitas</h1>
        <p style="font-size:13px;color:var(--abu)">Catatan riwayat aktivitas pengguna di sistem (hanya admin).</p>
    </div>
</div>

<div class="card" style="padding:16px">
    @if ($rows->isEmpty())
        <p style="color:var(--abu);margin:0">Belum ada catatan aktivitas.</p>
    @else
        <div style="overflow-x:auto">
            <table class="tbl-log">
                <thead>
                    <tr>
                        <th style="width:15%">Waktu</th>
                        <th style="width:15%">Pengguna</th>
                        <th style="width:15%">Aksi</th>
                        <th style="width:55%">Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $r)
                    <tr>
                        <td class="log-waktu">
                            <span style="display:block;color:var(--teks);font-weight:600;font-size:12.5px">{{ $r->created_at->format('d/m/Y') }}</span>
                            {{ $r->created_at->format('H:i:s') }}
                        </td>
                        <td class="log-pelaku">
                            <b>{{ $r->user ? $r->user->nama : 'Sistem/Dihapus' }}</b>
                            @if ($r->user)
                                <span class="role-{{ $r->user->role }}">{{ strtoupper($r->user->role) }}</span>
                            @endif
                        </td>
                        <td><span class="aksi-badge">{{ $r->aksi }}</span></td>
                        <td class="log-desc">{{ $r->deskripsi }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if ($rows->hasPages())
        <div class="pagination">
            <div>
                @if (!$rows->onFirstPage())
                    <a href="{{ $rows->previousPageUrl() }}">&laquo; Sebelumnya</a>
                @endif
            </div>
            <div style="color:var(--abu)">Halaman {{ $rows->currentPage() }} dari {{ $rows->lastPage() }}</div>
            <div>
                @if ($rows->hasMorePages())
                    <a href="{{ $rows->nextPageUrl() }}">Selanjutnya &raquo;</a>
                @endif
            </div>
        </div>
        @endif
    @endif
</div>
@endsection
