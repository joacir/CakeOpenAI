# CakeOpenAI Plugin
CakeOpenAI plugin integrates CakePHP with OpenAI API using [OpenAI API Client in PHP](https://github.com/orhanerday/open-ai).

## Install
Install it as require dependency:
```
composer require joacir/cake-open-a-i
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

### Response

To use the [Responses API](https://platform.openai.com/docs/api-reference/responses) (the recommended replacement for the Chat Completions and Assistants APIs), pass an options array with the request parameters:
```
$responses = $this->response([
    'model' => 'gpt-4o',
    'input' => 'Who won the world series in 2020?',
]);

echo $responses['output'][0]['content'][0]['text'];
```

The `input` may also be a list of messages, allowing multi-turn conversations and system instructions:
```
$responses = $this->response([
    'model' => 'gpt-4o',
    'instructions' => 'You are a helpful assistant.',
    'input' => [
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
    ],
]);
```

You can continue a previous conversation by passing the id returned in the previous response:
```
$responses = $this->response([
    'model' => 'gpt-4o',
    'previous_response_id' => $previousId,
    'input' => 'And who was the MVP?',
]);
```

The method returns the decoded API response as an array, or an empty array when no options are given or the API call fails. Any parameter supported by the [Responses API](https://platform.openai.com/docs/api-reference/responses/create) (such as `temperature`, `max_output_tokens`, `tools` or `instructions`) can be included in the options array.

> **Note:** The previous Assistants-based methods (`createThread()`, `createThreadAndRun()` and `createThreadMessage()`) are deprecated and have been superseded by `response()`.

### Transcribe

To transcribe an audio file into text with Whisper, pass the path of a local audio file:
```
$text = $this->transcribe('/path/to/audio.wav');

echo $text;
```

It returns the transcribed text, or an empty string when the file does not exist or the API call fails. Supported formats follow the [Whisper API](https://platform.openai.com/docs/api-reference/audio/createTranscription) (mp3, mp4, mpeg, mpga, m4a, wav, webm) with a 25 MB limit per file.

You can override the default settings per call without changing the behavior configuration:
```
$text = $this->transcribe('/path/to/audio.wav', ['language' => 'en']);
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

$this->setTranscribeConfig([
    'model' => 'whisper-1',
    'response_format' => 'json',
    'language' => 'pt',
]);
```
