<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;

class DataExportController extends Controller
{
    public function download(Request $request, $token)
{
    $filePath = null;
    
    try {
        $data = Crypt::decrypt($token);

        if (now()->timestamp > $data['expires_at']) {
            abort(410, 'This download link has expired. Please request a new export from your settings.');
        }

        $filename = $data['filename'];
        $filePath = 'exports/' . $filename;

        
        if (!Storage::disk('local')->exists($filePath)) {
            
           // $allExports = Storage::disk('local')->files('exports');
            
            abort(410, 'This download link has already been used. Each export can only be downloaded once. Please generate a new export if needed.');
        }

        $fileContents = Storage::disk('local')->get($filePath);

        Storage::disk('local')->delete($filePath);

        
        return response($fileContents)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($fileContents))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');

    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
        abort(403, 'This download link is invalid or has been tampered with.');
    } catch (\Exception $e) {
        
        if ($filePath && Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
        }
        
        abort(500, 'An error occurred while downloading your export. Please try again.');
    }
}
    
   
}
