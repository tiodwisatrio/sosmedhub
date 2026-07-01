@extends('layouts.admin')

@section('title', 'Edit Faq')

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">Edit Faq</h1>
@endsection

@section('content')
    <div>
        <div class="bg-card rounded-xl shadow-card border border-border p-6">
            <form method="POST" action="{{ route('admin.faqs.update', $faq) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Pertanyaan <span class="text-danger">*</span></label>
                        <x-admin.input-text name="pertanyaan" :value="old('pertanyaan', $faq->pertanyaan)" placeholder="Pertanyaan" />
                        @error('pertanyaan') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Jawaban <span class="text-danger">*</span></label>
                        <x-admin.input-text name="jawaban" :value="old('jawaban', $faq->jawaban)" placeholder="Jawaban" />
                        @error('jawaban') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Urutan</label>
                <x-admin.input-number name="urutan" :value="old('urutan', $faq->urutan)" min="0" />
                @error('urutan') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Status <span class="text-danger">*</span></label>
                <x-admin.select name="status" :options="['1' => 'Aktif', '0' => 'Nonaktif']" :selected="old('status', (string) $faq->status)" />
                @error('status') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-admin.button type="submit">Perbarui</x-admin.button>
                    <a href="{{ route('admin.faqs.index') }}">
                        <x-admin.button type="button" variant="outline">Batal</x-admin.button>
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
