# CakeOpenAI Plugin
CakeOpenAI plugin integrates CakePHP with OpenAI API using [OpenAI API Client in PHP](https://github.com/orhanerday/open-ai).

## Install
Install it as require dependency:
```
composer require joacir/CakeOpenAI
```

## Setup
Enable the plugin in your Application.php or call
```
bin/cake plugin load CakeOpenAI
```

Set the OpenAI credentials in your *app_local.php*:
```
'OpenAI' => [
    'apiKey' => '**************',
    'organizationID' => '***************',
];
```

Load a OpenAI Behavior in your Table *initialize()* method:
```
$this->addBehavior('CakeOpenAI.OpenAI');
```

## Usage

### Chat

To send messages to ChatGPT:
```
$messages = [
    [
        'role' => 'system',
        'content' => 'You are a helpful assistant.',
    ],
    [
        'role' => 'user',
        'content' => 'Who won the world series in 2020?',
    ],
    [
        'role' => 'assistant',
        'content' => 'The Los Angeles Dodgers won the World Series in 2020.',
    ],
    [
        'role' => 'user',
        'content' => 'Where was it played?',
    ],
];

$responses = $this->chat($messages);

echo $responses['choices'][0]['message']['content'];
```

### Image

To create image with DALL-e:
```
$prompt = 'Darth Vader riding on a bike.';

$responses = $this->image($prompt);

echo $responses['data'][0]['url'];
```

### Configurations

You can change the defaults configurations of chat and image creation, according to the [OpenAI API Reference](https://platform.openai.com/docs/api-reference):
```
$this->setChatConfig([
    'model' => 'gpt-3.5-turbo',
    'temperature' => 1.0,
    'max_tokens' => 4000,
    'frequency_penalty' => 0,
    'presence_penalty' => 0,
]);

$this->setImageConfig([
    'n' => 1,
    'size' => '1024x1024',
    'response_format' => 'url',
]);
```
