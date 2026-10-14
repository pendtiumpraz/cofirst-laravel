<?php

namespace App\Http\Requests\Academic;

use App\Models\Material;
use App\Models\Syllabus;
use App\Services\Academic\AcademicSettings;
use App\Services\Academic\MaterialFileService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Create or update a learning material (admin).
 *
 * The meeting range defaults to the meeting of the chosen syllabus and must start
 * at that meeting (a material of meeting 3 cannot claim meetings 10-12). Content is
 * Markdown (rendered without raw HTML). Files go to the private disk.
 */
class MaterialRequest extends FormRequest
{
    /** @var array<int, Syllabus|null> */
    private array $syllabusCache = [];

    /** Only admins manage materials. */
    public function authorize(): bool
    {
        $material = $this->route('material');

        return $material instanceof Material
            ? $this->user()->can('update', $material)
            : $this->user()->can('create', Material::class);
    }

    /** Default the meeting range and order before validating. */
    protected function prepareForValidation(): void
    {
        $syllabus = $this->syllabus();

        if ($syllabus) {
            $this->merge([
                'meeting_start' => $this->filled('meeting_start') ? $this->input('meeting_start') : $syllabus->meeting_number,
                'meeting_end' => $this->filled('meeting_end') ? $this->input('meeting_end') : ($this->input('meeting_start') ?: $syllabus->meeting_number),
            ]);
        }

        if (! $this->filled('order') && $syllabus) {
            $this->merge(['order' => (int) Material::where('syllabus_id', $syllabus->id)->max('order') + 1]);
        }
    }

    /**
     * Validation rules.
     *
     * Returns:
     *     array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'syllabus_id' => ['required', 'integer', Rule::exists('syllabuses', 'id')->whereNull('deleted_at')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string', 'max:50000'],
            'type' => ['required', Rule::in(array_keys(Material::getTypes()))],
            'meeting_start' => ['required', 'integer', 'min:1', 'max:200'],
            'meeting_end' => ['required', 'integer', 'gte:meeting_start', 'max:200'],
            'external_url' => ['nullable', 'url:https,http', 'max:2000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'order' => ['required', 'integer', 'min:1', 'max:999'],
            // Reason: `extensions` checks the name, `mimes` the real content (mpga = mp3).
            'file' => [
                'nullable', 'file',
                'extensions:'.implode(',', MaterialFileService::EXTENSIONS),
                'mimes:'.implode(',', [...MaterialFileService::EXTENSIONS, 'mpga']),
                'max:'.AcademicSettings::maxUploadKb(),
            ],
            'remove_file' => ['nullable', 'boolean'],
        ];
    }

    /** The meeting range must start at the syllabus meeting and stay inside the curriculum. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $syllabus = $this->syllabus();

            if (! $syllabus || $validator->errors()->hasAny(['meeting_start', 'meeting_end'])) {
                return;
            }

            if ((int) $this->input('meeting_start') !== (int) $syllabus->meeting_number) {
                $validator->errors()->add('meeting_start', "Materi silabus ini dimulai di pertemuan {$syllabus->meeting_number}.");
            }

            $lastMeeting = (int) Syllabus::where('curriculum_id', $syllabus->curriculum_id)->max('meeting_number');

            if ((int) $this->input('meeting_end') > $lastMeeting) {
                $validator->errors()->add('meeting_end', "Kurikulum ini hanya sampai pertemuan {$lastMeeting}.");
            }

            if (! $this->hasFile('file') && blank($this->input('content')) && blank($this->input('external_url'))
                && ! ($this->route('material')?->file_path && ! $this->boolean('remove_file'))) {
                $validator->errors()->add('content', 'Isi materi, tautan, atau berkas wajib diisi salah satu.');
            }
        });
    }

    /** Indonesian field names. */
    public function attributes(): array
    {
        return [
            'syllabus_id' => 'pertemuan (silabus)',
            'title' => 'judul materi',
            'description' => 'ringkasan',
            'content' => 'isi materi',
            'type' => 'jenis materi',
            'meeting_start' => 'pertemuan mulai',
            'meeting_end' => 'pertemuan akhir',
            'external_url' => 'tautan',
            'status' => 'status',
            'order' => 'urutan',
            'file' => 'berkas',
        ];
    }

    /** The chosen syllabus (not archived), looked up once. */
    private function syllabus(): ?Syllabus
    {
        $id = $this->input('syllabus_id');

        if (! is_numeric($id)) {
            return null;
        }

        if (! array_key_exists((int) $id, $this->syllabusCache)) {
            $this->syllabusCache[(int) $id] = Syllabus::find((int) $id);
        }

        return $this->syllabusCache[(int) $id];
    }
}
