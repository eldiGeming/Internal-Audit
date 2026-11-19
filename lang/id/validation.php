<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pesan validasi (Bahasa Indonesia)
    |--------------------------------------------------------------------------
    |
    | Aturan yang tidak ada di sini memakai pesan bahasa Inggris dari fallback locale.
    |
    */

    'accepted' => ':Attribute harus disetujui.',
    'alpha_dash' => ':Attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'array' => ':Attribute harus berupa daftar.',
    'boolean' => ':Attribute harus bernilai ya atau tidak.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => ':Attribute bukan tanggal yang valid.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'enum' => ':Attribute yang dipilih tidak valid.',
    'exists' => ':Attribute yang dipilih tidak valid.',
    'extensions' => ':Attribute harus berupa file dengan ekstensi: :values.',
    'file' => ':Attribute harus berupa file.',
    'image' => ':Attribute harus berupa gambar.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'integer' => ':Attribute harus berupa bilangan bulat.',
    'max' => [
        'array' => ':Attribute maksimal berisi :max item.',
        'file' => ':Attribute maksimal berukuran :max kilobyte.',
        'numeric' => ':Attribute maksimal bernilai :max.',
        'string' => ':Attribute maksimal :max karakter.',
    ],
    'mimes' => ':Attribute harus berupa file bertipe: :values.',
    'min' => [
        'array' => ':Attribute minimal berisi :min item.',
        'file' => ':Attribute minimal berukuran :min kilobyte.',
        'numeric' => ':Attribute minimal bernilai :min.',
        'string' => ':Attribute minimal :min karakter.',
    ],
    'numeric' => ':Attribute harus berupa angka.',
    'password' => [
        'letters' => ':Attribute harus mengandung minimal satu huruf.',
        'mixed' => ':Attribute harus mengandung huruf besar dan huruf kecil.',
        'numbers' => ':Attribute harus mengandung minimal satu angka.',
        'symbols' => ':Attribute harus mengandung minimal satu simbol.',
        'uncompromised' => ':Attribute ini pernah bocor di internet. Gunakan :attribute lain.',
    ],
    'required' => ':Attribute wajib diisi.',
    'string' => ':Attribute harus berupa teks.',
    'unique' => ':Attribute sudah digunakan.',
    'uploaded' => ':Attribute gagal diunggah.',

    'attributes' => [
        'username' => 'username',
        'email' => 'email',
        'password' => 'password',
        'auditee' => 'auditee',
        'auditor' => 'auditor',
        'question' => 'pertanyaan',
        'evidence' => 'catatan / bukti objektif',
        'criteria' => 'criteria',
        'result' => 'hasil pemeriksaan',
        'severity' => 'kategori temuan',
        'note' => 'catatan',
        'total' => 'total',
        'sesuai' => 'jumlah sesuai',
        'major' => 'jumlah major',
        'minor' => 'jumlah minor',
        'observasi' => 'jumlah observasi',
    ],

];
