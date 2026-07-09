<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SecureUploadsMiddleware
{
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;
    private const MAX_REQUEST_SIZE = 10 * 1024 * 1024;

    private const FORBIDDEN_EXTENSIONS = [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'pht',
        'exe', 'bat', 'cmd', 'sh', 'bash', 'js', 'jar',
        'html', 'htm', 'svg', 'xml', 'xsl', 'jsp', 'asp', 'aspx', 'cgi', 'pl'
    ];

    private const FORBIDDEN_MIME_TYPES = [
        'application/x-php',
        'application/x-httpd-php',
        'application/x-sh',
        'text/html',
        'text/javascript',
        'application/javascript',
        'application/x-javascript',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);
        if ($contentLength > self::MAX_REQUEST_SIZE) {
            return $this->fail($request, 'Request payload is too large.', 413);
        }

        foreach ($request->allFiles() as $field => $files) {
            $items = $files instanceof UploadedFile ? [$files] : $files;

            foreach ($items as $file) {
                if (!$file instanceof UploadedFile) {
                    continue;
                }

                if (!$file->isValid()) {
                    return $this->fail($request, 'Uploaded file is invalid.', 422);
                }

                if ($file->getSize() > self::MAX_FILE_SIZE) {
                    return $this->fail($request, 'Uploaded file is too large.', 413);
                }

                if ($this->isForbiddenFile($file)) {
                    return $this->fail($request, 'Upload contains forbidden file type.', 422);
                }

                if ($this->looksLikeExecutableContent($file)) {
                    return $this->fail($request, 'Upload contains suspicious content.', 422);
                }
            }
        }

        return $next($request);
    }

    private function isForbiddenFile(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = strtolower($file->getClientMimeType() ?? '');

        return in_array($extension, self::FORBIDDEN_EXTENSIONS, true)
            || in_array($mimeType, self::FORBIDDEN_MIME_TYPES, true);
    }

    private function looksLikeExecutableContent(UploadedFile $file): bool
    {
        $realPath = $file->getRealPath();

        if (!$realPath || !is_file($realPath)) {
            return false;
        }

        $contents = @file_get_contents($realPath, false, null, 0, 2048);
        if ($contents === false || $contents === '') {
            return false;
        }

        $content = strtolower($contents);

        return Str::contains($content, ['<?php', '<script', '<html', '<body', 'eval(', 'base64_decode', 'phpinfo(']);
    }

    private function fail(Request $request, string $message, int $status)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        throw ValidationException::withMessages(['file' => $message]);
    }
}
