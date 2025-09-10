<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\DevServer\Internal;

use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Response;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;
use phpDocumentor\FileSystem\FlySystemAdapter;
use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\CloseResponseTrait;
use Ratchet\Http\HttpServerInterface;
use Throwable;

use function str_replace;
use function strlen;
use function trim;

final class HttpHandler implements HttpServerInterface
{
    use CloseResponseTrait;

    private ExtensionMimeTypeDetector $detector;

    public function __construct(
        private FlySystemAdapter $files,
        private string $indexFile = 'index.html',
    ) {
        $this->detector = new ExtensionMimeTypeDetector();
    }

    public function onOpen(ConnectionInterface $conn, RequestInterface|null $request = null): void
    {
        if ($request === null) {
            $conn->close();

            return;
        }

        $path = $request->getUri()->getPath();

        // Remove leading slash and any route parameters
        $requestPath = trim($path, '/');

        // For empty path (root) serve index.html
        if ($requestPath === '') {
            $requestPath = $this->indexFile;
        }

        if ($this->files->isDirectory($requestPath)) {
            $requestPath .= '/' . $this->indexFile;
        }

        if ($this->files->has($requestPath)) {
            $content = $this->files->read($requestPath) ?: '';

            // Inject WebSocket client code for HTML files
            if ($this->detector->detectMimeTypeFromPath($requestPath) === 'text/html') {
                $content = $this->injectWebSocketClient($content);
            }

            $headers = [
                'Content-Type' => $this->detector->detectMimeTypeFromPath($requestPath) ?? 'text/plain',
                'Content-Length' => (string) strlen($content),
            ];

            $conn->send(Message::toString(new Response(200, $headers, $content)));
            $conn->close();

            return;
        }

        $content = '<!DOCTYPE html><html><body><h1>404 - Page Not Found</h1></body></html>';
        $headers = [
            'Content-Type' => 'text/html',
            'Content-Length' => strlen($content),
        ];

        $conn->send(Message::toString(new Response(404, $headers, $content)));
        $conn->close();
    }

    private function injectWebSocketClient(string $html): string
    {
        //Read html and inject script before closing body tag
        $injection = <<<'EOT'
<script>    
    const socket = new WebSocket('ws://' + window.location.host + '/ws');
    socket.addEventListener('message', function (event) {
        if (event.data === 'update') {
            console.log('Reloading page due to server change...');
            window.location.reload();
        }
    });
</script>
EOT;

        return str_replace('</body>', $injection . '</body>', $html);
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $this->close($conn);
    }

    public function onError(ConnectionInterface $conn, Throwable $e): void
    {
        $this->close($conn, 500);
    }

    /** @param string $msg */
    // phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function onMessage(ConnectionInterface $from, $msg): void
    {
        // TODO: Implement onMessage() method.
    }
}
