<?php

namespace Arikislam\ReplicatePhpClient;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Traits\Macroable;

/**
 * Class ReplicateClient
 *
 * A PHP client for interacting with the Replicate API.
 *
 * @package Arikislam\ReplicatePhpClient
 */
class ReplicateClient
{
    use Macroable;

    /**
     * @var string The API token for authentication
     */
    protected string $apiToken;

    /**
     * @var string The base URL for the Replicate API
     */
    protected string $apiBaseUrl;

    /**
     * @var GuzzleClient The HTTP client for making API requests
     */
    protected GuzzleClient $httpClient;

    /**
     * @var string The user agent string for API requests
     */
    protected string $userAgent;

    /**
     * @var string The strategy for encoding file data
     */
    protected string $fileEncodingStrategy = 'default';

    /**
     * @var bool Whether to use file output
     */
    protected bool $useFileOutput = false;

    /**
     * ReplicateClient constructor.
     *
     * @param string $apiToken The API token for authentication
     * @param string $apiBaseUrl The base URL for the Replicate API
     * @throws Exception If the API key is not set
     */
    public function __construct(string $apiToken, string $apiBaseUrl = 'https://api.replicate.com/v1/')
    {
        $this->apiToken   = $apiToken;
        $this->apiBaseUrl = $apiBaseUrl;
        $this->userAgent  = 'arikislam/replicate-php-client/1.0';
        $this->httpClient = new GuzzleClient([
            'base_uri' => $this->apiBaseUrl,
            'headers'  => [
                'Authorization' => 'Token ' . $this->apiToken,
                'User-Agent'    => $this->userAgent,
                'Content-Type'  => 'application/json',
            ],
        ]);

        $this->fileEncodingStrategy = 'default';
        $this->useFileOutput        = false;
    }

    /**
     * Make a GET request to the Replicate API
     *
     * @param string $endpoint The API endpoint
     * @param array $query Query parameters for the request
     * @return array The decoded JSON response
     * @throws GuzzleException
     * @throws Exception
     */
    public function get(string $endpoint, array $query = []): array
    {
        try {
            $response = $this->httpClient->get($endpoint, ['query' => $query]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {


            // Handle 4xx errors
            if ($e->getResponse()->getStatusCode() === 404) {
                throw new Exception("Endpoint not found: {$endpoint}. Please check the API documentation for available endpoints.");
            }
            throw $e;
        } catch (Exception $e) {
            // Handle other exceptions
            throw new Exception("An error occurred while making the request: " . $e->getMessage());
        }
    }

    /**
     * Make a POST request to the Replicate API
     *
     * @param string $endpoint The API endpoint
     * @param array $data The data to be sent in the request body
     * @param array $headers Additional headers for the request
     * @param string|null $body Raw body content (if provided)
     * @return array The decoded JSON response
     * @throws GuzzleException
     */
    public function post(string $endpoint, array $data = [], array $headers = [], string $body = null): array
    {
        $options = ['json' => $data];
        if (!empty($headers)) {
            $options['headers'] = $headers;
        }
        if ($body !== null) {
            $options['body'] = $body;
        }
        $response = $this->httpClient->post($endpoint, $options);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Run a model prediction and wait for the output
     *
     * @param string $identifier The model identifier
     * @param array $options Options for the prediction
     * @param callable|null $progress A callback function to track progress
     * @return array The prediction result
     * @throws GuzzleException
     * @throws Exception
     */
    public function run(string $identifier, array $options, callable $progress = null): array
    {
        $parts      = explode('/', $identifier);
        $modelOwner = $parts[0];
        $modelName  = explode(':', $parts[1])[0];
        $version    = explode(':', $parts[1])[1] ?? null;

        $endpoint = "predictions";
        $data     = [
            'version' => $version ?? $this->getLatestVersion($modelOwner, $modelName),
            'input'   => $options['input'],
        ];

        if (isset($options['webhook'])) {
            $data['webhook'] = $options['webhook'];
        }

        if (isset($options['webhook_events_filter'])) {
            $data['webhook_events_filter'] = $options['webhook_events_filter'];
        }

        $headers = [];
        if (isset($options['wait']) && $options['wait'] === true) {
            $headers['Prefer'] = 'wait';
        }

        $prediction = $this->post($endpoint, $data, $headers);

        // Wait for the prediction to complete
        $interval = $options['wait']['interval'] ?? 1;
        while ($prediction['status'] !== 'succeeded' && $prediction['status'] !== 'failed' && $prediction['status'] !== 'canceled') {
            sleep($interval);
            $prediction = $this->get("predictions/{$prediction['id']}");

            if ($progress && is_callable($progress)) {
                $progress($prediction);
            }
        }

        if ($prediction['status'] === 'failed') {
            throw new Exception("Prediction failed: " . ($prediction['error'] ?? 'Unknown error'));
        }

        if ($prediction['status'] === 'canceled') {
            throw new Exception("Prediction was canceled");
        }

        return [
            'output' => $prediction['output'],
            'id'     => $prediction['id']
        ];
    }

    /**
     * Get the latest version of a model
     *
     * @param string $modelOwner The owner of the model
     * @param string $modelName The name of the model
     * @return string The latest version ID
     * @throws GuzzleException
     */
    private function getLatestVersion(string $modelOwner, string $modelName): string
    {
        $endpoint = "models/{$modelOwner}/{$modelName}";
        $model    = $this->get($endpoint);
        return $model['latest_version']['id'];
    }

    /**
     * Wait for a prediction to complete
     *
     * @param array $prediction The initial prediction data
     * @param array $options Options for waiting
     * @return array The final prediction result
     * @throws GuzzleException
     */
    public function wait(array $prediction, array $options = []): array
    {
        $interval = $options['interval'] ?? 1;

        while ($prediction['status'] === 'starting' || $prediction['status'] === 'processing') {
            sleep($interval);
            $prediction = $this->get("predictions/{$prediction['id']}");
        }

        return $prediction;
    }

    /**
     * Get the current account information
     *
     * @return array
     * @throws GuzzleException
     */
    public function getCurrentAccount(): array
    {
        return $this->get('account');
    }

    /**
     * List collections
     *
     * @return array
     * @throws GuzzleException
     */
    public function listCollections(): array
    {
        return $this->get('collections');
    }

    /**
     * Get a specific collection
     *
     * @param string $collectionSlug
     * @return array
     * @throws GuzzleException
     */
    public function getCollection(string $collectionSlug): array
    {
        return $this->get("collections/{$collectionSlug}");
    }

    /**
     * List deployments
     *
     * @return array
     * @throws GuzzleException
     */
    public function listDeployments(): array
    {
        return $this->get('deployments');
    }

    /**
     * Get a specific deployment
     *
     * @param string $deploymentOwner
     * @param string $deploymentName
     * @return array
     * @throws GuzzleException
     */
    public function getDeployment(string $deploymentOwner, string $deploymentName): array
    {
        return $this->get("deployments/{$deploymentOwner}/{$deploymentName}");
    }

    /**
     * Create a deployment prediction
     *
     * @param string $deploymentOwner
     * @param string $deploymentName
     * @param array $options
     * @return array
     * @throws GuzzleException
     */
    public function createDeploymentPrediction(string $deploymentOwner, string $deploymentName, array $options): array
    {
        $endpoint = "deployments/{$deploymentOwner}/{$deploymentName}/predictions";
        return $this->post($endpoint, $options);
    }

    /**
     * Get collections
     *
     * @return array
     * @throws GuzzleException
     */
    public function getCollections(): array
    {
        // Remove this method if the collections endpoint is not available
        throw new Exception("The collections endpoint is not available in the current API version.");
    }

    /**
     * Search for models
     *
     * @param string $query The search query
     * @return array The search results
     * @throws GuzzleException
     */
    public function searchModels(string $query): array
    {
        return $this->post('models', [], ['Content-Type' => 'text/plain'], $query);
    }

    /**
     * Create a new deployment
     *
     * @param array $data The deployment data
     * @return array The created deployment
     * @throws GuzzleException
     */
    public function createDeployment(array $data): array
    {
        return $this->post('deployments', $data);
    }

    /**
     * Update an existing deployment
     *
     * @param string $deploymentOwner The owner of the deployment
     * @param string $deploymentName The name of the deployment
     * @param array $data The updated deployment data
     * @return array The updated deployment
     * @throws GuzzleException
     */
    public function updateDeployment(string $deploymentOwner, string $deploymentName, array $data): array
    {
        return $this->patch("deployments/{$deploymentOwner}/{$deploymentName}", $data);
    }

    /**
     * Delete a deployment
     *
     * @param string $deploymentOwner The owner of the deployment
     * @param string $deploymentName The name of the deployment
     * @throws GuzzleException
     */
    public function deleteDeployment(string $deploymentOwner, string $deploymentName): void
    {
        $this->delete("deployments/{$deploymentOwner}/{$deploymentName}");
    }

    /**
     * Send a PATCH request to the API
     *
     * @param string $endpoint The API endpoint
     * @param array $data The data to send
     * @return array The decoded JSON response
     * @throws GuzzleException
     */
    public function patch(string $endpoint, array $data = []): array
    {
        $response = $this->httpClient->patch($endpoint, ['json' => $data]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Send a DELETE request to the API
     *
     * @param string $endpoint The API endpoint
     * @throws GuzzleException
     */
    public function delete(string $endpoint): void
    {
        $this->httpClient->delete($endpoint);
    }

    /**
     * Create a new training
     *
     * @param string $modelOwner The owner of the model
     * @param string $modelName The name of the model
     * @param string $versionId The version ID
     * @param array $data The training data
     * @return array The created training
     * @throws GuzzleException
     */
    public function createTraining(string $modelOwner, string $modelName, string $versionId, array $data): array
    {
        return $this->post("models/{$modelOwner}/{$modelName}/versions/{$versionId}/trainings", $data);
    }

    /**
     * List all trainings
     *
     * @return array The list of trainings
     * @throws GuzzleException
     */
    public function listTrainings(): array
    {
        return $this->get('trainings');
    }

    /**
     * Get a specific training
     *
     * @param string $trainingId The ID of the training
     * @return array The training details
     * @throws GuzzleException
     */
    public function getTraining(string $trainingId): array
    {
        return $this->get("trainings/{$trainingId}");
    }

    /**
     * Cancel a training
     *
     * @param string $trainingId The ID of the training to cancel
     * @throws GuzzleException
     */
    public function cancelTraining(string $trainingId): void
    {
        $this->post("trainings/{$trainingId}/cancel");
    }

    /**
     * Get the webhook signing secret
     *
     * @return array The webhook signing secret
     * @throws GuzzleException
     */
    public function getWebhookSigningSecret(): array
    {
        return $this->get('webhooks/default/secret');
    }
}
