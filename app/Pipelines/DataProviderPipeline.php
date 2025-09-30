<?php
declare(strict_types=1);

namespace App\Pipelines;

use App\Interfaces\DataProviders\DataProviderInterface;
use App\Interfaces\Pipelines\DataProviderPipelineInterface;

final class DataProviderPipeline implements DataProviderPipelineInterface
{
    /**
     * Список провайдеров
     * @var array<class-string>
     */
    private array $dataProviders;
    /** @var int Всего провайдеров */
    private int $max;
    /** @var int Текущий провайдер */
    private int $current = 0;


    public function __construct()
    {
        $this->dataProviders = config('data-providers');
        $this->max = count($this->dataProviders) - 1;
    }

    /** @inheritDoc */
    public function next(): self
    {
        $this->current++;
        return $this;
    }

    /** @inheritDoc */
    public function canNext(): bool
    {
        return $this->current <= $this->max;
    }

    /** @inheritDoc */
    public function resolve(): DataProviderInterface
    {
        return resolve($this->dataProviders[$this->current]);
    }
}
