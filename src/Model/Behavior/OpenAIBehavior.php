<?php
declare(strict_types=1);

namespace CakeOpenAI\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\Behavior;
use Exception;
use Orhanerday\OpenAi\OpenAi;

/**
 * OpenAI behavior
 */
class OpenAIBehavior extends Behavior
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [
        'chat' => [
            'model' => 'gpt-3.5-turbo',
            'temperature' => 1.0,
            'max_tokens' => 4000,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ],
        'image' => [
            'n' => 1,
            'size' => '1024x1024',
            'response_format' => 'url',
        ],
    ];

    public OpenAI $openAI;

    public function initialize(array $config): void
    {
        $this->_defaultConfig = array_merge($this->_defaultConfig, $config);
        $apiKey = Configure::read('OpenAI.apiKey');
        if (isset($apiKey)) {
            $this->openAI = new OpenAi($apiKey);
            /** @var string $organizationID */
            $organizationID = Configure::read('OpenAI.organizationID');
            if (isset($organizationID)) {
                $this->openAI->setORG($organizationID);
            }
        }
    }

    /**
     * Send messages to ChatGPT and return responses.
     *
     * @param array<string, string> $messages messages to send
     * @return array $responses responses from ChatGPT
     */
    public function chat(array $messages): array
    {
        if (count($messages) === 0 || !isset($this->openAI)) {
            return [];
        }

        $config = $this->getChatConfig();
        $config['messages'] = $messages;
        try {
            $chat = $this->openAI->chat($config);
            $responses = is_string($chat) ? (array)json_decode($chat, true) : [];
        } catch (Exception $e) {
            return [];
        }

        return $responses;
    }

    /**
     * Send a image prompt to DALL-e and return responses.
     *
     * @param string $prompt prompt to describe a image
     * @return array $responses responses from DALL-e
     */
    public function image(string $prompt): array
    {
        if (empty($prompt) || !isset($this->openAI)) {
            return [];
        }

        $config = $this->getImageConfig();
        $config['prompt'] = $prompt;
        try {
            $image = $this->openAI->image($config);
            $responses = is_string($image) ? (array)json_decode($image, true) : [];
        } catch (Exception $e) {
            return [];
        }

        return $responses;
    }

    public function getChatConfig(): array
    {
        return is_array($this->_defaultConfig['chat']) ? $this->_defaultConfig['chat'] : [];
    }

    public function getImageConfig(): array
    {
        return is_array($this->_defaultConfig['image']) ? $this->_defaultConfig['image'] : [];
    }

    public function setChatConfig(array $config): void
    {
        $this->_defaultConfig['chat'] = $config;
    }

    public function setImageConfig(array $config): void
    {
        $this->_defaultConfig['image'] = $config;
    }
}
