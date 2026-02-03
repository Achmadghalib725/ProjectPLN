<?php

namespace App\Http\Controllers;

use App\Models\GroundingTest;
use App\Models\GroundingTestItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class GroundingTestController extends Controller
{
    public function index()
    {
        $groundingTests = GroundingTest::query()
            ->with(['creator'])
            ->withCount('items')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(10);

        return view('grounding-tests.index', compact('groundingTests'));
    }

    public function create()
    {
        return view('grounding-tests.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $groundingTest = DB::transaction(function () use ($validated, $request) {
            $groundingTest = GroundingTest::create([
                'nomor' => null,
                'nama_pembuat' => $validated['nama_pembuat'],
                'tanggal' => $validated['tanggal'],
                'catatan' => $validated['catatan'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $this->syncItems($groundingTest, $request);

            return $groundingTest;
        });

        return redirect()
            ->route('grounding-tests.show', $groundingTest)
            ->with('success', 'Surat hasil uji grounding berhasil dibuat.');
    }

    public function show(GroundingTest $groundingTest)
    {
        $groundingTest->load(['items', 'creator']);

        return view('grounding-tests.show', compact('groundingTest'));
    }

    public function edit(GroundingTest $groundingTest)
    {
        $groundingTest->load('items');

        return view('grounding-tests.edit', compact('groundingTest'));
    }

    public function update(Request $request, GroundingTest $groundingTest)
    {
        $validated = $this->validatePayload($request, $groundingTest);

        DB::transaction(function () use ($groundingTest, $validated, $request) {
            $groundingTest->update([
                'nomor' => null,
                'nama_pembuat' => $validated['nama_pembuat'],
                'tanggal' => $validated['tanggal'],
                'catatan' => $validated['catatan'] ?? null,
            ]);

            $this->syncItems($groundingTest, $request);
        });

        return redirect()
            ->route('grounding-tests.show', $groundingTest)
            ->with('success', 'Surat hasil uji grounding berhasil diperbarui.');
    }

    public function destroy(GroundingTest $groundingTest)
    {
        $groundingTest->load('items');

        foreach ($groundingTest->items as $item) {
            $this->deleteAttachment($item->attachment_path);
        }

        $groundingTest->delete();

        return redirect()
            ->route('grounding-tests.index')
            ->with('success', 'Surat hasil uji grounding berhasil dihapus.');
    }

    public function pdf(GroundingTest $groundingTest)
    {
        $groundingTest->load(['items', 'creator']);

        $pdf = Pdf::loadView('pdf.grounding-test', compact('groundingTest'));
        $pdf->setPaper('A4', 'portrait');

        $safeNomor = $groundingTest->tanggal?->format('Y-m-d') ?? ('grounding-' . $groundingTest->id);

        return $pdf->download('surat-hasil-uji-grounding-' . $safeNomor . '.pdf');
    }


    public function previewPdf(GroundingTest $groundingTest)
    {
        $groundingTest->load(['items', 'creator']);

        $pdf = Pdf::loadView('pdf.grounding-test', compact('groundingTest'));
        $pdf->setPaper('A4', 'portrait');

        $safeNomor = $groundingTest->tanggal?->format('Y-m-d') ?? ('grounding-' . $groundingTest->id);

        return $pdf->stream('surat-hasil-uji-grounding-' . $safeNomor . '.pdf');
    }

    private function validatePayload(Request $request, ?GroundingTest $groundingTest = null): array
    {
        $rules = [
            'nama_pembuat' => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.titik_ukur' => ['required', 'string', 'max:255'],
            'items.*.kriteria' => ['required', 'regex:/^\\d+(?:[.,]\\d{1,2})?$/'],
            'items.*.hasil_uji' => ['required', 'regex:/^\\d+(?:[.,]\\d{1,2})?$/'],
            'items.*.attachment' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
        ];

        if ($groundingTest) {
            $rules['items.*.id'] = [
                'nullable',
                'integer',
                Rule::exists('grounding_test_items', 'id')->where('grounding_test_id', $groundingTest->id),
            ];
            $rules['items.*.delete_attachment'] = ['nullable', 'boolean'];
        }

        $messages = [
            'nama_pembuat.required' => 'Nama pembuat wajib diisi.',
            'nama_pembuat.max' => 'Nama pembuat maksimal 100 karakter.',
            'tanggal.required' => 'Tanggal uji wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'items.required' => 'Minimal satu titik ukur wajib diisi.',
            'items.array' => 'Format detail titik ukur tidak valid.',
            'items.min' => 'Minimal satu titik ukur wajib diisi.',
            'items.*.titik_ukur.required' => 'Titik ukur grounding wajib diisi.',
            'items.*.kriteria.required' => 'Kriteria wajib diisi.',
            'items.*.kriteria.regex' => 'Kriteria harus berupa angka desimal (maksimal 2 angka di belakang koma). Gunakan titik atau koma.',
            'items.*.hasil_uji.required' => 'Hasil uji wajib diisi.',
            'items.*.hasil_uji.regex' => 'Hasil uji harus berupa angka desimal (maksimal 2 angka di belakang koma). Gunakan titik atau koma.',
            'items.*.attachment.image' => 'Lampiran harus berupa gambar.',
            'items.*.attachment.mimes' => 'Format gambar harus JPG, JPEG, atau PNG.',
            'items.*.attachment.max' => 'Ukuran gambar maksimal 10MB.',
        ];

        return $request->validate($rules, $messages);
    }

    private function syncItems(GroundingTest $groundingTest, Request $request): void
    {
        $itemsInput = $request->input('items', []);
        $existingItems = $groundingTest->items()->get()->keyBy('id');
        $incomingIds = collect($itemsInput)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        $idsToDelete = $existingItems->keys()->diff($incomingIds);
        foreach ($idsToDelete as $id) {
            $item = $existingItems->get($id);
            if ($item) {
                $this->deleteAttachment($item->attachment_path);
                $item->delete();
            }
        }

        foreach ($itemsInput as $index => $itemData) {
            $file = $request->file("items.{$index}.attachment");
            $deleteAttachment = !empty($itemData['delete_attachment']);

            if (!empty($itemData['id']) && $existingItems->has((int) $itemData['id'])) {
                $item = $existingItems->get((int) $itemData['id']);
                $payload = [
                    'titik_ukur' => $itemData['titik_ukur'],
                    'kriteria' => $this->normalizeDecimal($itemData['kriteria'] ?? null),
                    'hasil_uji' => $this->normalizeDecimal($itemData['hasil_uji'] ?? null),
                ];

                if ($file) {
                    $this->deleteAttachment($item->attachment_path);
                    $payload['attachment_path'] = $this->storeOptimizedAttachment($file);
                    $payload['attachment_name'] = $file->getClientOriginalName();
                } elseif ($deleteAttachment) {
                    $this->deleteAttachment($item->attachment_path);
                    $payload['attachment_path'] = null;
                    $payload['attachment_name'] = null;
                }

                $item->update($payload);
                continue;
            }

            $path = $file ? $this->storeOptimizedAttachment($file) : null;
            GroundingTestItem::create([
                'grounding_test_id' => $groundingTest->id,
                'titik_ukur' => $itemData['titik_ukur'],
                'kriteria' => $this->normalizeDecimal($itemData['kriteria'] ?? null),
                'hasil_uji' => $this->normalizeDecimal($itemData['hasil_uji'] ?? null),
                'attachment_path' => $path,
                'attachment_name' => $file?->getClientOriginalName(),
            ]);
        }
    }

    private function storeOptimizedAttachment(UploadedFile $file): string
    {
        $disk = Storage::disk('public');
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $path = 'grounding-test-attachments/' . uniqid() . '_' . time() . '.' . $ext;
        $maxDimension = 1600;
        $jpegQuality = 82;
        $pngCompression = 6;

        try {
            $imageInfo = @getimagesize($file->getPathname());
            if (!$imageInfo) {
                return $file->store('grounding-test-attachments', 'public');
            }

            $imageType = $imageInfo[2] ?? null;
            if (!in_array($imageType, [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
                return $file->store('grounding-test-attachments', 'public');
            }

            $source = $imageType === IMAGETYPE_PNG
                ? @imagecreatefrompng($file->getPathname())
                : @imagecreatefromjpeg($file->getPathname());

            if (!$source) {
                return $file->store('grounding-test-attachments', 'public');
            }

            if ($imageType === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
                $exif = @exif_read_data($file->getPathname());
                $orientation = (int) ($exif['Orientation'] ?? 1);
                if ($orientation === 3) {
                    $source = imagerotate($source, 180, 0);
                } elseif ($orientation === 6) {
                    $source = imagerotate($source, -90, 0);
                } elseif ($orientation === 8) {
                    $source = imagerotate($source, 90, 0);
                }
            }

            $width = imagesx($source);
            $height = imagesy($source);
            $largestSide = max($width, $height);
            $scale = $largestSide > 0 ? min(1, $maxDimension / $largestSide) : 1;

            if ($scale < 1) {
                $newWidth = (int) floor($width * $scale);
                $newHeight = (int) floor($height * $scale);
                $resized = imagecreatetruecolor($newWidth, $newHeight);
                if ($imageType === IMAGETYPE_PNG) {
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                    $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                    imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
                }
                imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($source);
                $source = $resized;
            }

            ob_start();
            if ($imageType === IMAGETYPE_PNG) {
                imagepng($source, null, $pngCompression);
            } else {
                imagejpeg($source, null, $jpegQuality);
            }
            $binary = ob_get_clean();
            imagedestroy($source);

            if ($binary === false) {
                return $file->store('grounding-test-attachments', 'public');
            }

            $disk->put($path, $binary);
            return $path;
        } catch (\Throwable $e) {
            return $file->store('grounding-test-attachments', 'public');
        }
    }

    private function deleteAttachment(?string $path): void
    {
        if (!$path) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function normalizeDecimal(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = str_replace(',', '.', trim($value));

        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        return $normalized;
    }

}