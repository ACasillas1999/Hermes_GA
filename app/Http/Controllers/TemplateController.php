<?php

namespace App\Http\Controllers;

use App\Models\WabaTemplate;
use App\Services\WabaClient;
use App\Services\WabaTemplateSyncService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = WabaTemplate::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('templates.index', [
            'templates' => $templates,
            'templatesForJs' => $templates->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'language' => $template->language,
                    'status' => $template->status,
                    'category' => $template->category,
                    'bodyText' => $template->bodyText(),
                    'bodyCount' => $template->bodyParameterCount(),
                    'headerFormat' => $template->headerFormat(),
                    'headerText' => $template->headerText(),
                    'headerCount' => $template->headerParameterCount(),
                    'headerRequiresMedia' => $template->headerRequiresMedia(),
                    'components' => $template->components,
                ];
            })->values(),
        ]);
    }

    public function store(Request $request, WabaClient $client, WabaTemplateSyncService $service)
    {
        $normalizedName = Str::of((string) $request->input('name'))
            ->lower()
            ->trim()
            ->replaceMatches('/\s+/', '_')
            ->replaceMatches('/[^a-z0-9_]/', '')
            ->toString();

        $request->merge([
            'name' => $normalizedName,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:512', 'regex:/^[a-z0-9_]+$/'],
            'language' => ['required', 'string', 'max:32'],
            'category' => ['required', 'string', 'in:MARKETING,UTILITY,AUTHENTICATION'],
            'header_type' => ['nullable', 'string', 'in:NONE,TEXT,IMAGE,VIDEO,DOCUMENT'],
            'header_text' => ['nullable', 'string', 'max:1024'],
            'header_media_url' => ['nullable', 'url', 'max:2048'],
            'body_text' => ['required', 'string', 'max:2048'],
            'footer_text' => ['nullable', 'string', 'max:1024'],
        ], [
            'name.regex' => 'El nombre debe estar en minusculas y solo usar letras, numeros o guion bajo.',
        ]);

        $headerType = strtoupper($validated['header_type'] ?? 'NONE');
        $bodyText = trim($validated['body_text']);
        $components = [];

        if ($headerType !== 'NONE') {
            if ($headerType === 'TEXT') {
                $headerText = trim((string) ($validated['header_text'] ?? ''));
                if ($headerText === '') {
                    return back()
                        ->withInput()
                        ->withErrors(['header_text' => 'El encabezado de texto es requerido.']);
                }

                $headerComponent = [
                    'type' => 'HEADER',
                    'format' => 'TEXT',
                    'text' => $headerText,
                ];

                preg_match_all('/\{\{(\d+)\}\}/', $headerText, $matches);
                if (!empty($matches[1])) {
                    $maxIndex = max(array_map('intval', $matches[1]));
                    $examples = [];
                    for ($i = 1; $i <= $maxIndex; $i += 1) {
                        $examples[] = 'Ejemplo '.$i;
                    }
                    $headerComponent['example'] = [
                        'header_text' => $examples,
                    ];
                }

                $components[] = $headerComponent;
            } else {
                $mediaUrl = $validated['header_media_url'] ?? '';
                if ($mediaUrl === '') {
                    return back()
                        ->withInput()
                        ->withErrors(['header_media_url' => 'La URL de ejemplo es requerida para encabezado multimedia.']);
                }

                $components[] = [
                    'type' => 'HEADER',
                    'format' => $headerType,
                    'example' => [
                        'header_handle' => [$mediaUrl],
                    ],
                ];
            }
        }

        $bodyComponent = [
            'type' => 'BODY',
            'text' => $bodyText,
        ];

        preg_match_all('/\{\{(\d+)\}\}/', $bodyText, $matches);
        if (!empty($matches[1])) {
            $maxIndex = max(array_map('intval', $matches[1]));
            $examples = [];
            for ($i = 1; $i <= $maxIndex; $i += 1) {
                $examples[] = 'Ejemplo '.$i;
            }
            $bodyComponent['example'] = [
                'body_text' => [$examples],
            ];
        }

        $components[] = $bodyComponent;

        $footerText = trim((string) ($validated['footer_text'] ?? ''));
        if ($footerText !== '') {
            $components[] = [
                'type' => 'FOOTER',
                'text' => $footerText,
            ];
        }

        try {
            $client->createTemplate(
                $validated['name'],
                $validated['language'],
                $validated['category'],
                $components
            );

            $service->sync();
        } catch (RequestException|Throwable $exception) {
            return back()->withErrors([
                'create' => 'Error al crear plantilla: '.$exception->getMessage(),
            ])->withInput();
        }

        return redirect()
            ->route('templates.index')
            ->with('status', 'Plantilla creada y enviada a Meta. Puede tardar en aprobarse.');
    }
    public function sync(Request $request, WabaTemplateSyncService $service)
    {
        try {
            $result = $service->sync();
        } catch (RequestException|Throwable $exception) {
            return back()->withErrors([
                'sync' => 'Error al sincronizar plantillas: '.$exception->getMessage(),
            ]);
        }

        return back()->with('status', 'Plantillas sincronizadas: '.$result['updated']);
    }

    public function list()
    {
        $templates = WabaTemplate::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return response()->json($templates);
    }
}
