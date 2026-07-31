<?php
namespace App\Http\Middleware;

use App\Models\Dispensasi;
use App\Services\DispensasiService;
use Closure;
use Illuminate\Http\Request;

class PrintLimitMiddleware
{
    public function handle(Request $request, Closure $next, DispensasiService $service)
    {
        $id = $request->route('dispensasi');
        $dispensasi = Dispensasi::find($id);

        if (!$dispensasi) abort(404);

        $check = $service->canPrint($dispensasi);
        if (!$check['allowed']) {
            return redirect()->back()->with('error', $check['reason']);
        }

        return $next($request);
    }
}
