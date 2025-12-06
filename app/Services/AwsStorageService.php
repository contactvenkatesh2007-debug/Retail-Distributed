<?php

namespace App\Services;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class AwsStorageService
{
    protected S3Client $s3Client;
    protected string $bucket;
    protected string $region;

    public function __construct()
    {
        $this->region = config('services.aws.default_region', 'us-east-1');
        $this->bucket = config('services.aws.bucket');

        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => $this->region,
            'credentials' => [
                'key' => config('services.aws.access_key_id'),
                'secret' => config('services.aws.secret_access_key'),
            ],
        ]);
    }

    /**
     * Upload file to S3
     */
    public function uploadFile(string $path, $file, string $visibility = 'private'): array
    {
        try {
            $result = $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $path,
                'Body' => $file,
                'ACL' => $visibility === 'public' ? 'public-read' : 'private',
            ]);

            return [
                'success' => true,
                'url' => $result['ObjectURL'],
                'path' => $path,
                'key' => $result['Key'],
            ];
        } catch (AwsException $e) {
            Log::error("S3 upload failed: {$e->getMessage()}", [
                'path' => $path,
                'error' => $e->getAwsErrorMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getAwsErrorMessage(),
            ];
        }
    }

    /**
     * Upload file using Laravel Storage facade
     */
    public function upload(string $path, $contents, string $visibility = 'private'): bool
    {
        try {
            return Storage::disk('s3')->put($path, $contents, $visibility);
        } catch (Exception $e) {
            Log::error("Storage upload failed: {$e->getMessage()}", [
                'path' => $path,
            ]);
            return false;
        }
    }

    /**
     * Download file from S3
     */
    public function download(string $path): ?string
    {
        try {
            $result = $this->s3Client->getObject([
                'Bucket' => $this->bucket,
                'Key' => $path,
            ]);

            return $result['Body']->getContents();
        } catch (AwsException $e) {
            Log::error("S3 download failed: {$e->getMessage()}", [
                'path' => $path,
            ]);
            return null;
        }
    }

    /**
     * Get file URL
     */
    public function getUrl(string $path, int $expiration = 3600): string
    {
        try {
            $cmd = $this->s3Client->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key' => $path,
            ]);

            $request = $this->s3Client->createPresignedRequest($cmd, "+{$expiration} seconds");
            return (string) $request->getUri();
        } catch (Exception $e) {
            Log::error("S3 URL generation failed: {$e->getMessage()}", [
                'path' => $path,
            ]);
            return Storage::disk('s3')->url($path);
        }
    }

    /**
     * Delete file from S3
     */
    public function delete(string $path): bool
    {
        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $path,
            ]);
            return true;
        } catch (AwsException $e) {
            Log::error("S3 delete failed: {$e->getMessage()}", [
                'path' => $path,
            ]);
            return false;
        }
    }

    /**
     * List files in S3 bucket
     */
    public function listFiles(string $prefix = '', int $maxKeys = 1000): array
    {
        try {
            $result = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucket,
                'Prefix' => $prefix,
                'MaxKeys' => $maxKeys,
            ]);

            $files = [];
            if (isset($result['Contents'])) {
                foreach ($result['Contents'] as $object) {
                    $files[] = [
                        'key' => $object['Key'],
                        'size' => $object['Size'],
                        'last_modified' => $object['LastModified'],
                    ];
                }
            }

            return $files;
        } catch (AwsException $e) {
            Log::error("S3 list failed: {$e->getMessage()}", [
                'prefix' => $prefix,
            ]);
            return [];
        }
    }

    /**
     * Get EC2 instance information
     */
    public function getEc2InstanceInfo(string $instanceId = null): array
    {
        try {
            $ec2Client = new \Aws\Ec2\Ec2Client([
                'version' => 'latest',
                'region' => $this->region,
                'credentials' => [
                    'key' => config('services.aws.access_key_id'),
                    'secret' => config('services.aws.secret_access_key'),
                ],
            ]);

            $instanceId = $instanceId ?? config('services.aws.ec2.instance_id');

            if (!$instanceId) {
                return ['success' => false, 'error' => 'Instance ID not configured'];
            }

            $result = $ec2Client->describeInstances([
                'InstanceIds' => [$instanceId],
            ]);

            $reservations = $result->get('Reservations');
            if (empty($reservations)) {
                return ['success' => false, 'error' => 'Instance not found'];
            }

            $instance = $reservations[0]['Instances'][0];

            return [
                'success' => true,
                'instance_id' => $instance['InstanceId'],
                'state' => $instance['State']['Name'],
                'instance_type' => $instance['InstanceType'],
                'public_ip' => $instance['PublicIpAddress'] ?? null,
                'private_ip' => $instance['PrivateIpAddress'] ?? null,
                'tags' => $instance['Tags'] ?? [],
            ];
        } catch (AwsException $e) {
            Log::error("EC2 info failed: {$e->getMessage()}");
            return ['success' => false, 'error' => $e->getAwsErrorMessage()];
        }
    }
}

