<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SuratTemplateController extends Controller
{
    public function edit()
    {
        $template = SuratTemplate::find(1);
        $content = $template->template ?? [];
        $logoKiri = $template->logo_kiri;
        $logoKanan = $template->logo_kanan;
        return view('admin.surat-template.edit', compact('content', 'logoKiri', 'logoKanan'));
    }

    public function update(Request $request)
    {
        $fields = array_keys(\App\Http\Controllers\Admin\SuratController::defaultContent());
        $validated = $request->only($fields);

        $data = ['template' => $validated];

        $template = SuratTemplate::find(1);

        // Handle upload logo kiri
        if ($request->hasFile('logo_kiri')) {
            $file = $request->file('logo_kiri');
            $filename = 'logo-kiri.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/surat'), $filename);
            $data['logo_kiri'] = 'images/surat/' . $filename;
            // Hapus file lama jika ada
            if ($template && $template->logo_kiri && File::exists(public_path($template->logo_kiri))) {
                File::delete(public_path($template->logo_kiri));
            }
        } elseif ($request->input('hapus_logo_kiri') === '1') {
            if ($template && $template->logo_kiri && File::exists(public_path($template->logo_kiri))) {
                File::delete(public_path($template->logo_kiri));
            }
            $data['logo_kiri'] = null;
        }

        // Handle upload logo kanan
        if ($request->hasFile('logo_kanan')) {
            $file = $request->file('logo_kanan');
            $filename = 'logo-kanan.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/surat'), $filename);
            $data['logo_kanan'] = 'images/surat/' . $filename;
            // Hapus file lama jika ada
            if ($template && $template->logo_kanan && File::exists(public_path($template->logo_kanan))) {
                File::delete(public_path($template->logo_kanan));
            }
        } elseif ($request->input('hapus_logo_kanan') === '1') {
            if ($template && $template->logo_kanan && File::exists(public_path($template->logo_kanan))) {
                File::delete(public_path($template->logo_kanan));
            }
            $data['logo_kanan'] = null;
        }

        SuratTemplate::updateOrCreate(
            ['id' => 1],
            $data
        );

        return redirect()->route('surat-template.edit')
            ->with('success', 'Template surat global berhasil diperbarui. Semua surat akan menggunakan template ini.');
    }
}
