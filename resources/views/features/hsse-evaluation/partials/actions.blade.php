{{--
    resources/views/features/hsse-evaluation/partials/actions.blade.php

    Variabel:
      $r          → HsseEvaluation instance
      $canManage  → bool  (true = super-admin / hsse | false = koordinator)

    Logika:
      👁  Detail  → semua role
      ✏️  Edit    → hanya jika status=draft ATAU canManage (super-admin/hsse)
      🗑  Delete  → hanya canManage, hard-delete
--}}

<div class="d-flex gap-1 justify-content-center">

    {{-- ── DETAIL: semua role ────────────────────────────────── --}}
    <a href="{{ route('hsse-evaluation.show', $r->id) }}"
       class="btn btn-sm btn-outline-info"
       title="Lihat Detail">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
             viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
        </svg>
    </a>

    {{-- ── EDIT: draft boleh siapa saja yg canManage; submitted hanya canManage ── --}}
    @if($r->status === 'draft' || $canManage)
    <a href="{{ route('hsse-evaluation.edit', $r->id) }}"
       class="btn btn-sm btn-outline-warning"
       title="Edit Evaluasi">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
             viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
        </svg>
    </a>
    @endif

    {{-- ── DELETE: hanya canManage, hard-delete ─────────────────── --}}
    @if($canManage)
    <button type="button"
            class="btn btn-sm btn-outline-danger delete-eval"
            data-url="{{ route('hsse-evaluation.destroy', $r->id) }}"
            data-name="{{ $r->crew_name }}"
            title="Hapus Permanen">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
             viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
            <path d="M10 11v6"></path>
            <path d="M14 11v6"></path>
            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path>
        </svg>
    </button>
    @endif

</div>
