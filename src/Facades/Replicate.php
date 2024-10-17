<?php

namespace Arikislam\ReplicatePhpClient\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Replicate
 *
 * @package Arikislam\ReplicatePhpClient\Facades
 *
 * @method static array get(string $endpoint, array $query = [])
 * @method static array post(string $endpoint, array $data = [], array $headers = [], string $body = null)
 * @method static array run(string $identifier, array $options, callable $progress = null)
 * @method static array wait(array $prediction, array $options = [])
 * @method static array getCurrentAccount()
 * @method static array listCollections()
 * @method static array getCollection(string $collectionSlug)
 * @method static array listDeployments()
 * @method static array getDeployment(string $deploymentOwner, string $deploymentName)
 * @method static array createDeploymentPrediction(string $deploymentOwner, string $deploymentName, array $options)
 * @method static array searchModels(string $query)
 * @method static array createDeployment(array $data)
 * @method static array updateDeployment(string $deploymentOwner, string $deploymentName, array $data)
 * @method static void deleteDeployment(string $deploymentOwner, string $deploymentName)
 * @method static array patch(string $endpoint, array $data = [])
 * @method static void delete(string $endpoint)
 * @method static array createTraining(string $modelOwner, string $modelName, string $versionId, array $data)
 * @method static array listTrainings()
 * @method static array getTraining(string $trainingId)
 * @method static void cancelTraining(string $trainingId)
 * @method static array getWebhookSigningSecret()
 *
 * @see \Arikislam\ReplicatePhpClient\ReplicateClient
 */
class Replicate extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'replicate';
    }
}
