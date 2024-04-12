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

namespace phpDocumentor\Guides\Graphs\Renderer;

use phpDocumentor\Guides\RenderContext;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Jawira\PlantUml\encodep;
use function sprintf;

final class PlantumlServerRenderer implements DiagramRenderer
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $plantumlServerUrl,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function render(RenderContext $renderContext, string $diagram): string|null
    {
        $encodedDiagram = encodep($diagram);

        $url = $this->plantumlServerUrl . '/svg/' . $encodedDiagram;

        try {
            $response = $this->httpClient->request(
                'GET',
                $url,
            );

            if ($response->getStatusCode() !== 200) {
                $this->logger->warning(
                    sprintf(
                        'Failed to render diagram using url: %s. The server returned status code %s. ',
                        $url,
                        $response->getStatusCode(),
                    ),
                    $renderContext->getLoggerInformation(),
                );

                return null;
            }

            return $response->getContent();
        } catch (TransportExceptionInterface) {
            $this->logger->warning(
                sprintf(
                    'Failed to render diagram using url: %s. ',
                    $url,
                ),
                $renderContext->getLoggerInformation(),
            );

            return null;
        }
    }
}
