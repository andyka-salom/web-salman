<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\CermatReport;
use App\Models\ActionItem;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        View::composer('*', function ($view) {
            $user = Auth::user();

            $supervisorPending = 0;
            $hssePending = 0;
            $pendingCermatCount = 0;
            $myActionCount = 0;

            if ($user) {
                // 1. HITUNG NOTIFIKASI CERMAT REPORT
                // ----------------------------------

                // A. Supervisor: Menunggu Approval Saya
                // Hanya hitung jika user terdaftar sebagai supervisor di laporan tersebut
                // DAN status globalnya masih 'in_review'
                $supervisorPending = CermatReport::where('line_supervisor_id', $user->id)
                    ->where('status', CermatReport::STATUS_IN_REVIEW)
                    ->where('supervisor_status', CermatReport::SUPERVISOR_STATUS_AWAITING)
                    ->count();

                // B. HSSE: Menunggu Review HSSE
                // PERBAIKAN: Gunakan hasRole('hsse') agar SAMA PERSIS dengan @role('hsse') di Blade
                // Jangan gunakan can('manage cermat') karena Supervisor mungkin punya permission itu tapi bukan HSSE
                if ($user->hasRole('hsse')) {
                     $hssePending = CermatReport::where('status', CermatReport::STATUS_IN_REVIEW)
                        ->where('hsse_status', CermatReport::HSSE_STATUS_PENDING_REVIEW)
                        ->count();
                }

                // C. Total Pending (Sidebar)
                // Jika user bukan HSSE, $hssePending akan 0, jadi sidebar tidak akan "kelebihan" hitung
                $pendingCermatCount = $supervisorPending + $hssePending;


                // 2. HITUNG NOTIFIKASI ACTION ITEMS
                $myActionCount = ActionItem::where('responsible_id', $user->id)
                    ->whereIn('status', [ActionItem::STATUS_DO, ActionItem::STATUS_IN_PROGRESS])
                    ->count();
            }

            $view->with([
                'supervisorPendingCount' => $supervisorPending,
                'hssePendingCount'       => $hssePending,
                'pendingCermatCount'     => $pendingCermatCount,
                'myActionCount'          => $myActionCount
            ]);
        });
    }
}
