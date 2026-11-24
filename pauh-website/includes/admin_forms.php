<?php
/**
 * includes/admin_forms.php
 * Komponen form bersama untuk halaman admin Pengaturan Situs & Konten Halaman.
 * Satu definisi field (lihat settingsSchema() & contentSections() di settings.php)
 * dipakai untuk menampilkan form, memvalidasi, dan menyimpan — sehingga menambah
 * field baru cukup di satu tempat.
 */

/** Nama input file untuk field gambar (titik pada key diganti agar valid sebagai nama). */
function fileInputName(string $key): string
{
    return 'up_' . preg_replace('/[^a-z0-9]+/i', '__', $key);
}

function fieldDomId(string $key): string
{
    return 'f_' . preg_replace('/[^a-z0-9]+/i', '_', $key);
}

/**
 * Tentukan nilai akhir sebuah field gambar dari upload / URL / centang hapus.
 * Mengembalikan nilai yang akan disimpan. Pemanggil bertanggung jawab memanggil
 * deleteSiteImage($lama) setelah penyimpanan sukses jika nilainya berubah.
 */
function resolveImageField(string $key, string $label, string $current, array &$errors): string
{
    $input     = fileInputName($key);
    $postedUrl = clean($_POST['f'][$key] ?? '');
    $remove    = !empty($_POST['remove'][$key]);

    if (!empty($_FILES[$input]['name'])) {
        $upload = handleSiteUpload($_FILES[$input]);
        if (!$upload['success']) {
            $errors[] = $label . ': ' . $upload['error'];
            return $current;
        }
        return (string) $upload['filename'];
    }
    if ($postedUrl !== '' && $postedUrl !== $current) {
        if (!preg_match('#^https?://[^\s]+$#i', $postedUrl) || mb_strlen($postedUrl) > 255) {
            $errors[] = $label . ': URL gambar harus diawali http:// atau https:// dan maksimal 255 karakter.';
            return $current;
        }
        return $postedUrl;
    }
    return $remove ? '' : $current;
}

/**
 * Validasi & normalisasi nilai satu field (selain gambar) dari $_POST['f'][key].
 * Mengisi $errors bila tidak valid. Mengembalikan nilai bersih untuk disimpan.
 */
function readFieldValue(array $field, string $current, array &$errors): string
{
    $key   = $field['key'];
    $label = $field['label'];
    $type  = $field['type'];
    $raw   = $_POST['f'][$key] ?? '';
    $value = is_string($raw) ? clean($raw) : '';

    switch ($type) {
        case 'toggle':
            return $value === '1' ? '1' : '0';

        case 'longtext':
            // Samakan baris baru (Windows/Mac/Linux) agar paragraf dikenali konsisten.
            $value = str_replace(["\r\n", "\r"], "\n", $value);
            break;

        case 'color':
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
                $errors[] = $label . ': format warna tidak valid.';
                return $current;
            }
            return strtolower($value);

        case 'number':
            $min = (int) ($field['min'] ?? 0);
            $max = (int) ($field['max'] ?? 1000);
            if (!ctype_digit($value) || (int) $value < $min || (int) $value > $max) {
                $errors[] = $label . ": isi angka antara $min dan $max.";
                return $current;
            }
            return (string) (int) $value;

        case 'select':
            if (!array_key_exists($value, $field['options'] ?? [])) {
                $errors[] = $label . ': pilihan tidak valid.';
                return $current;
            }
            return $value;

        case 'link':
            $normalized = normalizeLinkInput($value);
            if ($normalized === null) {
                $errors[] = $label . ': tautan tidak valid. Gunakan /, #bagian, /halaman, https://…, mailto: atau tel:.';
                return $current;
            }
            $value = $normalized;
            break;
    }

    if (!empty($field['required']) && $value === '') {
        $errors[] = $label . ' wajib diisi.';
    }
    if (isset($field['max']) && $type !== 'number' && mb_strlen($value) > (int) $field['max']) {
        $errors[] = $label . ' maksimal ' . (int) $field['max'] . ' karakter.';
    }
    return $value;
}

/** Cetak satu field form sesuai tipenya. $value = nilai saat ini. */
function renderField(array $field, string $value): void
{
    $key   = $field['key'];
    $id    = fieldDomId($key);
    $name  = 'f[' . e($key) . ']';
    $label = $field['label'];
    $type  = $field['type'];
    $req   = !empty($field['required']);
    $hint  = $field['hint'] ?? '';
    $max   = isset($field['max']) && $type !== 'number' ? ' maxlength="' . (int) $field['max'] . '"' : '';

    echo '<div class="form-group">';

    if ($type === 'toggle') {
        echo '<input type="hidden" name="' . $name . '" value="0">';
        echo '<label class="toggle-row" for="' . e($id) . '">';
        echo '<input type="checkbox" id="' . e($id) . '" name="' . $name . '" value="1"' . ($value === '1' ? ' checked' : '') . '> ';
        echo '<span>' . e($label) . '</span></label>';
    } else {
        echo '<label for="' . e($id) . '">' . e($label) . ($req ? ' <span style="color:var(--red)">*</span>' : '') . '</label>';

        if ($type === 'textarea') {
            echo '<textarea id="' . e($id) . '" name="' . $name . '" class="form-control" rows="3"' . $max . '>' . e($value) . '</textarea>';
        } elseif ($type === 'longtext') {
            echo '<textarea id="' . e($id) . '" name="' . $name . '" class="form-control" rows="12"' . $max . ' style="line-height:1.6">' . e($value) . '</textarea>';
        } elseif ($type === 'color') {
            $safe = preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? $value : '#000000';
            echo '<div class="color-row"><input type="color" id="' . e($id) . '" name="' . $name . '" value="' . e($safe) . '" class="color-input">';
            echo '<code>' . e($safe) . '</code></div>';
        } elseif ($type === 'number') {
            echo '<input type="number" id="' . e($id) . '" name="' . $name . '" class="form-control" style="max-width:140px" value="' . e($value) . '" min="' . (int) ($field['min'] ?? 0) . '" max="' . (int) ($field['max'] ?? 1000) . '">';
        } elseif ($type === 'select') {
            echo '<select id="' . e($id) . '" name="' . $name . '" class="form-control">';
            foreach ($field['options'] as $optValue => $optLabel) {
                echo '<option value="' . e((string) $optValue) . '"' . ((string) $optValue === $value ? ' selected' : '') . '>' . e($optLabel) . '</option>';
            }
            echo '</select>';
        } elseif ($type === 'image') {
            $isUrl = (bool) preg_match('#^https?://#i', $value);
            if ($value !== '') {
                echo '<div class="current-thumb"><img src="' . e(mediaUrl($value)) . '" alt="Gambar saat ini">';
                echo '<label style="margin:0;font-weight:600"><input type="checkbox" name="remove[' . e($key) . ']" value="1"> Hapus gambar</label></div>';
            }
            echo '<input type="file" id="' . e($id) . '" name="' . e(fileInputName($key)) . '" class="form-control" accept=".jpg,.jpeg,.png,.webp">';
            echo '<p class="hint" style="margin:8px 0 6px">atau tempel URL gambar:</p>';
            echo '<input type="text" name="' . $name . '" class="form-control" maxlength="255" placeholder="https://…" value="' . e($isUrl ? $value : '') . '">';
        } else { // text | emoji | link
            $placeholder = $type === 'link' ? ' placeholder="/ , #layanan , /news , https://…"' : '';
            echo '<input type="text" id="' . e($id) . '" name="' . $name . '" class="form-control"' . $max . $placeholder . ' value="' . e($value) . '"' . ($req ? ' required' : '') . '>';
        }
        if ($hint !== '') {
            echo '<p class="hint">' . e($hint) . '</p>';
        }
    }
    echo '</div>';
}
