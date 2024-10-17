
# Replicate PHP Client

[![Latest Stable Version](https://poser.pugx.org/arikislam/replicate-php-client/v/stable)](https://packagist.org/packages/arikislam/replicate-php-client)
[![License](https://poser.pugx.org/arikislam/replicate-php-client/license)](https://packagist.org/packages/arikislam/replicate-php-client)

A simple PHP client for interacting with the [Replicate API](https://replicate.com), enabling the ability to run machine learning models from your PHP applications. This library provides a clean and reusable way to interact with the API in a Laravel environment but can also be used in other PHP applications.

## Features

- Simple API client for [Replicate](https://replicate.com)
- Supports Laravel out of the box
- Uses [Guzzle HTTP client](https://github.com/guzzle/guzzle) for HTTP requests
- Allows easy integration with machine learning models using Replicate

## Installation

To install the package, simply use Composer:

```bash
composer require arikislam/replicate-php-client
```

### Laravel Configuration

For Laravel applications, the package will automatically register the `ReplicatePhpClientServiceProvider` and the `Replicate` alias.

You can publish the config file with:

```bash
php artisan vendor:publish --provider="Arikislam\ReplicatePhpClient\Providers\ReplicatePhpClientServiceProvider"
```

## Usage

### Configuration

To use this package, you must have an API key from [Replicate](https://replicate.com).

Set up the API key in your environment file:

```bash
REPLICATE_API_KEY=your_api_key_here
```

### Example Request

To create an inference using the Replicate API, use the `Replicate` facade in your application as shown below:

```php
use Arikislam\ReplicatePhpClient\Replicate;

$response = Replicate::createPrediction([
    'input' => [
        'prompt' => 'Test',
        'go_fast' => true,
        'num_outputs' => 1,
        'aspect_ratio' => '16:9',
        'output_format' => 'png',
        'output_quality' => 80,
        'megapixels' => '.25',
    ],
]);

// Get the prediction results
$predictionResult = $response->getResult();
```

This example demonstrates how to send a request to the Replicate API with the required parameters.

### Detailed Breakdown

#### 1. Input

The `input` parameter specifies the model input options. In this case:

- `prompt`: The text prompt for generating the image.
- `go_fast`: A boolean indicating whether the model should prioritize speed over quality.
- `num_outputs`: The number of outputs required from the model.
- `aspect_ratio`: The aspect ratio of the output (e.g., 16:9).
- `output_format`: The format of the generated output (e.g., `png`).
- `output_quality`: The quality of the output (percentage-based, 80 means 80% quality).
- `megapixels`: Controls the megapixel size for the image (e.g., .25).

#### 2. Webhooks

Although webhooks can be used to track the progress of asynchronous tasks, this package allows you to specify a `webhook` URL and `webhook_events_filter` to listen for certain events. Currently, the `webhook` parameter is set to `null` in the example for simplicity.

#### 3. API Version

The `version` is an identifier for the version of the Replicate API being used. You can specify which model version you are targeting by using the correct version hash.

### Response Handling

The response from the API will contain details of the prediction, including the generated content or the status of the request. You can handle the response as follows:

```php
if ($response->isSuccessful()) {
    $output = $response->getResult();
    // Do something with the output, such as saving the image or displaying it
} else {
    // Handle errors
    $errorMessage = $response->getErrorMessage();
    // Log or display the error message
}
```

## Testing

This package includes support for unit testing. You can run tests using PHPUnit:

```bash
vendor/bin/phpunit
```

## Advanced Usage

### Custom HTTP Client

You can provide your own custom HTTP client if needed by extending the Guzzle client. For example:

```php
use GuzzleHttp\Client;
use Arikislam\ReplicatePhpClient\Replicate;

$client = new Client([
    'base_uri' => 'https://api.replicate.com',
    'timeout'  => 10.0,
]);

$replicate = new Replicate($client);
$response = $replicate->createPrediction([
    // input options
]);
```

### Facades in Laravel

For Laravel users, the `Replicate` facade provides a convenient way to interact with the API. However, if you are not using Laravel, you can directly instantiate the `Replicate` class and make API requests.

## Contributing

Contributions are welcome! Please submit a pull request or open an issue to contribute to the development of this package.

## License

This package is licensed under the MIT License. See the [LICENSE](LICENSE) file for more information.

## Author

This package is maintained by [S M Ariq Islam](mailto:arikislam321@gmail.com).
