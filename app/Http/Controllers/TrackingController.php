<?php
namespace App\Http\Controllers;

use App\Services\TrackingService;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function __construct(private TrackingService $tracking) {}

    // Open tracking pixel: GET /t/o/{emailId}
    public function open(Request $request, string $emailId)
    {
        $this->tracking->recordOpen($emailId, $request->ip(), $request->userAgent());

        // Return 1x1 transparent GIF
        return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'))
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    // Click tracking: GET /t/c/{emailId}?url=...
    public function click(Request $request, string $emailId)
    {
        $url = $request->get('url', '/');
        $this->tracking->recordClick($emailId, $url, $request->ip(), $request->userAgent());
        return redirect($url);
    }
}
