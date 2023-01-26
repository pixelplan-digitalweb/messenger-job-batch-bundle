# MessengerJobBatchBundle #

## About ##

// TODO

## Installation ##

Use [Composer](https://github.com/composer/composer):
```sh
composer require pixelplan/messenger-job-batch-bundle
```

### Without Symfony Flex ###

Add a new line to `config/bundles.php`

```
Pixelplan\MessengerJobBatchBundle\MessengerJobBatchBundle::class => ['all' => true],
```

Configure Cache pool and CRON

```
messenger_job_batch:
    cache_pool: messenger_job_batch.cache
    lock_factory: lock.messenger_job_batch.factory
```

## License ##

See [LICENSE](LICENSE).
