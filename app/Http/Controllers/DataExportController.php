<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;

class DataExportController extends Controller
{
    
    public function download(Request $request, $token)
    {
       try {
        $data = Crypt::decrypt($token);

        if (now()->timestamp > $data['expires_at']) {
            \Log::warning('Token expired for file: ' . $data['filename']);
            abort(410, 'This download link has expired. Please request a new export from your settings.');
        }

        $filename = $data['filename'];
        $filePath = 'exports/' . $filename;

        if (!Storage::disk('local')->exists($filePath)) {
            \Log::warning('File not found: ' . $filePath);
            abort(404, 'Export file not found. It may have been deleted.');
        }

        $fileContents = Storage::disk('local')->get($filePath);

        $deleted = Storage::disk('local')->delete($filePath);
        if ($deleted) {
        } else {
            \Log::error('Failed to delete file: ' . $filePath);
        }

        return response($fileContents)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($fileContents))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');

    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
        \Log::error('Invalid token: ' . $e->getMessage());
        abort(403, 'This download link is invalid or has been tampered with.');
    } catch (\Exception $e) {
        \Log::error('Export download error: ' . $e->getMessage());
        abort(500, 'An error occurred while downloading your export. Please try again.');
    }
    }
}
