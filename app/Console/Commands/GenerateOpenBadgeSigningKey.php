<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use RuntimeException;

#[Signature('openbadges:generate-key')]
#[Description('Generate the RSA signing key used for Open Badges 3.0 credentials')]
class GenerateOpenBadgeSigningKey extends Command
{
    public function handle(): int
    {
        $keyPath = config('open_badges.private_key_path');

        if (! is_string($keyPath)) {
            $this->error('The Open Badges private key path is not configured.');

            return self::FAILURE;
        }

        if (is_file($keyPath)) {
            $this->error('A signing key already exists at the configured path. Refusing to replace it.');

            return self::FAILURE;
        }

        $directory = dirname($keyPath);

        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new RuntimeException('Unable to create the Open Badges private key directory.');
        }

        $options = [
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ];
        $opensslConfig = getenv('OPENSSL_CONF');
        $bundledConfig = dirname(PHP_BINARY).DIRECTORY_SEPARATOR.'extras'.DIRECTORY_SEPARATOR.'ssl'.DIRECTORY_SEPARATOR.'openssl.cnf';

        if (is_string($opensslConfig) && is_file($opensslConfig)) {
            $options['config'] = $opensslConfig;
        } elseif (is_file($bundledConfig)) {
            $options['config'] = $bundledConfig;
        }

        $key = openssl_pkey_new($options);

        $privateKey = null;

        if (! $key || ! openssl_pkey_export($key, $privateKey) || ! is_string($privateKey) || file_put_contents($keyPath, $privateKey, LOCK_EX) === false) {
            $this->error('Unable to generate the Open Badges RSA signing key.');

            return self::FAILURE;
        }

        @chmod($keyPath, 0600);
        $this->info('Open Badges signing key generated. Keep the private key backed up and out of source control.');

        return self::SUCCESS;
    }
}
