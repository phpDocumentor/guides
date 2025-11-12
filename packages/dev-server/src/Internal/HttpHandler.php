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

use function is_array;
use function str_replace;
use function strlen;
use function trim;

final class HttpHandler implements HttpServerInterface
{
    use CloseResponseTrait;

    private ExtensionMimeTypeDetector $detector;

    /** @param string|string[] $indexFile */
    public function __construct(
        private FlySystemAdapter $files,
        private string|array $indexFile = 'index.html',
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

        if ($requestPath === '' || $this->files->isDirectory($requestPath)) {
            if (is_array($this->indexFile)) {
                foreach ($this->indexFile as $indexFile) {
                    if ($this->files->has(trim($requestPath . '/' . $indexFile, '/'))) {
                        $requestPath = trim($requestPath . '/' . $indexFile, '/');
                        break;
                    }
                }
            } else {
                $requestPath .= '/' . $this->indexFile;
            }
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

        $content = '<!DOCTYPE html><html><body><h1>404 - Page Not Found</h1>
<p>Path ' . $requestPath . ' does not exist</p>
</body></html>';
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
    const socket = new WebSocket((window.location.protocol === 'https:' ? 'wss://' : 'ws://') + window.location.host + '/ws');
    socket.addEventListener('message', function (event) {
        if (event.data === 'update') {
            console.log('Reloading page due to server change... Stored scrollPosition: ' + window.scrollY);
            sessionStorage.setItem('scrollPosition', window.scrollY);
            sessionStorage.setItem('scrollURL', window.location.href);
            window.location.reload();
        }
    });

    // Restore scroll position after page loads. Note that sessionStorage is
    // browser-tab specific, so multiple instances should not affect each other.
    window.addEventListener('load', function() {
        const scrollPosition = sessionStorage.getItem('scrollPosition');
        const scrollURL = sessionStorage.getItem('scrollURL');

        // Only restore if we're on the same URL (hot reload, not navigation)
        if (scrollPosition !== null && scrollURL === window.location.href) {
            console.log('Prepare to restore scrollPosition to: ' + scrollPosition);

            // Use setTimeout to override hash scrolling that happens after load
            setTimeout(function() {
                console.log('Restoring scrollPosition to: ' + scrollPosition);
                window.scrollTo(0, parseInt(scrollPosition));
            }, 10);
        }

        // Ensure local scroll position is reset, so other reloads to not carry state.
        sessionStorage.removeItem('scrollPosition');
        sessionStorage.removeItem('scrollURL');
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
