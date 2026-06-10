<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::orderBy('created_at', 'desc')->get();
        return view('email_templates.index', compact('templates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:email_templates,name'],
            'subject' => ['required', 'string', 'max:255'],
            'html_body' => ['required', 'string'],
        ]);

        EmailTemplate::create($data);

        return back()->with('status', 'Plantilla de correo creada correctamente.');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();
        return back()->with('status', 'Plantilla de correo eliminada.');
    }

    public function sync()
    {
        $apiKey = env('RESEND_API_KEY');
        if (!$apiKey) {
            return back()->withErrors(['error' => 'API Key de Resend no configurada.']);
        }

        $response = Http::withToken($apiKey)->get('https://api.resend.com/templates');

        if (!$response->successful()) {
            return back()->withErrors(['error' => 'No se pudieron obtener las plantillas de Resend.']);
        }

        $templates = $response->json('data') ?? [];

        foreach ($templates as $t) {
            $templateData = Http::withToken($apiKey)->get("https://api.resend.com/templates/{$t['id']}")->json();
            
            EmailTemplate::updateOrCreate(
                ['resend_id' => $t['id']],
                [
                    'name' => $t['name'] ?? $t['id'],
                    'subject' => $templateData['subject'] ?? '',
                    'html_body' => $templateData['html'] ?? '',
                ]
            );
        }

        return back()->with('status', 'Plantillas sincronizadas desde Resend exitosamente.');
    }
}
