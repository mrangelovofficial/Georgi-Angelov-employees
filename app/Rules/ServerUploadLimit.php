<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ServerUploadLimit implements ValidationRule
{
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {
        if (! $value instanceof UploadedFile) {
            return;
        }

        if ($value->getError() === UPLOAD_ERR_INI_SIZE) {
            $fail('The selected file is too large. Please choose a smaller file.');

            return;
        }

        if ($value->getError() === UPLOAD_ERR_PARTIAL) {
            $fail('The file was only partially uploaded. Please try again.');

            return;
        }

        if (! $value->isValid()) {
            $fail('The file could not be uploaded. Please try again.');

            return;
        }

        $maxBytes = $this->maxUploadSizeInBytes();

        if ($value->getSize() > $maxBytes) {
            $fail('The selected file is too large. Please choose a smaller file.');
        }
    }

    private function maxUploadSizeInBytes(): int
    {
        return min(
            $this->iniSizeToBytes(ini_get('upload_max_filesize')),
            $this->iniSizeToBytes(ini_get('post_max_size')),
        );
    }

    private function iniSizeToBytes(string|false $value): int
    {
        if (! $value) {
            return PHP_INT_MAX;
        }

        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $size = (float) $value;

        return match ($unit) {
            'g' => (int) ($size * 1024 ** 3),
            'm' => (int) ($size * 1024 ** 2),
            'k' => (int) ($size * 1024),
            default => (int) $size,
        };
    }
}
