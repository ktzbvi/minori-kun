<?php

namespace App\Domain\ProducerRegistration;

use ErrorException;
use finfo;
use Illuminate\Http\UploadedFile;
use Throwable;

final class ProducerRegistrationPhotoProcessor
{
    private const MAX_INPUT_BYTES = 5 * 1024 * 1024;

    private const MAX_INPUT_DIMENSION = 4096;

    private const MAX_OUTPUT_DIMENSION = 1024;

    /**
     * Decode and normalize a single supported shop photo without writing it to storage.
     *
     * @throws ProducerRegistrationException
     */
    public function process(UploadedFile $file): ProcessedRegistrationPhoto
    {
        $this->assertRuntimeAvailable();

        try {
            if (! $file->isValid()) {
                $this->invalidPhoto();
            }

            $path = $file->getRealPath();

            if (! is_string($path) || ! is_file($path)) {
                $this->invalidPhoto();
            }

            $reportedSize = $file->getSize();

            if (! is_int($reportedSize) || $reportedSize < 1 || $reportedSize > self::MAX_INPUT_BYTES) {
                $this->invalidPhoto();
            }

            $bytes = $this->withWarningsAsExceptions(
                fn (): string|false => file_get_contents($path),
            );

            if (! is_string($bytes) || $bytes === '' || strlen($bytes) > self::MAX_INPUT_BYTES) {
                $this->invalidPhoto();
            }

            $mimeType = $this->detectMimeType($bytes);
            $imageInfo = $this->withWarningsAsExceptions(
                fn (): array|false => getimagesizefromstring($bytes),
            );

            if (! is_array($imageInfo)
                || ! isset($imageInfo[0], $imageInfo[1], $imageInfo['mime'])
                || ! is_int($imageInfo[0])
                || ! is_int($imageInfo[1])
                || $imageInfo[0] < 1
                || $imageInfo[1] < 1
                || $imageInfo[0] > self::MAX_INPUT_DIMENSION
                || $imageInfo[1] > self::MAX_INPUT_DIMENSION
                || $imageInfo['mime'] !== $mimeType) {
                $this->invalidPhoto();
            }

            $orientation = match ($mimeType) {
                'image/jpeg' => $this->jpegOrientation($bytes),
                'image/png' => $this->pngOrientation($bytes),
                'image/webp' => $this->webpOrientation($bytes),
                default => $this->invalidPhoto(),
            };

            $decoded = null;
            $resized = null;
            $oriented = null;

            try {
                $decoded = $this->withWarningsAsExceptions(
                    fn () => imagecreatefromstring($bytes),
                );

                if (! $decoded instanceof \GdImage
                    || imagesx($decoded) !== $imageInfo[0]
                    || imagesy($decoded) !== $imageInfo[1]) {
                    $this->invalidPhoto();
                }

                if (! imageistruecolor($decoded) && ! imagepalettetotruecolor($decoded)) {
                    $this->invalidPhoto();
                }

                $preserveAlpha = $mimeType !== 'image/jpeg';

                if ($preserveAlpha) {
                    imagealphablending($decoded, false);
                    imagesavealpha($decoded, true);
                }

                [$resizedWidth, $resizedHeight] = $this->resizedDimensions(
                    $imageInfo[0],
                    $imageInfo[1],
                    $orientation,
                );

                $resizeWidth = in_array($orientation, [5, 6, 7, 8], true)
                    ? $resizedHeight
                    : $resizedWidth;
                $resizeHeight = in_array($orientation, [5, 6, 7, 8], true)
                    ? $resizedWidth
                    : $resizedHeight;

                $resized = $this->resample(
                    $decoded,
                    $resizeWidth,
                    $resizeHeight,
                    $preserveAlpha,
                );

                $oriented = $this->applyOrientation($resized, $orientation, $preserveAlpha);

                if (imagesx($oriented) !== $resizedWidth
                    || imagesy($oriented) !== $resizedHeight) {
                    $this->invalidPhoto();
                }

                $processedBytes = $this->encode($oriented, $mimeType);
                $processedWidth = imagesx($oriented);
                $processedHeight = imagesy($oriented);

                return new ProcessedRegistrationPhoto(
                    bytes: $processedBytes,
                    mimeType: $mimeType,
                    width: $processedWidth,
                    height: $processedHeight,
                    sizeBytes: strlen($processedBytes),
                    extension: match ($mimeType) {
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                    },
                );
            } finally {
                if ($oriented instanceof \GdImage && $oriented !== $resized) {
                    imagedestroy($oriented);
                }

                if ($resized instanceof \GdImage) {
                    imagedestroy($resized);
                }

                if ($decoded instanceof \GdImage) {
                    imagedestroy($decoded);
                }
            }
        } catch (ProducerRegistrationException $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->invalidPhoto();
        }
    }

    private function assertRuntimeAvailable(): void
    {
        if (! extension_loaded('gd')
            || ! extension_loaded('exif')
            || ! class_exists(finfo::class)
            || ! function_exists('getimagesizefromstring')
            || ! function_exists('imagecreatefromstring')
            || ! function_exists('imagecreatetruecolor')
            || ! function_exists('imagecopyresampled')
            || ! function_exists('imageistruecolor')
            || ! function_exists('imagepalettetotruecolor')) {
            $this->processingUnavailable();
        }
    }

    private function detectMimeType(string $bytes): string
    {
        $mimeType = $this->withWarningsAsExceptions(
            fn (): string|false => (new finfo(FILEINFO_MIME_TYPE))->buffer($bytes),
        );

        if (! is_string($mimeType) || ! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            $this->invalidPhoto();
        }

        return $mimeType;
    }

    /** @return array{int, int} */
    private function resizedDimensions(int $width, int $height, int $orientation): array
    {
        $orientationSwapsAxes = in_array($orientation, [5, 6, 7, 8], true);
        $displayWidth = $orientationSwapsAxes ? $height : $width;
        $displayHeight = $orientationSwapsAxes ? $width : $height;
        $scale = min(1, self::MAX_OUTPUT_DIMENSION / max($displayWidth, $displayHeight));

        return [
            max(1, (int) round($displayWidth * $scale)),
            max(1, (int) round($displayHeight * $scale)),
        ];
    }

    private function resample(\GdImage $source, int $width, int $height, bool $preserveAlpha): \GdImage
    {
        $target = imagecreatetruecolor($width, $height);

        if (! $target instanceof \GdImage) {
            $this->invalidPhoto();
        }

        try {
            if ($preserveAlpha) {
                imagealphablending($target, false);
                imagesavealpha($target, true);
                $background = imagecolorallocatealpha($target, 0, 0, 0, 127);
            } else {
                imagealphablending($target, true);
                imagesavealpha($target, false);
                $background = imagecolorallocate($target, 255, 255, 255);
            }

            if (! is_int($background)
                || ! imagefilledrectangle($target, 0, 0, $width - 1, $height - 1, $background)
                || ! imagecopyresampled(
                    $target,
                    $source,
                    0,
                    0,
                    0,
                    0,
                    $width,
                    $height,
                    imagesx($source),
                    imagesy($source),
                )) {
                $this->invalidPhoto();
            }

            return $target;
        } catch (Throwable $exception) {
            imagedestroy($target);
            throw $exception;
        }
    }

    private function applyOrientation(\GdImage $image, int $orientation, bool $preserveAlpha): \GdImage
    {
        $flip = match ($orientation) {
            2, 5, 7 => IMG_FLIP_HORIZONTAL,
            4 => IMG_FLIP_VERTICAL,
            default => null,
        };

        if ($flip !== null && ! imageflip($image, $flip)) {
            $this->invalidPhoto();
        }

        $angle = match ($orientation) {
            3 => 180,
            5, 8 => 90,
            6, 7 => 270,
            default => null,
        };

        if ($angle === null) {
            return $image;
        }

        $background = $preserveAlpha
            ? imagecolorallocatealpha($image, 0, 0, 0, 127)
            : imagecolorallocate($image, 255, 255, 255);

        if (! is_int($background)) {
            $this->invalidPhoto();
        }

        $rotated = imagerotate($image, $angle, $background);

        if (! $rotated instanceof \GdImage) {
            $this->invalidPhoto();
        }

        if ($preserveAlpha) {
            imagealphablending($rotated, false);
            imagesavealpha($rotated, true);
        }

        return $rotated;
    }

    private function encode(\GdImage $image, string $mimeType): string
    {
        $encoder = match ($mimeType) {
            'image/jpeg' => 'imagejpeg',
            'image/png' => 'imagepng',
            'image/webp' => 'imagewebp',
        };

        if (! function_exists($encoder)) {
            $this->processingUnavailable();
        }

        ob_start();

        try {
            $encoded = $this->withWarningsAsExceptions(fn (): bool => match ($mimeType) {
                'image/jpeg' => imagejpeg($image, null, 85),
                'image/png' => imagepng($image, null, 6),
                'image/webp' => imagewebp($image, null, 85),
            });
            $bytes = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        if (! $encoded || ! is_string($bytes) || $bytes === '') {
            $this->invalidPhoto();
        }

        return $bytes;
    }

    private function jpegOrientation(string $bytes): int
    {
        if (substr($bytes, 0, 2) !== "\xFF\xD8") {
            $this->invalidPhoto();
        }

        $offset = 2;
        $length = strlen($bytes);
        $orientation = 1;
        $foundExif = false;

        while ($offset < $length) {
            if (ord($bytes[$offset]) !== 0xFF) {
                $this->invalidPhoto();
            }

            while ($offset < $length && ord($bytes[$offset]) === 0xFF) {
                $offset++;
            }

            if ($offset >= $length) {
                $this->invalidPhoto();
            }

            $marker = ord($bytes[$offset++]);

            if ($marker === 0xDA || $marker === 0xD9) {
                break;
            }

            if ($marker === 0x00 || $marker === 0xFF || ($marker >= 0xD0 && $marker <= 0xD8)) {
                $this->invalidPhoto();
            }

            if ($marker === 0x01) {
                continue;
            }

            if ($offset + 2 > $length) {
                $this->invalidPhoto();
            }

            $segmentLength = unpack('nlength', substr($bytes, $offset, 2))['length'];

            if ($segmentLength < 2 || $offset + $segmentLength > $length) {
                $this->invalidPhoto();
            }

            $payloadStart = $offset + 2;
            $payloadLength = $segmentLength - 2;

            if ($marker === 0xE1 && substr($bytes, $payloadStart, 6) === "Exif\0\0") {
                if ($foundExif) {
                    $this->invalidPhoto();
                }

                $foundExif = true;
                $orientation = $this->parseExifOrientation(substr($bytes, $payloadStart, $payloadLength));
            }

            $offset += $segmentLength;
        }

        return $orientation;
    }

    private function pngOrientation(string $bytes): int
    {
        if (substr($bytes, 0, 8) !== "\x89PNG\r\n\x1A\n") {
            $this->invalidPhoto();
        }

        $length = strlen($bytes);
        $offset = 8;
        $seenHeader = false;
        $seenEnd = false;
        $foundExif = false;
        $orientation = 1;

        while ($offset < $length) {
            if ($offset + 12 > $length) {
                $this->invalidPhoto();
            }

            $chunkLength = unpack('Nlength', substr($bytes, $offset, 4))['length'];
            $chunkType = substr($bytes, $offset + 4, 4);
            $chunkStart = $offset + 8;
            $chunkEnd = $chunkStart + $chunkLength;

            if (! preg_match('/^[A-Za-z]{4}$/D', $chunkType)
                || $chunkEnd + 4 > $length) {
                $this->invalidPhoto();
            }

            $chunkData = substr($bytes, $chunkStart, $chunkLength);
            $chunkCrc = substr($bytes, $chunkEnd, 4);

            if (! hash_equals(hash('crc32b', $chunkType.$chunkData, true), $chunkCrc)) {
                $this->invalidPhoto();
            }

            if (! $seenHeader && $chunkType !== 'IHDR') {
                $this->invalidPhoto();
            }

            if ($chunkType === 'IHDR') {
                if ($seenHeader || $chunkLength !== 13) {
                    $this->invalidPhoto();
                }

                $seenHeader = true;
            }

            if (in_array($chunkType, ['acTL', 'fcTL', 'fdAT'], true)) {
                $this->invalidPhoto();
            }

            if ($chunkType === 'eXIf') {
                if ($foundExif) {
                    $this->invalidPhoto();
                }

                $foundExif = true;
                $orientation = $this->parseExifOrientation($chunkData);
            }

            $offset = $chunkEnd + 4;

            if ($chunkType === 'IEND') {
                if ($chunkLength !== 0 || $offset !== $length) {
                    $this->invalidPhoto();
                }

                $seenEnd = true;
                break;
            }
        }

        if (! $seenHeader || ! $seenEnd) {
            $this->invalidPhoto();
        }

        return $orientation;
    }

    private function webpOrientation(string $bytes): int
    {
        $length = strlen($bytes);

        if ($length < 20
            || substr($bytes, 0, 4) !== 'RIFF'
            || substr($bytes, 8, 4) !== 'WEBP'
            || unpack('Vsize', substr($bytes, 4, 4))['size'] + 8 !== $length) {
            $this->invalidPhoto();
        }

        $offset = 12;
        $orientation = 1;
        $foundExif = false;
        $seenExtendedHeader = false;
        $hasImageChunk = false;

        while ($offset < $length) {
            if ($offset + 8 > $length) {
                $this->invalidPhoto();
            }

            $chunkType = substr($bytes, $offset, 4);
            $chunkLength = unpack('Vlength', substr($bytes, $offset + 4, 4))['length'];
            $chunkStart = $offset + 8;
            $chunkEnd = $chunkStart + $chunkLength;
            $nextOffset = $chunkEnd + ($chunkLength % 2);

            if ($chunkEnd > $length
                || $nextOffset > $length
                || (($chunkLength % 2) === 1 && substr($bytes, $chunkEnd, 1) !== "\0")) {
                $this->invalidPhoto();
            }

            $chunkData = substr($bytes, $chunkStart, $chunkLength);

            if (in_array($chunkType, ['ANIM', 'ANMF'], true)) {
                $this->invalidPhoto();
            }

            if ($chunkType === 'VP8X') {
                if ($seenExtendedHeader
                    || $offset !== 12
                    || $chunkLength !== 10
                    || (ord($chunkData[0]) & 0x02) !== 0) {
                    $this->invalidPhoto();
                }

                $seenExtendedHeader = true;
            }

            if (in_array($chunkType, ['VP8 ', 'VP8L'], true)) {
                $hasImageChunk = true;
            }

            if ($chunkType === 'EXIF') {
                if ($foundExif) {
                    $this->invalidPhoto();
                }

                $foundExif = true;
                $orientation = $this->parseExifOrientation($chunkData);
            }

            $offset = $nextOffset;
        }

        if ($offset !== $length || ! $hasImageChunk) {
            $this->invalidPhoto();
        }

        return $orientation;
    }

    private function parseExifOrientation(string $payload): int
    {
        if (str_starts_with($payload, "Exif\0\0")) {
            $payload = substr($payload, 6);
        }

        if (strlen($payload) < 8) {
            $this->invalidPhoto();
        }

        $byteOrder = substr($payload, 0, 2);
        $littleEndian = match ($byteOrder) {
            'II' => true,
            'MM' => false,
            default => $this->invalidPhoto(),
        };

        if ($this->readExifUInt16($payload, 2, $littleEndian) !== 42) {
            $this->invalidPhoto();
        }

        $ifdOffset = $this->readExifUInt32($payload, 4, $littleEndian);

        if ($ifdOffset < 8 || $ifdOffset + 2 > strlen($payload)) {
            $this->invalidPhoto();
        }

        $entryCount = $this->readExifUInt16($payload, $ifdOffset, $littleEndian);
        $entriesStart = $ifdOffset + 2;

        if ($entryCount > intdiv(strlen($payload) - $entriesStart, 12)) {
            $this->invalidPhoto();
        }

        $orientation = 1;
        $foundOrientation = false;

        for ($index = 0; $index < $entryCount; $index++) {
            $entryOffset = $entriesStart + ($index * 12);
            $tag = $this->readExifUInt16($payload, $entryOffset, $littleEndian);

            if ($tag !== 0x0112) {
                continue;
            }

            if ($foundOrientation
                || $this->readExifUInt16($payload, $entryOffset + 2, $littleEndian) !== 3
                || $this->readExifUInt32($payload, $entryOffset + 4, $littleEndian) !== 1) {
                $this->invalidPhoto();
            }

            $orientation = $this->readExifUInt16($payload, $entryOffset + 8, $littleEndian);

            if ($orientation < 1 || $orientation > 8) {
                $this->invalidPhoto();
            }

            $foundOrientation = true;
        }

        return $orientation;
    }

    private function readExifUInt16(string $bytes, int $offset, bool $littleEndian): int
    {
        if ($offset < 0 || $offset + 2 > strlen($bytes)) {
            $this->invalidPhoto();
        }

        $value = unpack($littleEndian ? 'vvalue' : 'nvalue', substr($bytes, $offset, 2));

        return $value['value'];
    }

    private function readExifUInt32(string $bytes, int $offset, bool $littleEndian): int
    {
        if ($offset < 0 || $offset + 4 > strlen($bytes)) {
            $this->invalidPhoto();
        }

        $value = unpack($littleEndian ? 'Vvalue' : 'Nvalue', substr($bytes, $offset, 4));

        return $value['value'];
    }

    private function withWarningsAsExceptions(callable $callback): mixed
    {
        set_error_handler(static function (int $severity, string $message): never {
            throw new ErrorException($message, 0, $severity);
        });

        try {
            return $callback();
        } finally {
            restore_error_handler();
        }
    }

    private function invalidPhoto(): never
    {
        throw new ProducerRegistrationException(
            'PHOTO_INVALID',
            422,
            ['photo' => ['写真ファイルを確認してください。']],
        );
    }

    private function processingUnavailable(): never
    {
        throw new ProducerRegistrationException(
            'PHOTO_PROCESSING_UNAVAILABLE',
            503,
            [],
            [],
            null,
            '写真処理を現在利用できません。',
        );
    }
}
