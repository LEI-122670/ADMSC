<?php
declare(strict_types=1);

/*
 * Upload de imagens: valida o conteúdo real do ficheiro (não a extensão),
 * dá-lhe um nome aleatório e, com a extensão GD, volta a gravá-lo (reduz para
 * no máximo 2000 px e retira metadados como a localização GPS das fotos).
 * A pasta uploads/ tem um .htaccess que impede a execução de PHP.
 */

const IMAGE_MAX_BYTES = 8 * 1024 * 1024;
const IMAGE_MAX_SIDE = 2000;
const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

final class UploadError extends RuntimeException
{
}

function uploads_path(string $relative): string
{
    return dirname(__DIR__) . '/public_html/uploads/' . $relative;
}

/** Lista os ficheiros enviados num campo <input type="file" name="x[]" multiple>. */
function uploaded_files(string $field): array
{
    $f = $_FILES[$field] ?? null;
    if (!is_array($f) || !is_array($f['name'] ?? null)) {
        return [];
    }
    $files = [];
    foreach ($f['name'] as $i => $name) {
        if ((int) $f['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $files[] = ['name' => (string) $name, 'tmp' => (string) $f['tmp_name'][$i], 'error' => (int) $f['error'][$i], 'size' => (int) $f['size'][$i]];
    }
    return $files;
}

/** Verifica um ficheiro sem o guardar; devolve a mensagem de erro ou null. */
function image_problem(array $file): ?string
{
    $label = '«' . mb_substr($file['name'], 0, 60) . '»';
    if (in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) || $file['size'] > IMAGE_MAX_BYTES) {
        return "A foto $label é demasiado grande (máximo 8 MB).";
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp'])) {
        return "Não foi possível carregar a foto $label.";
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp']);
    $size = @getimagesize($file['tmp']);
    if (!isset(IMAGE_TYPES[$mime]) || $size === false || $size[0] < 1 || $size[1] < 1) {
        return "A foto $label não está num formato aceite (JPG, PNG ou WebP).";
    }
    if ($size[0] * $size[1] > 50_000_000) {
        return "A foto $label tem demasiados píxeis.";
    }
    return null;
}

/** Guarda um ficheiro já validado em uploads/$dir/ e devolve o caminho relativo. */
function store_image(array $file, string $dir): string
{
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp']);
    $ext = IMAGE_TYPES[$mime] ?? throw new UploadError('Formato não aceite.');
    $folder = uploads_path($dir);
    if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
        throw new UploadError('Não foi possível criar a pasta de uploads.');
    }
    $relative = $dir . '/' . bin2hex(random_bytes(16)) . '.' . $ext;
    $target = uploads_path($relative);

    if (!reencode_image($file['tmp'], $mime, $target) && !move_uploaded_file($file['tmp'], $target)) {
        throw new UploadError('Não foi possível guardar a foto.');
    }
    return $relative;
}

/** Volta a gravar a imagem com GD (sem metadados, orientação corrigida, lado máximo limitado). */
function reencode_image(string $source, string $mime, string $target): bool
{
    $loaders = ['image/jpeg' => 'imagecreatefromjpeg', 'image/png' => 'imagecreatefrompng', 'image/webp' => 'imagecreatefromwebp'];
    if (!function_exists($loaders[$mime])) {
        return false;
    }
    $img = @$loaders[$mime]($source);
    if ($img === false) {
        return false;
    }

    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $orientation = (int) (@exif_read_data($source)['Orientation'] ?? 1);
        $img = match ($orientation) {
            3 => imagerotate($img, 180, 0),
            6 => imagerotate($img, -90, 0),
            8 => imagerotate($img, 90, 0),
            default => $img,
        };
    }

    $w = imagesx($img);
    $h = imagesy($img);
    if (max($w, $h) > IMAGE_MAX_SIDE) {
        $scale = IMAGE_MAX_SIDE / max($w, $h);
        $img = imagescale($img, (int) round($w * $scale), (int) round($h * $scale), IMG_BICUBIC);
    }

    if ($mime !== 'image/jpeg') {
        imagealphablending($img, false);
        imagesavealpha($img, true);
    }
    return match ($mime) {
        'image/jpeg' => imagejpeg($img, $target, 85),
        'image/png' => imagepng($img, $target, 6),
        'image/webp' => function_exists('imagewebp') && imagewebp($img, $target, 85),
    };
}

/** Apaga um ficheiro de uploads/ (só aceita caminhos gerados por store_image). */
function delete_upload(?string $relative): void
{
    if ($relative !== null && preg_match('~^[a-z]+/[a-f0-9]{32}\.(jpg|png|webp)$~', $relative) === 1) {
        @unlink(uploads_path($relative));
    }
}
