<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Graphs\Renderer;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Jawira\PlantUml\encodep;

final class PlantumlServerRenderer implements DiagramRenderer
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $plantumlServerUrl,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function render(string $diagram): string|null
    {
        $encodedDiagram = encodep($diagram);

        $response = $this->httpClient->request(
            'GET',
            $this->plantumlServerUrl . '/svg/' . $encodedDiagram,
        );

        if ($response->getStatusCode() !== 200) {
            $this->logger->error('Failed to render diagram using server:' . $this->plantumlServerUrl);

            return null;
        }

        return $response->getContent();
    }
}
