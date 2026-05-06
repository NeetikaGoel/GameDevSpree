<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Logging/Logger.php';

class BootstrapLoader
{
    private const TTL_SECONDS_DEFAULT = 7200;
    private const TTL_SECONDS_MAX = 604800;
    private const HOST_DEFAULT = '127.0.0.1';
    private const PORT_DEFAULT = 8080;
    private const LOG_FILE_DEFAULT = 'logs/cache-server.log';

    private string $bootstrapFilePath;

    public function __construct(string $bootstrapFilePath)
    {
        $this->bootstrapFilePath = $bootstrapFilePath;
    }

    public function load(): array
    {
        $result = [
            'config' => $this->getDefaultConfig(),
            'items' => [],
            'loaded' => false,
            'warnings' => [],
            'errors' => []
        ];

        if (!file_exists($this->bootstrapFilePath)) {
            $this->createDefaultBootstrapFile();
            $result['warnings'][] = 'Bootstrap file was missing. Default file created.';
            Logger::logWarn('BootstrapLoader', 'Bootstrap file missing. Default file created.', 'BOOTSTRAP_MISSING');
            return $result;
        }

        $rawJson = file_get_contents($this->bootstrapFilePath);

        if ($rawJson === false) {
            $result['errors'][] = 'Bootstrap file could not be read.';
            Logger::logError('BootstrapLoader', 'Bootstrap file could not be read.', 'BOOTSTRAP_READ_FAILED');
            return $result;
        }

        $decodedJson = json_decode($rawJson, true);

        if (!is_array($decodedJson)) {
            $result['errors'][] = 'Bootstrap JSON is malformed.';
            Logger::logError('BootstrapLoader', 'Bootstrap JSON is malformed.', 'BOOTSTRAP_JSON_INVALID');
            return $result;
        }

        $result['loaded'] = true;
        $result['config'] = $this->configSalvage($decodedJson['config'] ?? []);
        $result['items'] = $this->itemsSalvage($decodedJson['items'] ?? []);

        return $result;
    }

    private function getDefaultConfig(): array
    {
        return [
            'ttlDefault' => self::TTL_SECONDS_DEFAULT,
            'ttlMax' => self::TTL_SECONDS_MAX,
            'host' => self::HOST_DEFAULT,
            'port' => self::PORT_DEFAULT,
            'logFile' => self::LOG_FILE_DEFAULT
        ];
    }

    private function createDefaultBootstrapFile(): void
    {
        $defaultContent = [
            'config' => $this->getDefaultConfig(),
            'items' => []
        ];

        $directoryPath = dirname($this->bootstrapFilePath);

        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0777, true);
        }

        file_put_contents($this->bootstrapFilePath, json_encode($defaultContent, JSON_PRETTY_PRINT));
    }

    private function configSalvage(array $config): array
    {
        $defaultConfig = $this->getDefaultConfig();

        if (isset($config['ttlDefault']) && is_int($config['ttlDefault']) && $config['ttlDefault'] >= 1) {
            $defaultConfig['ttlDefault'] = $config['ttlDefault'];
        }

        if (isset($config['ttlMax']) && is_int($config['ttlMax']) && $config['ttlMax'] >= $defaultConfig['ttlDefault']) {
            $defaultConfig['ttlMax'] = $config['ttlMax'];
        }

        if (isset($config['host']) && is_string($config['host']) && $config['host'] !== '') {
            $defaultConfig['host'] = $config['host'];
        }

        if (isset($config['port']) && is_int($config['port']) && $config['port'] > 0 && $config['port'] <= 65535) {
            $defaultConfig['port'] = $config['port'];
        }

        if (isset($config['logFile']) && is_string($config['logFile']) && $config['logFile'] !== '') {
            $defaultConfig['logFile'] = $config['logFile'];
        }

        return $defaultConfig;
    }

    private function itemsSalvage(array $items): array
    {
        $validItems = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                Logger::logWarn('BootstrapLoader', 'Invalid preload item skipped.', 'BOOTSTRAP_ITEM_INVALID');
                continue;
            }

            if (!isset($item['key']) || !is_string($item['key']) || !$this->isValidKey($item['key'])) {
                Logger::logWarn('BootstrapLoader', 'Invalid preload item key skipped.', 'BOOTSTRAP_ITEM_KEY_INVALID');
                continue;
            }

            if (!array_key_exists('value', $item)) {
                Logger::logWarn('BootstrapLoader', 'Invalid preload item value skipped.', 'BOOTSTRAP_ITEM_VALUE_MISSING');
                continue;
            }

            if (isset($item['ttl']) && (!is_int($item['ttl']) || $item['ttl'] < 1 || $item['ttl'] > self::TTL_SECONDS_MAX)) {
                Logger::logWarn('BootstrapLoader', 'Invalid preload item TTL skipped.', 'BOOTSTRAP_ITEM_TTL_INVALID');
                continue;
            }

            $validItems[] = [
                'key' => $item['key'],
                'value' => $item['value'],
                'ttl' => $item['ttl'] ?? null
            ];
        }

        return $validItems;
    }

    private function isValidKey(string $key): bool
    {
        return $key !== '' && strlen($key) <= 255 && preg_match('/^[A-Za-z0-9._:-]+$/', $key) === 1;
    }
}
